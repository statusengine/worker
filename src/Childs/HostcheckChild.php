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
use Statusengine\ValueObjects\Hostcheck;
use Statusengine\ValueObjects\Pid;
use Statusengine\Redis\Statistics;

class HostcheckChild extends Child {

    /**
     * @var QueueInterface
     */
    private $Queue;

    /**
     * @var WorkerConfig
     */
    private $HostcheckConfig;

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var ChildSignalHandler
     */
    private $SignalHandler;

    /**
     * @var Statistics
     */
    private $Statistics;

    /**
     * Storage Backend
     */
    private $StorageBackend;

    /**
     * @var QueueingEngine
     */
    private $QueueingEngine;

    /**
     * @var string
     */
    protected $childName = 'HostcheckChild';

    /**
     * HostcheckChild constructor.
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
        $this->HostcheckConfig = new \Statusengine\Config\Hostcheck();
        $this->Statistics = new Statistics($this->Config, $this->Syslog);

        $BulkConfig = $this->Config->getBulkSettings();
        $BulkInsertObjectStore = new \Statusengine\BulkInsertObjectStore(
            $BulkConfig['max_bulk_delay'],
            $BulkConfig['number_of_bulk_records']
        );
        $BackendSelector = new BackendSelector($this->Config, $BulkInsertObjectStore, $this->Syslog);
        $this->StorageBackend = $BackendSelector->getStorageBackend();

        $this->SignalHandler->bind();

        $this->QueueingEngine = new QueueingEngine($this->Config, $this->HostcheckConfig);
        $this->Queue = $this->QueueingEngine->getQueue();
        $this->Queue->connect();
    }

    public function loop() {
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isHostcheckStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        //Connect to backend
        $this->StorageBackend->connect();

        while (true) {
            $jobData = $this->Queue->getJob();
            if ($jobData !== null) {
                foreach ($jobData->messages as $jobJson) {
                    $Hostcheck = new Hostcheck($jobJson);
                    $this->StorageBackend->saveHostcheck(
                        $Hostcheck
                    );
                    $this->Statistics->increase();
                }
            }

            $this->StorageBackend->dispatch();

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
