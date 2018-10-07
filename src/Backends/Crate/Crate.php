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

namespace Statusengine\Crate;

use Statusengine\BulkInsertObjectStore;
use Statusengine\Config;
use Crate\PDO\PDO as PDO;
use Crate\PDO\PDOStatement;
use Statusengine\Crate\SqlObjects\CrateHostAcknowledgement;
use Statusengine\Crate\SqlObjects\CrateHostcheck;
use Statusengine\Crate\SqlObjects\CrateHostDowntimehistory;
use Statusengine\Crate\SqlObjects\CrateHostScheduleddowntime;
use Statusengine\Crate\SqlObjects\CrateHoststatus;
use Statusengine\Crate\SqlObjects\CratePerfdata;
use Statusengine\Crate\SqlObjects\CrateServiceAcknowledgement;
use Statusengine\Crate\SqlObjects\CrateServicecheck;
use Statusengine\Crate\SqlObjects\CrateServiceDowntimehistory;
use Statusengine\Crate\SqlObjects\CrateServiceScheduleddowntime;
use Statusengine\Crate\SqlObjects\CrateStatechange;
use Statusengine\Crate\SqlObjects\CrateServicestatus;
use Statusengine\Crate\SqlObjects\CrateTask;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Exception\UnknownTypeException;
use Statusengine\Mysql\SqlObjects\CrateNotification;
use Statusengine\Syslog;
use Statusengine\ValueObjects\Gauge;
use Statusengine\ValueObjects\NodeName;
use Statusengine\ValueObjects\Servicestatus;

class Crate implements \Statusengine\StorageBackend {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var PDO
     */
    protected $Connection;


    /**
     * @var BulkInsertObjectStore
     */
    private $BulkInsertObjectStore;

    /**
     * @var Syslog
     */
    protected $Syslog;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * Crate constructor.
     * @param Config $Config
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     * @param Syslog $Syslog
     */
    public function __construct(Config $Config, BulkInsertObjectStore $BulkInsertObjectStore, Syslog $Syslog) {
        $this->Config = $Config;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
        $this->Syslog = $Syslog;

        $this->nodeName = $Config->getNodeName();
    }

