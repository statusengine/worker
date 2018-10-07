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
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var string
     */
    protected $childName = 'PerfdataChild';

    /**
     * PerfdataChild constructor.
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
        $this->PerfdataConfig = new Perfdata();
        $this->Statistics = new Statistics($this->Config, $this->Syslog);

        $BulkConfig = $this->Config->getBulkSettings();
        $BulkInsertObjectStore = new \Statusengine\BulkInsertObjectStore(
            $BulkConfig['max_bulk_delay'],
            $BulkConfig['number_of_bulk_records']
        );
        $this->PerfdataStorageBackends = new \Statusengine\Backends\PerfdataBackends\PerfdataStorageBackends(
            $this->Config,
            $BulkInsertObjectStore,
            $this->Syslog
        );

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
                $jobData = $this->convertJobToBulkJobObject($jobData);
                foreach ($jobData->messages as $jobJson) {
                    $PerfdataRaw = new PerfdataRaw($jobJson);
                    if (!$PerfdataRaw->isEmpty()) {
                        $PerfdataParser = new PerfdataParser($PerfdataRaw->getPerfdata());
                        $Perfdata = $PerfdataParser->parse();
                        unset($PerfdataParser);

                        foreach ($Perfdata as $label => $gaugeRaw) {
                            if (!is_numeric($gaugeRaw['current'])) {
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
            }

            foreach ($perfdataStorageBackends as $key => $backend) {
                $perfdataStorageBackends[$key]->dispatch();
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
