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

namespace Statusengine;

use Statusengine\Config\WorkerConfig;
use Statusengine\QueueingEngines\QueueingEngine;
use Statusengine\QueueingEngines\QueueInterface;
use Statusengine\ValueObjects\Statechange;
use Statusengine\ValueObjects\Pid;
use Statusengine\Redis\Statistics;

class StatechangeChild extends Child {

    /**
     * @var QueueInterface
     */
    private $Queue;

    /**
     * @var WorkerConfig
     */
    private $StatechangeConfig;

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
     * HoststatusChild constructor.
     * @param ChildSignalHandler $SignalHandler
     * @param Config $Config
     * @param $StatechangeConfig
     * @param Pid $Pid
     * @param \Statusengine\Redis\Statistics $Statistics
     * @param $StorageBackend
     */
    public function __construct(ChildSignalHandler $SignalHandler, Config $Config, $StatechangeConfig, Pid $Pid, Statistics $Statistics, $StorageBackend) {
        $this->SignalHandler = $SignalHandler;
        $this->Config = $Config;
        $this->StatechangeConfig = $StatechangeConfig;
        $this->parentPid = $Pid->getPid();
        $this->Statistics = $Statistics;
        $this->StorageBackend = $StorageBackend;

        $this->SignalHandler->bind();

        $this->QueueingEngine = new QueueingEngine($this->Config, $this->StatechangeConfig);
        $this->Queue = $this->QueueingEngine->getQueue();
        $this->Queue->connect();
    }


    public function loop() {
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isStatechangeStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        //Connect to backend
        $this->StorageBackend->connect();

        while (true) {
            $jobData = $this->Queue->getJob();
            if ($jobData !== null) {
                $Statechange = new Statechange($jobData);
                $this->StorageBackend->saveStatechange(
                    $Statechange
                );
                $this->Statistics->increase();
            }

            $this->StorageBackend->dispatch();

            $this->Statistics->dispatch();

            $this->SignalHandler->dispatch();
            $this->checkIfParentIsAlive();
        }
    }
}
