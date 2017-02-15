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
     * @var bool
     */
    private $checkForCommands;

    /**
     * ParentProcess constructor.
     * @param StatisticCollector $StatisticCollector
     */
    public function __construct(StatisticCollector $StatisticCollector, Config $Config, TaskManager $TaskManager) {
        $this->StatisticCollector = $StatisticCollector;
        $this->Config = $Config;
        $this->TaskManager = $TaskManager;

        $this->checkForCommands = $Config->checkForCommands();
    }

    public function loop() {
        $this->ParentSignalHandler = new \Statusengine\ParentSignalHandler($this);
        $this->ParentSignalHandler->bind();

        $this->StatisticCollector->setPids($this->getChildPids());

        while (true) {
            $this->ParentSignalHandler->dispatch();
            $this->StatisticCollector->dispatch();
            $this->checkForDeadChilds();

            if ($this->checkForCommands) {
                $this->TaskManager->checkAndProcessTasks();
            }

            sleep(1);
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

    public function getPid() {
        return new Pid(getmypid());
    }

    public function checkForDeadChilds() {
        $pidsAlive = [];
        foreach ($this->pids as $Pid) {
            if (pcntl_waitpid($Pid->getPid(), $status, WNOHANG) == 0) {
                //Child still alive
                $pidsAlive[] = $Pid;
            }
        }
        $this->pids = $pidsAlive;
    }

}