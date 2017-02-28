<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2017  Daniel Ziegler
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
use Statusengine\Crate\SqlObjects\CrateHostcheck;
use Statusengine\Crate\SqlObjects\CrateHoststatus;
use Statusengine\Crate\SqlObjects\CratePerfdata;
use Statusengine\Crate\SqlObjects\CrateServicecheck;
use Statusengine\Crate\SqlObjects\CrateStatechange;
use Statusengine\Crate\SqlObjects\CrateServicestatus;
use Statusengine\Crate\SqlObjects\CrateTask;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Exception\UnknownTypeException;
use Statusengine\Mysql\SqlObjects\CrateNotification;
use Statusengine\ValueObjects\Gauge;
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
     * @var string
     */
    private $nodeName;

    /**
     * Crate constructor.
     * @param Config $Config
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     */
    public function __construct(Config $Config, BulkInsertObjectStore $BulkInsertObjectStore) {
        $this->Config = $Config;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
        $this->nodeName = $Config->getNodeName();
    }

    public function saveNodeName() {
        $this->connect();
        $query = $this->Connection->prepare('INSERT INTO statusengine_nodes (node_name, node_version, node_start_time)
          VALUES(?,?,?) ON DUPLICATE KEY UPDATE node_version=VALUES(node_version), node_start_time=VALUES(node_start_time)');
        $query->bindValue(1, $this->nodeName);
        $query->bindValue(2, STATUSENGINE_WORKER_VERSION);
        $query->bindValue(3, time());
        $query->execute();

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
        $this->Connection = new PDO($this->getDsn(), null, null, [PDO::ATTR_TIMEOUT => 1]);
        $this->Connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $this->Connection;
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
            print_r($Exception->getMessage());
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
        // TODO: Implement saveAcknowledgement() method.
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

}
