<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2018  Daniel Ziegler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Statusengine\Mysql;

use Statusengine\BulkInsertObjectStore;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Exception\UnknownTypeException;
use Statusengine\Mysql\SqlObjects\MysqlHostAcknowledgement;
use Statusengine\Mysql\SqlObjects\MysqlHostcheck;
use Statusengine\Mysql\SqlObjects\MySQLHostDowntimehistory;
use Statusengine\Mysql\SqlObjects\MysqlHostScheduleddowntime;
use Statusengine\Mysql\SqlObjects\MysqlHoststatus;
use Statusengine\Mysql\SqlObjects\MysqlLogentry;
use Statusengine\Mysql\SqlObjects\MysqlNotification;
use Statusengine\Mysql\SqlObjects\MysqlPerfdata;
use Statusengine\Mysql\SqlObjects\MysqlServiceAcknowledgement;
use Statusengine\Mysql\SqlObjects\MysqlServicecheck;
use Statusengine\Mysql\SqlObjects\MysqlServiceDowntimehistory;
use Statusengine\Mysql\SqlObjects\MysqlServiceScheduleddowntime;
use Statusengine\Mysql\SqlObjects\MysqlServicestatus;
use Statusengine\Mysql\SqlObjects\MysqlStatechange;
use Statusengine\Mysql\SqlObjects\MysqlTask;
use Statusengine\Syslog;
use Statusengine\ValueObjects\Gauge;
use Statusengine\ValueObjects\NodeName;

class MySQL implements \Statusengine\StorageBackend {

    /**
     * @var \Statusengine\Config
     */
    private $Config;

    /**
     * @var BulkInsertObjectStore
     */
    private $BulkInsertObjectStore;

    /**
     * @var \PDO
     */
    protected $Connection;

    /**
     * @var Syslog
     */
    protected $Syslog;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * @var bool
     */
    private $isDumpOfMysqlQueryParametersEnabled = false;

    /**
     * MySQL constructor.
     * @param \Statusengine\Config $Config
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     * @param Syslog $Syslog
     */
    public function __construct(\Statusengine\Config $Config, BulkInsertObjectStore $BulkInsertObjectStore, Syslog $Syslog) {
        $this->Config = $Config;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
        $this->Syslog = $Syslog;
        $this->nodeName = $Config->getNodeName();
        $this->isDumpOfMysqlQueryParametersEnabled = $Config->isDumpOfMysqlQueryParametersEnabled();
    }


