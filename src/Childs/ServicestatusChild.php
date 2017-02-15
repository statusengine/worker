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
use Statusengine\ValueObjects\Pid;
use Statusengine\ValueObjects\Servicestatus;
use Statusengine\Redis\Statistics;

class ServicestatusChild extends Child {

    /**
     * @var GearmanWorker
     */
    private $ServicetatusGearmanWorker;

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
     * ServicestatusChild constructor.
     * @param ChildSignalHandler $SignalHandler
     * @param Config $Config
     * @param $ServicestatusConfig
     * @param Pid $Pid
     * @param Statistics $Statistics
     * @param StorageBackend $StorageBackend
     */
    public function __construct(ChildSignalHandler $SignalHandler, Config $Config, $ServicestatusConfig, Pid $Pid, Statistics $Statistics, StorageBackend $StorageBackend){
        $this->SignalHandler = $SignalHandler;
        $this->Config = $Config;
        $this->ServicestatusConfig = $ServicestatusConfig;
        $this->parentPid = $Pid->getPid();
        $this->Statistics = $Statistics;

        $this->isRedisEnabled = $Config->isRedisEnabled();
        $this->storeLiveDateInArchive = $Config->storeLiveDateInArchive();

        $this->SignalHandler->bind();

        $this->ServicetatusGearmanWorker = new GearmanWorker($this->ServicestatusConfig, $Config);
        $this->ServicetatusGearmanWorker->connect();

        $this->ServicestatusRedis = new \Statusengine\Redis\Redis($Config);
        $this->ServicestatusRedis->connect();

        $this->ServicestatusList = new ServicestatusList($this->ServicestatusRedis);

        $this->StorageBackend = $StorageBackend;

    }

    public function loop(){
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isServicestatusStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        if ($this->storeLiveDateInArchive) {
            $this->StorageBackend->connect();
        }

        while (true) {
            $jobData = $this->ServicetatusGearmanWorker->getJob();
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
            $this->checkIfParentIsAlive();
        }
    }
}