    /**
     * @param null|string $nodeName
     * @param null|int $startTime
     */
    public function saveNodeName($nodeName = null, $startTime = null) {
        if ($nodeName === null) {
            $nodeName = $this->nodeName;
        }

        if ($startTime === null) {
            $startTime = time();
        }

        $this->connect();
        $query = $this->Connection->prepare('INSERT INTO statusengine_nodes (node_name, node_version, node_start_time)
          VALUES(?,?,?) ON CONFLICT (node_name) DO UPDATE SET node_version = excluded.node_version, node_start_time = excluded.node_start_time';
        $query->bindValue(1, $nodeName);
        $query->bindValue(2, STATUSENGINE_WORKER_VERSION);
        $query->bindValue(3, $startTime);

        try {
            $query->execute();
        } catch (\Exception $e) {
            $this->Syslog->emergency($e->getMessage());
            exit(1);
        }

        $this->disconnect();
    }

    /**
     * @return array
     */
    public function getNodes() {
        $this->connect();
        $query = $this->Connection->prepare('SELECT * FROM statusengine_nodes ORDER BY node_name ASC');

        try {
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->Syslog->emergency($e->getMessage());
            exit(1);
        }
        $this->disconnect();
        $nodes = [];
        foreach ($result as $record) {
            $nodes[] = NodeName::fromCrateDb($record);
        }
        return $nodes;
    }

    /**
     * @param string $nodeName
     */
    public function deleteNodeByName($nodeName) {
        $this->connect();
        $query = $this->Connection->prepare('DELETE FROM statusengine_nodes WHERE node_name=?');
        $query->bindValue(1, $nodeName);
        $query->execute();

        $Hoststatus = new CrateHoststatus($this, $this->BulkInsertObjectStore, $nodeName);
        $Hoststatus->truncate();

        $Servicestatus = new CrateServicestatus($this, $this->BulkInsertObjectStore, $nodeName);
        $Servicestatus->truncate();

        $this->disconnect();
    }

    /**
     * @return string
     */
    public function getDsn() {
        $config = $this->Config->getCrateConfig();
        return sprintf('crate:%s', implode(',', $config));
    }

    /**
     * @return \Crate\PDO\PDO
     */
    public function connect() {
        try {
            $this->Connection = new PDO($this->getDsn(), null, null, [PDO::ATTR_TIMEOUT => 1]);
            $this->Connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
        $this->Connection->setAttribute(PDO::ATTR_TIMEOUT, $timeout);
    }

    public function disconnect() {
        unset($this->Connection);
    }

    /**
     * @return \Crate\PDO\PDO
     */
    public function reconnect() {
        $this->Connection = null;
        return $this->connect();
    }

    /**
     * @return \Crate\PDO\PDO
     */
    public function getConnection() {
        return $this->Connection;
    }

    /**
     * @param \PDOStatement $query
     * @return bool
     * @throws StorageBackendUnavailableExceptions
     */
    public function executeQuery(\PDOStatement $query) {
        $result = false;
        try {
            $result = $query->execute();

        } catch (\Exception $Exception) {
            $this->Syslog->error($Exception->getMessage());
            $this->reconnect();
            //todo implement error handling
            /*
             * PHP Fatal error:  Uncaught exception 'GuzzleHttp\Exception\ConnectException' with message 'No more servers available, exception from last server: cURL error 28: Operation timed out after 5001 milliseconds with 0 bytes received (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)' in /opt/statusengine-redis-5dadaf382f3e66ff3cd66a63df9b9f01df659860/redis/vendor/crate/crate-pdo/src/Crate/PDO/Http/Client.php:225
             */
        }
        return $result;
    }

    /**
     * @param PDOStatement $query
     * @return array
     */
    public function fetchAll(PDOStatement $query) {
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $statement
     * @return bool|\Crate\PDO\PDOStatement|\PDOStatement
     */
    public function prepare($statement) {
        return $this->Connection->prepare($statement);
    }

    public function dispatch() {
        if ($this->BulkInsertObjectStore->hasRaisedTimeout()) {
            try {
                $type = $this->BulkInsertObjectStore->getStoredType();

                switch ($type) {
                    case 'Statusengine\ValueObjects\Logentry':
                        $CrateSqlObject = new SqlObjects\CrateLogentry($this, $this->BulkInsertObjectStore, $this->nodeName);
                        break;

                    case 'Statusengine\ValueObjects\Hostcheck':
                        $CrateSqlObject = new  CrateHostcheck($this, $this->BulkInsertObjectStore);
                        break;

                    case 'Statusengine\ValueObjects\Servicecheck':
                        $CrateSqlObject = new  CrateServicecheck($this, $this->BulkInsertObjectStore);
                        break;

                    case 'Statusengine\ValueObjects\Statechange':
                        $CrateSqlObject = new  CrateStatechange($this, $this->BulkInsertObjectStore);
                        break;

                    case 'Statusengine\ValueObjects\Gauge':
                        $CrateSqlObject = new  CratePerfdata($this, $this->BulkInsertObjectStore);
                        break;

                    case 'Statusengine\ValueObjects\Servicestatus':
                        $CrateSqlObject = new  CrateServicestatus($this, $this->BulkInsertObjectStore, $this->nodeName);
                        break;

                    case 'Statusengine\ValueObjects\Hoststatus':
                        $CrateSqlObject = new  CrateHoststatus($this, $this->BulkInsertObjectStore, $this->nodeName);
                        break;

                    case 'Statusengine\ValueObjects\Notification':
                        $CrateSqlObject = new  CrateNotification($this, $this->BulkInsertObjectStore);
                        break;
                }
                $CrateSqlObject->insert();
                $this->BulkInsertObjectStore->reset();

            } catch (UnknownTypeException $e) {
                return;
            }
        }
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
     * @param Servicestatus $Servicestatus
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

    public function saveNotification(\Statusengine\ValueObjects\Notification $Notification) {
        $this->BulkInsertObjectStore->addObject($Notification);
    }

    public function saveAcknowledgement(\Statusengine\ValueObjects\Acknowledgement $Acknowledgement) {
        if ($Acknowledgement->isHostAcknowledgement()) {
            $CrateHostAcknowledgementSaver = new CrateHostAcknowledgement($this, $Acknowledgement);
        } else {
            $CrateHostAcknowledgementSaver = new CrateServiceAcknowledgement($this, $Acknowledgement);
        }
        $CrateHostAcknowledgementSaver->insert();
    }

    /**
     * @param Gauge $Gauge
     */
    public function savePerfdata(Gauge $Gauge) {
        $this->BulkInsertObjectStore->addObject($Gauge);
    }

    /**
     * @return array
     */
    public function getTasks() {
        $this->connect();
        $TaskLoader = new CrateTask($this, $this->nodeName);
        $tasks = $TaskLoader->getTasks();
        $this->disconnect();
        return $tasks;
    }

    /**
     * @param array $uuids
     * @return array|bool
     */
    public function deleteTaskByUuids($uuids = []) {
        $this->connect();
        $TaskLoader = new CrateTask($this, $this->nodeName);
        $result = $TaskLoader->deleteTaskByUuids($uuids);
        $this->disconnect();
        return $result;
    }

    /**
     * @param $timestamp
     */
    public function deleteHostchecksOlderThan($timestamp) {
        $partitions = $this->getPartitionsByTableName('statusengine_hostchecks');
        $daysToDelete = [];
        foreach ($partitions as $record) {
            if (isset($record['values']['day']) && $record['values']['day'] < $timestamp) {
                $daysToDelete[] = $record['values']['day'];
            }
        }

        foreach ($daysToDelete as $partition) {
            $this->dropPartitionsFromTableByTableNameAndDayValue('statusengine_hostchecks', $partition);
        }
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
     */
    public function deleteHostNotificationsOlderThan($timestamp) {
        $partitions = $this->getPartitionsByTableName('statusengine_host_notifications');
        $daysToDelete = [];
        foreach ($partitions as $record) {
            if (isset($record['values']['day']) && $record['values']['day'] < $timestamp) {
                $daysToDelete[] = $record['values']['day'];
            }
        }

        foreach ($daysToDelete as $partition) {
            $this->dropPartitionsFromTableByTableNameAndDayValue('statusengine_host_notifications', $partition);
        }
    }

    /**
     * @param int $timestamp
     */
    public function deleteHostStatehistoryOlderThan($timestamp) {
        $partitions = $this->getPartitionsByTableName('statusengine_host_statehistory');
        $daysToDelete = [];
        foreach ($partitions as $record) {
            if (isset($record['values']['day']) && $record['values']['day'] < $timestamp) {
                $daysToDelete[] = $record['values']['day'];
            }
        }

        foreach ($daysToDelete as $partition) {
            $this->dropPartitionsFromTableByTableNameAndDayValue('statusengine_host_statehistory', $partition);
        }
    }

    /**
     * @param $timestamp
     * @return bool
     */
    public function deleteHostDowntimeHistoryOlderThan($timestamp) {
        $query = $this->prepare(
            'DELETE FROM statusengine_host_downtimehistory WHERE entry_time < ?'
        );
        $query->bindValue(1, $timestamp);
        return $query->execute();
    }

    /**
     * @param $timestamp
     */
    public function deleteServicechecksOlderThan($timestamp) {
        $partitions = $this->getPartitionsByTableName('statusengine_servicechecks');
        $daysToDelete = [];
        foreach ($partitions as $record) {
            if (isset($record['values']['day']) && $record['values']['day'] < $timestamp) {
                $daysToDelete[] = $record['values']['day'];
            }
        }

        foreach ($daysToDelete as $partition) {
            $this->dropPartitionsFromTableByTableNameAndDayValue('statusengine_servicechecks', $partition);
        }
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
     */
    public function deleteServiceNotificationsOlderThan($timestamp) {
        $partitions = $this->getPartitionsByTableName('statusengine_service_notifications');
        $daysToDelete = [];
        foreach ($partitions as $record) {
            if (isset($record['values']['day']) && $record['values']['day'] < $timestamp) {
                $daysToDelete[] = $record['values']['day'];
            }
        }

        foreach ($daysToDelete as $partition) {
            $this->dropPartitionsFromTableByTableNameAndDayValue('statusengine_service_notifications', $partition);
        }
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deleteServiceStatehistoryOlderThan($timestamp) {
        $partitions = $this->getPartitionsByTableName('statusengine_service_statehistory');
        $daysToDelete = [];
        foreach ($partitions as $record) {
            if (isset($record['values']['day']) && $record['values']['day'] < $timestamp) {
                $daysToDelete[] = $record['values']['day'];
            }
        }

        foreach ($daysToDelete as $partition) {
            $this->dropPartitionsFromTableByTableNameAndDayValue('statusengine_service_statehistory', $partition);
        }
    }

    /**
     * @param $timestamp
     * @return bool
     */
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
        $partitions = $this->getPartitionsByTableName('statusengine_logentries');
        $daysToDelete = [];
        foreach ($partitions as $record) {
            if (isset($record['values']['day']) && $record['values']['day'] < $timestamp) {
                $daysToDelete[] = $record['values']['day'];
            }
        }

        foreach ($daysToDelete as $partition) {
            $this->dropPartitionsFromTableByTableNameAndDayValue('statusengine_logentries', $partition);
        }
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
     * @param int $timestamp
     * @return bool
     */
    public function deletePerfdataOlderThan($timestamp) {
        $timestamp = $timestamp * 1000;

        $partitions = $this->getPartitionsByTableName('statusengine_perfdata');
        $daysToDelete = [];
        foreach ($partitions as $record) {
            if (isset($record['values']['day']) && $record['values']['day'] < $timestamp) {
                $daysToDelete[] = $record['values']['day'];
            }
        }

        foreach ($daysToDelete as $partition) {
            $this->dropPartitionsFromTableByTableNameAndDayValue('statusengine_perfdata', $partition);
        }
    }

    /**
     * @param string $tablename
     * @return array
     */
    public function getPartitionsByTableName($tablename) {
        $query = $this->prepare(
            'SELECT * FROM information_schema.table_partitions WHERE table_name=?'
        );
        $query->bindValue(1, $tablename);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * @param string $tableName
     * @param int $dayValue
     * @return bool
     */
    public function dropPartitionsFromTableByTableNameAndDayValue($tableName, $dayValue) {
        $query = $this->prepare(sprintf('DELETE FROM %s WHERE DAY = ?', $tableName));
        $query->bindValue(1, $dayValue);
        return $query->execute();
    }

    /**
     * @return CrateHostDowntimehistory
     */
    public function getHostDowntimehistoryBackend() {
        return new CrateHostDowntimehistory($this, $this->nodeName);
    }

    /**
     * @return CrateHostScheduleddowntime
     */
    public function getHostScheduleddowntimeBackend() {
        return new CrateHostScheduleddowntime($this, $this->nodeName);
    }

    /**
     * @return CrateServiceDowntimehistory
     */
    public function getServiceDowntimehistoryBackend() {
        return new CrateServiceDowntimehistory($this, $this->nodeName);
    }

    /**
     * @return CrateServiceScheduleddowntime
     */
    public function getServiceScheduleddowntimeBackend() {
        return new CrateServiceScheduleddowntime($this, $this->nodeName);
    }

    public function monitoringengineWasRestarted() {
        $this->connect();
        $Hoststatus = new CrateHoststatus($this, $this->BulkInsertObjectStore, $this->nodeName);
        $Hoststatus->truncate();

        $Servicestatus = new CrateServicestatus($this, $this->BulkInsertObjectStore, $this->nodeName);
        $Servicestatus->truncate();
        $this->disconnect();
    }

}