    /**
     * @return string
     */
    public function getDsn() {
        $config = $this->Config->getMysqlConfig();
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s',
            $config['host'],
            $config['port'],
            $config['database']
        );
    }

    /**
     * @return \PDO
     * @throws \Exception
     */
    public function connect() {
        $config = $this->Config->getMysqlConfig();

        try {
            $this->Connection = new \PDO($this->getDsn(), $config['username'], $config['password'], [
                \PDO::ATTR_TIMEOUT => 1,
            ]);
            $this->Connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            $this->Syslog->error($e->getMessage());

            //rethrow exception that the parent process will not die.
            throw $e;
        }

        //Enable UTF-8 / utf8 / encoding value
        try {

            $config = $this->Config->getMysqlConfig();

            $query = $this->Connection->prepare('SET NAMES ' . $config['encoding']);
            $query->execute();
        } catch (\Exception $e) {
            $this->Syslog->error($e->getMessage());
            //rethrow exception that the parent process will not die.
            throw $e;
        }

        return $this->Connection;
    }

    /**
     * @param int $timeout in seconds
     */
    public function setTimeout($timeout) {
        $this->Connection->setAttribute(\PDO::ATTR_TIMEOUT, $timeout);
    }

    /**
     * @return \PDO
     * @throws \Exception
     */
    public function reconnect() {
        $this->Connection = null;
        return $this->connect();
    }

    public function disconnect() {
        unset($this->Connection);
    }

    /**
     * @param null|string $nodeName
     * @param null $startTime
     * @return mixed|void
     * @throws \Exception
     */
    public function saveNodeName($nodeName = null, $startTime = null) {
        if ($nodeName === null) {
            $nodeName = $this->nodeName;
        }

        if ($startTime === null) {
            $startTime = time();
        }

        $this->connect();
        try {
            $query = $this->Connection->prepare('INSERT INTO statusengine_nodes (node_name, node_version, node_start_time)
          VALUES(?,?,?) ON DUPLICATE KEY UPDATE node_version=VALUES(node_version), node_start_time=VALUES(node_start_time)');
            $query->bindValue(1, $nodeName);
            $query->bindValue(2, STATUSENGINE_WORKER_VERSION);
            $query->bindValue(3, $startTime);
            $query->execute();
        } catch (\Exception $e) {
            print_r($e);
            $this->Syslog->emergency($e->getMessage());
            exit(1);
        }
        $this->disconnect();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getNodes() {
        $this->connect();
        $query = $this->Connection->prepare('SELECT * FROM statusengine_nodes ORDER BY node_name ASC');

        try {
            $result = $this->fetchAll($query);
        } catch (\Exception $e) {
            $this->Syslog->emergency($e->getMessage());
            exit(1);
        }
        $this->disconnect();
        $nodes = [];
        foreach ($result as $record) {
            $nodes[] = NodeName::fromMysqlDb($record);
        }
        return $nodes;
    }

    /**
     * @param string $nodeName
     * @return mixed|void
     * @throws \Exception
     */
    public function deleteNodeByName($nodeName) {
        $this->connect();
        $query = $this->Connection->prepare('DELETE FROM statusengine_nodes WHERE node_name=?');
        $query->bindValue(1, $nodeName);
        $query->execute();

        $Hoststatus = new MysqlHoststatus($this, $this->BulkInsertObjectStore, $nodeName);
        $Hoststatus->truncate();

        $Servicestatus = new MysqlServicestatus($this, $this->BulkInsertObjectStore, $nodeName);
        $Servicestatus->truncate();

        $this->disconnect();
    }

    public function dispatch() {
            /*$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            if($bt[3]['function'] === 'forkServicestatusChild'){
              // echo "============ START ========== \n";
              // foreach($bt as $line){
              //     printf("File: %s Line: %s function: %s\n", $line['file'], $line['line'], $line['function']);
              // }
              // echo "============ END ========== \n\n";
            }*/


        if ($this->BulkInsertObjectStore->hasRaisedTimeout()) {
            try {
                $type = $this->BulkInsertObjectStore->getStoredType();

                switch ($type) {
                    case 'Statusengine\ValueObjects\Logentry':
                        $MySQLSqlObject = new MysqlLogentry($this, $this->BulkInsertObjectStore, $this->nodeName);
                        break;

                    case 'Statusengine\ValueObjects\Hostcheck':
                        $MySQLSqlObject = new  MysqlHostcheck($this, $this->BulkInsertObjectStore);
                        break;

                    case 'Statusengine\ValueObjects\Servicecheck':
                        $MySQLSqlObject = new  MysqlServicecheck($this, $this->BulkInsertObjectStore);
                        break;

                    case 'Statusengine\ValueObjects\Statechange':
                        $MySQLSqlObject = new  MysqlStatechange($this, $this->BulkInsertObjectStore);
                        break;

                    case 'Statusengine\ValueObjects\Servicestatus':
                        $MySQLSqlObject = new  MysqlServicestatus($this, $this->BulkInsertObjectStore, $this->nodeName);
                        break;

                    case 'Statusengine\ValueObjects\Hoststatus':
                        $MySQLSqlObject = new  MysqlHoststatus($this, $this->BulkInsertObjectStore, $this->nodeName);
                        break;

                    case 'Statusengine\ValueObjects\Notification':
                        $MySQLSqlObject = new  MysqlNotification($this, $this->BulkInsertObjectStore);
                        break;

                    case 'Statusengine\ValueObjects\Gauge':
                        $MySQLSqlObject = new  MysqlPerfdata($this, $this->BulkInsertObjectStore);
                        break;
                }
                $MySQLSqlObject->insert();
                $this->BulkInsertObjectStore->reset();

            } catch (UnknownTypeException $e) {
                return;
            }
        }
    }

    /**
     * @param \PDOStatement $query
     * @return bool
     * @throws StorageBackendUnavailableExceptions
     * @package string $caller
     */
    public function executeQuery(\PDOStatement $query, $caller = 'Unknown') {
        // https://dev.mysql.com/doc/refman/8.0/en/innodb-deadlocks.html
        // https://dev.mysql.com/doc/refman/8.0/en/innodb-deadlocks-handling.html
        // The deadlock logic is ported from Statusengine 2
        // https://github.com/nook24/statusengine/blame/b5c86f0e02fd69a7045eb652f49dad20ee4d2b67/cakephp/app/src/BulkRepository.php#L185
        $retries = 10;
        for ($i = 1; $i < $retries; $i++) {
            try {
                return $query->execute();

            } catch (\Exception $Exception) {
                $sqlstateErrorCode = $Exception->errorInfo[0]; // SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
                $errorNo = $Exception->errorInfo[1]; //  Driver-specific error code.
                $errorString = $Exception->errorInfo[2]; //  Driver-specific error message.
                $this->Syslog->error(sprintf('[%s] %s', $errorNo, $errorString));

                if ($i <= $retries && $sqlstateErrorCode == 40001 && $errorNo == 1213) {
                    // This is a InnoDB deadlock - retry
                    $sleep = 50000 + rand(0, 450000);
                    $this->Syslog->info('Encountered MySQL Deadlock during transaction on ' . $caller . '. Retry transaction in ' . floor($sleep / 1000) . 'ms (try ' . ($i) . '/' . $retries . ')');
                    usleep($sleep);
                } else if ($sqlstateErrorCode == 40001 && $errorNo == 1213) {
                    // too many deadlocks
                    $this->Syslog->info('Couldn\'t solve deadlock for ' . $caller . '. Ignore for now to prevent crash: Exception: ' . $Exception->getMessage());
                } else {
                    // Any other error
                    $this->Syslog->error($query->queryString);
                    $this->Syslog->error("Run the worker in foreground mode to see the full query: https://statusengine.org/worker/#debugging");

                    // This function has no return - so no log file -.-
                    // If Statusengine is running via systemd systemd will write the messages to syslog
                    if ($this->isDumpOfMysqlQueryParametersEnabled) {
                        $query->debugDumpParams();
                    }

                    if ($errorString == 'MySQL server has gone away') {
                        $this->reconnect();
                        throw new StorageBackendUnavailableExceptions($errorString);
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param \PDOStatement $query
     * @return array
     */
    public function fetchAll(\PDOStatement $query) {
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTasks() {
        $this->connect();
        $TaskLoader = new MysqlTask($this, $this->nodeName);
        $tasks = $TaskLoader->getTasks();
        $this->disconnect();
        return $tasks;
    }

    /**
     * @param array $uuids
     * @return array|bool
     * @throws \Exception
     */
    public function deleteTaskByUuids($uuids = []) {
        $this->connect();
        $TaskLoader = new MysqlTask($this, $this->nodeName);
        $result = $TaskLoader->deleteTaskByUuids($uuids);
        $this->disconnect();
        return $result;
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deletePerfdataOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_perfdata WHERE timestamp_unix < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @return \PDO
     */
    public function getConnection() {
        return $this->Connection;
    }


    /**
     * @param string $statement
     * @return \PDOStatement
     */
    public function prepare($statement) {
        return $this->Connection->prepare($statement);
    }

    /**
     * @param \Statusengine\ValueObjects\Logentry $Logentry
     */
    public function saveLogentry(\Statusengine\ValueObjects\Logentry $Logentry) {
        $this->BulkInsertObjectStore->addObject($Logentry);
    }

    /**
     * @param \Statusengine\ValueObjects\Statechange $Statechange
     */
    public function saveStatechange(\Statusengine\ValueObjects\Statechange $Statechange) {
        $this->BulkInsertObjectStore->addObject($Statechange);
    }

    /**
     * @param \Statusengine\ValueObjects\Hostcheck $Hostcheck
     */
    public function saveHostcheck(\Statusengine\ValueObjects\Hostcheck $Hostcheck) {
        $this->BulkInsertObjectStore->addObject($Hostcheck);
    }

    /**
     * @param \Statusengine\ValueObjects\Servicecheck $Servicecheck
     */
    public function saveServicecheck(\Statusengine\ValueObjects\Servicecheck $Servicecheck) {
        $this->BulkInsertObjectStore->addObject($Servicecheck);
    }

    /**
     * @param \Statusengine\ValueObjects\Servicestatus $Servicestatus
     */
    public function saveServicestatus(\Statusengine\ValueObjects\Servicestatus $Servicestatus) {
        $this->BulkInsertObjectStore->addObject($Servicestatus);
    }

    /**
     * @param \Statusengine\ValueObjects\Hoststatus $Hoststatus
     */
    public function saveHoststatus(\Statusengine\ValueObjects\Hoststatus $Hoststatus) {
        $this->BulkInsertObjectStore->addObject($Hoststatus);
    }

    /**
     * @param \Statusengine\ValueObjects\Notification $Notification
     */
    public function saveNotification(\Statusengine\ValueObjects\Notification $Notification) {
        $this->BulkInsertObjectStore->addObject($Notification);
    }

    /**
     * @param \Statusengine\ValueObjects\Acknowledgement $Acknowledgement
     */
    public function saveAcknowledgement(\Statusengine\ValueObjects\Acknowledgement $Acknowledgement) {
        if ($Acknowledgement->isHostAcknowledgement()) {
            $MysqlAcknowledgementSaver = new MysqlHostAcknowledgement($this, $Acknowledgement);
        } else {
            $MysqlAcknowledgementSaver = new MysqlServiceAcknowledgement($this, $Acknowledgement);
        }
        $MysqlAcknowledgementSaver->insert();
    }

    /**
     * @param Gauge $Gauge
     */
    public function savePerfdata(Gauge $Gauge) {
        $this->BulkInsertObjectStore->addObject($Gauge);
    }

    /**
     * @param $timestamp
     * @return bool
     */
    public function deleteHostchecksOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_hostchecks WHERE start_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deleteHostAcknowledgementsOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_host_acknowledgements WHERE entry_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deleteHostNotificationsOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_host_notifications WHERE start_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deleteHostStatehistoryOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_host_statehistory WHERE state_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    public function deleteHostDowntimeHistoryOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_host_downtimehistory WHERE entry_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param $timestamp
     * @return bool
     */
    public function deleteServicechecksOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_servicechecks WHERE start_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deleteServiceAcknowledgementsOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_service_acknowledgements WHERE entry_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deleteServiceNotificationsOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_service_notifications WHERE start_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deleteServiceStatehistoryOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_service_statehistory WHERE state_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    public function deleteServiceDowntimeHistoryOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_service_downtimehistory WHERE entry_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param $timestamp
     * @return bool
     */
    public function deleteLogentriesOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_logentries WHERE entry_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param $timestamp
     * @return bool
     */
    public function deleteTasksOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_tasks WHERE entry_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @return MySQLHostDowntimehistory
     */
    public function getHostDowntimehistoryBackend() {
        return new MySQLHostDowntimehistory($this, $this->nodeName);
    }

    /**
     * @return MysqlHostScheduleddowntime
     */
    public function getHostScheduleddowntimeBackend() {
        return new MysqlHostScheduleddowntime($this, $this->nodeName);
    }

    /**
     * @return MysqlServiceDowntimehistory
     */
    public function getServiceDowntimehistoryBackend() {
        return new MysqlServiceDowntimehistory($this, $this->nodeName);
    }

    /**
     * @return MysqlServiceScheduleddowntime
     */
    public function getServiceScheduleddowntimeBackend() {
        return new MysqlServiceScheduleddowntime($this, $this->nodeName);
    }

    public function monitoringengineWasRestarted() {
        $this->connect();
        $Hoststatus = new MysqlHoststatus($this, $this->BulkInsertObjectStore, $this->nodeName);
        $Hoststatus->truncate();

        $Servicestatus = new MysqlServicestatus($this, $this->BulkInsertObjectStore, $this->nodeName);
        $Servicestatus->truncate();
        $this->disconnect();
    }

}
