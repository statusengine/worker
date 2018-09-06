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
use Statusengine\Redis\StatisticCollector;

class ParentProcess {

    /**
     * @var ParentSignalHandler
     */
    private $ParentSignalHandler;

    /**
     * @var array
     */
    private $pids;

    /**
     * @var StatisticCollector
     */
    private $StatisticCollector;

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var TaskManager
     */
    private $TaskManager;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var WorkerConfig
     */
    private $MonitoringRestartConfig;

    /**
     * @var QueueInterface
     */
    private $Queue;

    /**
     * @var StorageBackend
     */
    private $StorageBackend;

    /**
     * @var bool
     */
    private $checkForCommands;

    /**
     * @var QueueingEngine
     */
    private $QueueingEngine;

    /**
     * @var ChildFactory
     */
    private $ChildFactory;

    /**
     * @var array
     */
    private $deadChilds = [];

    /**
     * ParentProcess constructor.
     * @param StatisticCollector $StatisticCollector
     */
    public function __construct(
        StatisticCollector $StatisticCollector,
        Config $Config,
        TaskManager $TaskManager,
        Syslog $Syslog,
        $MonitoringRestartConfig,
        StorageBackend $StorageBackend,
        ChildFactory $ChildFactory
    ) {
        $this->StatisticCollector = $StatisticCollector;
        $this->Config = $Config;
        $this->TaskManager = $TaskManager;
        $this->Syslog = $Syslog;
        $this->MonitoringRestartConfig = $MonitoringRestartConfig;
        $this->StorageBackend = $StorageBackend;
        $this->ChildFactory = $ChildFactory;

        $this->QueueingEngine = new QueueingEngine($this->Config, $this->MonitoringRestartConfig);
        $this->Queue = $this->QueueingEngine->getQueue();
        $this->Queue->connect();

        $this->checkForCommands = $Config->checkForCommands();
    }

    public function loop() {
        $this->ParentSignalHandler = new \Statusengine\ParentSignalHandler($this, $this->Syslog);
        $this->ParentSignalHandler->bind();

        $this->StatisticCollector->setPids($this->getChildPids());

        while (true) {
            $this->ParentSignalHandler->dispatch();
            $this->StatisticCollector->dispatch();
            $this->checkForDeadChilds();

            if ($this->checkForCommands) {
                try {
                    $this->TaskManager->checkAndProcessTasks();
                }catch (\Exception $exception){
                    $this->Syslog->error($exception->getMessage());
                }
            }

            //Also replaces sleep(1)
            $jobData = $this->Queue->getJob();
            if ($jobData !== null) {
                //Monitoring engine was restarted
                if ($jobData->object_type == 102) {
                    $this->Syslog->info('Catch monitoring restart. Trigger callbacks...');
                    $this->StorageBackend->monitoringengineWasRestarted();
                }
            }

            //Check for dead childs
            if (!empty($this->deadChilds)) {
                /** @var Pid $deadChild */
                foreach ($this->deadChilds as $deadChild) {
                    $this->removePid($deadChild);
                    if ($this->ChildFactory->canChildBeReborn($deadChild->getChildName())) {
                        $this->Syslog->info('Respawn dead child');
                        $newPid = $this->ChildFactory->respawn($deadChild->getChildName());
                        $this->addChildPid($newPid);
                    } else {
                        $this->Syslog->error(sprintf(
                            'Can not respawn child of type %s',
                            $deadChild->getChildName()
                        ));
                    }
                }
                $this->deadChilds = [];
                //Update StatisticCollector with the new PIDs
                $this->StatisticCollector->setPids($this->getChildPids());
            }
        }
    }

    /**
     * @param Pid $pid
     */
    public function addChildPid(Pid $pid) {
        $this->pids[] = $pid;
    }

    /**
     * @return array
     */
    public function getChildPids() {
        return $this->pids;
    }

    /**
     * @return Pid
     * @throws Exception\NotNumericValueException
     */
    public function getPid() {
        return new Pid(getmypid(), 'ParentProcess');
    }

    public function checkForDeadChilds() {
        $pidsAlive = [];
        foreach ($this->pids as $Pid) {
            if (pcntl_waitpid($Pid->getPid(), $status, WNOHANG) == 0) {
                //Child still alive
                $pidsAlive[] = $Pid;
            } else {
                $this->Syslog->alert(sprintf('Child with pid %s is dead!!', $Pid->getPid()));
                $this->deadChilds[] = $Pid;
            }
        }
        $this->pids = $pidsAlive;
    }

    private function removePid(Pid $PidToRemove) {
        /** @var Pid $Pid */
        foreach ($this->pids as $index => $Pid) {
            if ($Pid->getPid() === $PidToRemove->getPid()) {
                unset($this->pids[$index]);
            }
        }
    }

}