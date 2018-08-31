#!/usr/bin/php
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

require_once __DIR__ . '/../bootstrap.php';

$Config = new \Statusengine\Config();

if ($Config->getDisableHttpProxy()) {
    \Statusengine\ProxySettings::disableAllProxySettings();
}

$Syslog = new \Statusengine\Syslog($Config);
$Syslog->info(sprintf('Starting Statusengine-Worker Version %s', STATUSENGINE_WORKER_VERSION));

$BulkConfig = $Config->getBulkSettings();
$BulkInsertObjectStore = new \Statusengine\BulkInsertObjectStore(
    $BulkConfig['max_bulk_delay'],
    $BulkConfig['number_of_bulk_records']
);
$BackendSelector = new Statusengine\BackendSelector($Config, $BulkInsertObjectStore, $Syslog);
$StorageBackend = $BackendSelector->getStorageBackend();

$StorageBackend->saveNodeName();

$pids = [];
$ParentPid = new \Statusengine\ValueObjects\Pid(getmypid());

if ($Config->isRedisEnabled() || $Config->storeLiveDateInArchive()) {
    for ($i = 0; $i < $Config->getNumberOfHoststatusWorkers(); $i++) {
        $Syslog->info('Fork new host status worker');
        $HoststatusChild = new Statusengine\HoststatusChild(
            $Config,
            $ParentPid,
            $Syslog
        );
        $hoststatusChildPid = $HoststatusChild->fork();
        $pids[] = $hoststatusChildPid;
    }
}

if ($Config->isRedisEnabled() || $Config->storeLiveDateInArchive()) {
    for ($i = 0; $i < $Config->getNumberOfServicestatusWorkers(); $i++) {
        $Syslog->info('Fork new service status worker');
        $ServicestatusChild = new Statusengine\ServicestatusChild(
            $Config,
            $ParentPid,
            $Syslog
        );
        $servicestatusChildPid = $ServicestatusChild->fork();
        $pids[] = $servicestatusChildPid;
    }
}

if ($Config->isCrateEnabled() || $Config->isMysqlEnabled()) {
    for ($i = 0; $i < $Config->getNumberOfLogentryWorkers(); $i++) {
        $Syslog->info('Fork new log entry worker');
        $LogentryChild = new Statusengine\LogentryChild(
            $Config,
            $ParentPid,
            $Syslog
        );
        $logentryChildPid = $LogentryChild->fork();
        $pids[] = $logentryChildPid;
    }

    for ($i = 0; $i < $Config->getNumberOfStatechangeWorkers(); $i++) {
        $Syslog->info('Fork new state change worker');
        $StatechangeConfig = new Statusengine\Config\Statechange();
        $StatechangeSignalHandler = new \Statusengine\ChildSignalHandler();
        $StatechangeStatistics = new \Statusengine\Redis\Statistics($Config, $Syslog);
        $StatechangeChild = new Statusengine\StatechangeChild(
            $Config,
            $ParentPid,
            $Syslog
        );
        $statechangeChildPid = $StatechangeChild->fork();
        $pids[] = $statechangeChildPid;
    }

    for ($i = 0; $i < $Config->getNumberOfHostcheckWorkers(); $i++) {
        $Syslog->info('Fork new host check worker');
        $HostcheckChild = new Statusengine\HostcheckChild(
            $Config,
            $ParentPid,
            $Syslog
        );
        $hostcheckChildPid = $HostcheckChild->fork();
        $pids[] = $hostcheckChildPid;
    }

    for ($i = 0; $i < $Config->getNumberOfServicecheckWorkers(); $i++) {
        $Syslog->info('Fork new service check worker');
        $ServicecheckChild = new Statusengine\ServicecheckChild(
            $Config,
            $ParentPid,
            $Syslog
        );
        $servicecheckChildPid = $ServicecheckChild->fork();
        $pids[] = $servicecheckChildPid;
    }

    for ($i = 0; $i < $Config->getNumberOfMiscWorkers(); $i++) {
        $Syslog->info('Fork new misc worker');
        $MiscChild = new Statusengine\MiscChild(
            $Config,
            $ParentPid,
            $Syslog
        );
        $miscChildPid = $MiscChild->fork();
        $pids[] = $miscChildPid;
    }
}

if ($Config->isProcessPerfdataEnabled() && $Config->isOnePerfdataBackendEnabled()) {
    for ($i = 0; $i < $Config->getNumberOfPerfdataWorkers(); $i++) {
        $Syslog->info('Fork new performance data worker');
        $PerfdataChild = new Statusengine\PerfdataChild(
            $Config,
            $ParentPid,
            $Syslog
        );
        $perfdataChildPid = $PerfdataChild->fork();
        $pids[] = $perfdataChildPid;
    }
}

// Parent Process

$ParentRedis = new Statusengine\Redis\Redis($Config, $Syslog);
$ParentRedis->connect();
$StatisticCollector = new Statusengine\Redis\StatisticCollector(
    $ParentRedis,
    new \Statusengine\Config\StatisticType()
);

$QueryHandler = new \Statusengine\QueryHandler($Config, $Syslog);
$ExternalCommandFile = new \Statusengine\ExternalCommandFile($Config, $Syslog);
$TaskManager = new \Statusengine\TaskManager($Config, $StorageBackend, $QueryHandler, $ExternalCommandFile, $Syslog);
$MonitoringRestartConfig = new Statusengine\Config\MonitoringRestart();
$ParentProcess = new \Statusengine\ParentProcess(
    $StatisticCollector,
    $Config,
    $TaskManager,
    $Syslog,
    $MonitoringRestartConfig,
    $StorageBackend
);
foreach ($pids as $Pid) {
    $ParentProcess->addChildPid($Pid);
}

// while(true) and wait for signals
$Syslog->info('Finished daemonizing');
$ParentProcess->loop();


