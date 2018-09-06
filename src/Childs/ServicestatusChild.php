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

namespace Statusengine;

use Statusengine\Config\WorkerConfig;
use Statusengine\QueueingEngines\QueueingEngine;
use Statusengine\QueueingEngines\QueueInterface;
use Statusengine\ValueObjects\Pid;
use Statusengine\ValueObjects\Servicestatus;
use Statusengine\Redis\Statistics;

class ServicestatusChild extends Child {

    /**
     * @var QueueInterface
     */
    private $Queue;

    /**
     * @var WorkerConfig
     */
    private $ServicestatusConfig;

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Redis
     */
    private $ServicestatusRedis;

    /**
     * @var ChildSignalHandler
     */
    private $SignalHandler;

    /**
     * @var Statistics
     */
    private $Statistics;

    /**
     * @var ServicestatusList
     */
    private $ServicestatusList;

    /**
     * @var StorageBackend
     */
    private $StorageBackend;

    /**
     * @var bool
     */
    private $isRedisEnabled;

    /**
     * @var bool
     */
    private $storeLiveDateInArchive;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var QueueingEngine
     */
    private $QueueingEngine;

    /**
     * ServicestatusChild constructor.
     * @param Config $Config
     * @param Pid $Pid
     * @param Syslog $Syslog
     */
    public function __construct(
        Config $Config,
        Pid $Pid,
        Syslog $Syslog
    ) {
        $this->Config = $Config;
        $this->parentPid = $Pid->getPid();
        $this->Syslog = $Syslog;
    }

    public function setup() {
        $this->SignalHandler = new ChildSignalHandler();
        $this->ServicestatusConfig = new \Statusengine\Config\Servicestatus();
        $this->Statistics = new Statistics($this->Config, $this->Syslog);

        $BulkConfig = $this->Config->getBulkSettings();
        $BulkInsertObjectStore = new \Statusengine\BulkInsertObjectStore(
            $BulkConfig['max_bulk_delay'],
            $BulkConfig['number_of_bulk_records']
        );
        $BackendSelector = new BackendSelector($this->Config, $BulkInsertObjectStore, $this->Syslog);
        $this->StorageBackend = $BackendSelector->getStorageBackend();

        $this->isRedisEnabled = $this->Config->isRedisEnabled();
        $this->storeLiveDateInArchive = $this->Config->storeLiveDateInArchive();

        $this->SignalHandler->bind();

        $this->QueueingEngine = new QueueingEngine($this->Config, $this->ServicestatusConfig);
        $this->Queue = $this->QueueingEngine->getQueue();
        $this->Queue->connect();

        $this->ServicestatusRedis = new \Statusengine\Redis\Redis($this->Config, $this->Syslog);
        $this->ServicestatusRedis->connect();

        $this->ServicestatusList = new ServicestatusList($this->ServicestatusRedis);
    }

    public function loop() {
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isServicestatusStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        if ($this->storeLiveDateInArchive) {
            $this->StorageBackend->connect();
        }

        while (true) {
            $jobData = $this->Queue->getJob();
            if ($jobData !== null) {
                $Servicestatus = new Servicestatus($jobData);

                //Only save records that stay for more than 5 minutes in the queue
                if ($Servicestatus->getStatusUpdateTime() < (time() - 500)) {
                    continue;
                }

                if ($this->isRedisEnabled) {
                    $this->ServicestatusRedis->save(
                        $Servicestatus->getKey(),
                        $Servicestatus->serialize(),
                        $Servicestatus->getExpires()
                    );
                    $this->ServicestatusList->updateList($Servicestatus);
                }

                if ($this->storeLiveDateInArchive) {
                    $this->StorageBackend->saveServicestatus(
                        $Servicestatus
                    );
                }

                $this->Statistics->increase();
            }

            if ($this->storeLiveDateInArchive) {
                $this->StorageBackend->dispatch();
            }

            $this->Statistics->dispatch();
            $this->SignalHandler->dispatch();
            if ($this->SignalHandler->shouldExit()) {
                $this->Queue->disconnect();
                exit(0);
            }
            $this->checkIfParentIsAlive();
        }
    }
}
