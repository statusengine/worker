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
use Statusengine\ValueObjects\Hoststatus;
use Statusengine\ValueObjects\Pid;
use Statusengine\Redis\Statistics;

class HoststatusChild extends Child {

    /**
     * @var QueueInterface
     */
    private $Queue;

    /**
     * @var WorkerConfig
     */
    private $HoststatusConfig;

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Redis
     */
    private $HoststatusRedis;

    /**
     * @var ChildSignalHandler
     */
    private $SignalHandler;

    /**
     * @var Statistics
     */
    private $Statistics;

    /**
     * @var HoststatusList
     */
    private $HoststatusList;

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
     * HoststatusChild constructor.
     * @param ChildSignalHandler $SignalHandler
     * @param Config $Config
     * @param $HoststatusConfig
     * @param Pid $Pid
     * @param Statistics $Statistics
     * @param StorageBackend $StorageBackend
     * @param Syslog $Syslog
     */
    public function __construct(
        ChildSignalHandler $SignalHandler,
        Config $Config,
        $HoststatusConfig,
        Pid $Pid,
        Statistics $Statistics,
        StorageBackend $StorageBackend,
        Syslog $Syslog
    ) {
        $this->SignalHandler = $SignalHandler;
        $this->Config = $Config;
        $this->HoststatusConfig = $HoststatusConfig;
        $this->parentPid = $Pid->getPid();
        $this->Statistics = $Statistics;
        $this->Syslog = $Syslog;

        $this->isRedisEnabled = $Config->isRedisEnabled();
        $this->storeLiveDateInArchive = $Config->storeLiveDateInArchive();

        $this->SignalHandler->bind();

        $this->QueueingEngine = new QueueingEngine($this->Config, $this->HoststatusConfig);
        $this->Queue = $this->QueueingEngine->getQueue();
        $this->Queue->connect();


        $this->HoststatusRedis = new \Statusengine\Redis\Redis($Config, $this->Syslog);
        $this->HoststatusRedis->connect();

        $this->HoststatusList = new HoststatusList($this->HoststatusRedis);

        $this->StorageBackend = $StorageBackend;

    }


    public function loop() {
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isHoststatusStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        if ($this->storeLiveDateInArchive) {
            $this->StorageBackend->connect();
        }

        while (true) {
            $jobData = $this->Queue->getJob();
            if ($jobData !== null) {
                $Hoststatus = new Hoststatus($jobData);

                //Only save records that stay for more than 5 minutes in the queue
                if ($Hoststatus->getStatusUpdateTime() < (time() - 500)) {
                    continue;
                }

                if ($this->isRedisEnabled) {
                    $this->HoststatusRedis->save(
                        $Hoststatus->getKey(),
                        $Hoststatus->serialize(),
                        $Hoststatus->getExpires()
                    );
                    $this->HoststatusList->updateList($Hoststatus);
                }

                if ($this->storeLiveDateInArchive) {
                    $this->StorageBackend->saveHoststatus(
                        $Hoststatus
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
