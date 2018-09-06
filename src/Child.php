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
use Statusengine\ValueObjects\Pid;

class Child {

    /**
     * @var WorkerConfig
     */
    private $Config;

    /**
     * @var Pid
     */
    protected $Pid;

    /**
     * @var int
     */
    protected $parentPid;

    /**
     * @var string
     */
    protected $childName = 'Unknown';

    /**
     * Child constructor.
     * @param WorkerConfig $Config
     */
    public function __construct($Config, Pid $parentPid) {
        $this->Config = $Config;
        $this->parentPid = $parentPid->getPid();
    }

    /**
     * @return Pid
     */
    public function fork() {
        $pid = pcntl_fork();
        if (!$pid) {
            //We are the child
            $this->Pid = new Pid(getmypid(), $this->childName);

            $this->setup();

            //Go to while(true) loop and do your work :)
            $this->loop();
        }

        //Return back to the parent process
        return new Pid($pid, $this->childName);
    }

    /**
     * @throws \Exception
     */
    public function checkIfParentIsAlive() {
        if ($this->parentPid != posix_getppid()) {
            throw new \Exception('My parent process is gone I guess I am orphaned and will exit now!');
            exit(); //Just to make clear that the process will exit here!
        }
    }

    public function setup(){
        //Overwrite in child
    }
}