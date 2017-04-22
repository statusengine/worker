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


class ParentSignalHandler {

    /**
     * @var ParentProcess
     */
    private $ParentProcess;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * ParentSignalHandler constructor.
     * @param ParentProcess $parentProcess
     * @param Syslog $Syslog
     */
    public function __construct(ParentProcess $parentProcess, Syslog $Syslog) {
        $this->ParentProcess = $parentProcess;
        $this->Syslog = $Syslog;
    }

    public function bind() {
        pcntl_signal(\SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(\SIGINT, [$this, 'handleSignal']);
    }

    /**
     * @param int $signo
     */
    public function handleSignal($signo) {
        switch ($signo) {
            case \SIGTERM:
            case \SIGINT:
                foreach ($this->ParentProcess->getChildPids() as $pid) {
                    //Send kill singal
                    posix_kill($pid->getPid(), \SIGTERM);

                    //Wait until child is dead
                    \pcntl_waitpid($pid->getPid(), $status);
                }

                $this->Syslog->info('Bye');
                exit(0);
                break;
        }
        $this->bind();
    }

    public function dispatch() {
        pcntl_signal_dispatch();
    }

}
