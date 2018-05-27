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

use Statusengine\Backends\PerfdataBackends\PerfdataStorageBackends;
use Statusengine\Config\Perfdata;
use Statusengine\QueueingEngines\QueueingEngine;
use Statusengine\QueueingEngines\QueueInterface;
use Statusengine\ValueObjects\Gauge;
use Statusengine\ValueObjects\PerfdataRaw;
use Statusengine\ValueObjects\Pid;
use Statusengine\Redis\Statistics;

class PerfdataChild extends Child {

    /**
     * @var QueueInterface
     */
    private $Queue;

    /**
     * @var Perfdata
     */
    private $PerfdataConfig;

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
     * @var PerfdataStorageBackends
     */
    private $PerfdataStorageBackends;

    /**
     * @var QueueingEngine
     */
    private $QueueingEngine;

    /**
     * PerfdataChild constructor.
     * @param ChildSignalHandler $SignalHandler
     * @param Config $Config
     * @param $PerfdataConfig
     * @param Pid $Pid
     * @param Statistics $Statistics
     * @param PerfdataStorageBackends $PerfdataStorageBackends
     */
    public function __construct(ChildSignalHandler $SignalHandler, Config $Config, $PerfdataConfig, Pid $Pid, Statistics $Statistics, PerfdataStorageBackends $PerfdataStorageBackends) {
        $this->SignalHandler = $SignalHandler;
        $this->Config = $Config;
        $this->PerfdataConfig = $PerfdataConfig;
        $this->parentPid = $Pid->getPid();
        $this->Statistics = $Statistics;
        $this->PerfdataStorageBackends = $PerfdataStorageBackends;

        $this->SignalHandler->bind();


        $this->QueueingEngine = new QueueingEngine($this->Config, $this->PerfdataConfig);
        $this->Queue = $this->QueueingEngine->getQueue();
        $this->Queue->connect();
    }


    public function loop() {
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isPerfdataStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        //Connect to backends
        $perfdataStorageBackends = $this->PerfdataStorageBackends->getBackends();
        foreach ($perfdataStorageBackends as $key => $backend) {
            $perfdataStorageBackends[$key]->connect();
        }

        while (true) {
            $jobData = $this->Queue->getJob();
            if ($jobData !== null) {
                $PerfdataRaw = new PerfdataRaw($jobData);
                if(!$PerfdataRaw->isEmpty()) {
                    $PerfdataParser = new PerfdataParser($PerfdataRaw->getPerfdata());
                    $Perfdata = $PerfdataParser->parse();
                    unset($PerfdataParser);

                    foreach ($Perfdata as $label => $gaugeRaw) {
                        if(!is_numeric($gaugeRaw['current'])){
                            continue;
                        }
                        $Gauge = new Gauge(
                            $PerfdataRaw->getHostName(),
                            $PerfdataRaw->getServiceDescription(),
                            $label,
                            $gaugeRaw['current'],
                            $PerfdataRaw->getTimestamp(),
                            $gaugeRaw['unit']
                        );

                        foreach ($perfdataStorageBackends as $key => $backend) {
                            $perfdataStorageBackends[$key]->savePerfdata($Gauge);
                        }
                    }
                    $this->Statistics->increase();
                }
            }

            foreach ($perfdataStorageBackends as $key => $backend) {
                $perfdataStorageBackends[$key]->dispatch();
            }

            $this->Statistics->dispatch();

            $this->SignalHandler->dispatch();
            $this->checkIfParentIsAlive();
        }
    }

}
