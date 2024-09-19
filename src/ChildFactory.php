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


use Statusengine\ValueObjects\Pid;

class ChildFactory {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var Pid
     */
    private $ParentPid;

    public function __construct(Config $Config, Syslog $Syslog, Pid $ParentPid) {
        $this->Config = $Config;
        $this->Syslog = $Syslog;
        $this->ParentPid = $ParentPid;
    }

    /**
     * @return Pid
     */
    public function forkHoststatusChild() {
        $this->Syslog->info('Fork new host status worker');
        $HoststatusChild = new HoststatusChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $HoststatusChild->fork();
        return $Pid;
    }

    /**
     * @return Pid
     */
    public function forkServicestatusChild() {
        $this->Syslog->info('Fork new service status worker');
        $ServicestatusChild = new ServicestatusChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $ServicestatusChild->fork();
        return $Pid;
    }

    /**
     * @return Pid
     */
    public function forkLogentryChild() {
        $this->Syslog->info('Fork new log entry worker');
        $LogentryChild = new LogentryChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $LogentryChild->fork();
        return $Pid;
    }

    /**
     * @return Pid
     */
    public function forkStatechangeChild() {
        $this->Syslog->info('Fork new state change worker');
        $StatechangeChild = new StatechangeChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $StatechangeChild->fork();
        return $Pid;
    }

    /**
     * @return Pid
     */
    public function forkHostcheckChild() {
        $this->Syslog->info('Fork new host check worker');
        $HostcheckChild = new HostcheckChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $HostcheckChild->fork();
        return $Pid;
    }

    /**
     * @return Pid
     */
    public function forkServicecheckChild() {
        $this->Syslog->info('Fork new service check worker');
        $ServicecheckChild = new ServicecheckChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $ServicecheckChild->fork();
        return $Pid;
    }

    /**
     * @return Pid
     */
    public function forkNotificationChild() {
        $this->Syslog->info('Fork new notification log worker');
        $NotificationChild = new NotificationChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $NotificationChild->fork();
        return $Pid;
    }

    /**
     * @return Pid
     */
    public function forkMiscChild() {
        $this->Syslog->info('Fork new misc worker');
        $MiscChild = new MiscChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $MiscChild->fork();
        return $Pid;
    }

    /**
     * @return Pid
     */
    public function forkPerfdataChild() {
        $this->Syslog->info('Fork new performance data worker');
        $PerfdataChild = new PerfdataChild(
            $this->Config,
            $this->ParentPid,
            $this->Syslog
        );
        $Pid = $PerfdataChild->fork();
        return $Pid;
    }

    /**
     * @param string $childName
     * @return bool
     */
    public function canChildBeReborn($childName) {
        $methodName = 'fork' . $childName;
        return method_exists($this, $methodName);
    }

    /**
     * @param $childName
     * @return Pid
     */
    public function respawn($childName) {
        $methodName = 'fork' . $childName;
        return call_user_func([$this, $methodName]);
    }

}
