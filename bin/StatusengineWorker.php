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
        $HoststatusConfig = new Statusengine\Config\Hoststatus();
        $HoststatusSignalHandler = new \Statusengine\ChildSignalHandler();
        $HoststatusStatistics = new \Statusengine\Redis\Statistics($Config, $Syslog);
        $HoststatusChild = new Statusengine\HoststatusChild(
            $HoststatusSignalHandler,
            $Config,
            $HoststatusConfig,
            $ParentPid,
            $HoststatusStatistics,
            $StorageBackend,
            $Syslog
        );
        $hoststatusChildPid = $HoststatusChild->fork();
        $pids[] = $hoststatusChildPid;
    }
}

if ($Config->isRedisEnabled() || $Config->storeLiveDateInArchive()) {
    for ($i = 0; $i < $Config->getNumberOfServicestatusWorkers(); $i++) {
        $Syslog->info('Fork new service status worker');
        $ServicestatusConfig = new Statusengine\Config\Servicestatus();
        $ServicestatusSignalHandler = new \Statusengine\ChildSignalHandler();
        $ServicestatusStatistics = new \Statusengine\Redis\Statistics($Config, $Syslog);
        $ServicestatusChild = new Statusengine\ServicestatusChild(
            $ServicestatusSignalHandler,
            $Config,
            $ServicestatusConfig,
            $ParentPid,
            $ServicestatusStatistics,
            $StorageBackend,
            $Syslog
        );
        $servicestatusChildPid = $ServicestatusChild->fork();
        $pids[] = $servicestatusChildPid;
    }
}

if ($Config->isCrateEnabled() || $Config->isMysqlEnabled()) {
    for ($i = 0; $i < $Config->getNumberOfLogentryWorkers(); $i++) {
        $Syslog->info('Fork new log entry worker');
        $LogentryConfig = new Statusengine\Config\Logentry();
        $LogentrySignalHandler = new \Statusengine\ChildSignalHandler();
        $LogentryStatistics = new \Statusengine\Redis\Statistics($Config, $Syslog);
        $LogentryChild = new Statusengine\LogentryChild(
            $LogentrySignalHandler,
            $Config,
            $LogentryConfig,
            $ParentPid,
            $LogentryStatistics,
            $StorageBackend
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
            $StatechangeSignalHandler,
            $Config,
            $StatechangeConfig,
            $ParentPid,
            $StatechangeStatistics,
            $StorageBackend
        );
        $statechangeChildPid = $StatechangeChild->fork();
        $pids[] = $statechangeChildPid;
    }

    for ($i = 0; $i < $Config->getNumberOfHostcheckWorkers(); $i++) {
        $Syslog->info('Fork new host check worker');
        $HostcheckConfig = new Statusengine\Config\Hostcheck();
        $HostcheckSignalHandler = new \Statusengine\ChildSignalHandler();
        $HostcheckStatistics = new \Statusengine\Redis\Statistics($Config, $Syslog);
        $HostcheckChild = new Statusengine\HostcheckChild(
            $HostcheckSignalHandler,
            $Config,
            $HostcheckConfig,
            $ParentPid,
            $HostcheckStatistics,
            $StorageBackend
        );
        $hostcheckChildPid = $HostcheckChild->fork();
        $pids[] = $hostcheckChildPid;
    }

    for ($i = 0; $i < $Config->getNumberOfServicecheckWorkers(); $i++) {
        $Syslog->info('Fork new service check worker');
        $ServicecheckConfig = new Statusengine\Config\Servicecheck();
        $ServicecheckSignalHandler = new \Statusengine\ChildSignalHandler();
        $ServicecheckStatistics = new \Statusengine\Redis\Statistics($Config, $Syslog);
        $ServicecheckChild = new Statusengine\ServicecheckChild(
            $ServicecheckSignalHandler,
            $Config,
            $ServicecheckConfig,
            $ParentPid,
            $ServicecheckStatistics,
            $StorageBackend
        );
        $servicecheckChildPid = $ServicecheckChild->fork();
        $pids[] = $servicecheckChildPid;
    }

    for ($i = 0; $i < $Config->getNumberOfMiscWorkers(); $i++) {
        $Syslog->info('Fork new misc worker');
        $NotificationConfig = new Statusengine\Config\Notification();
        $AcknowledgementConfig = new \Statusengine\Config\Acknowledgement();

        $MiscSignalHandler = new \Statusengine\ChildSignalHandler();
        $MiscStatistics = new \Statusengine\Redis\Statistics($Config, $Syslog);
        $MiscChild = new Statusengine\MiscChild(
            $MiscSignalHandler,
            $Config,
            $NotificationConfig,
            $AcknowledgementConfig,
            $ParentPid,
            $MiscStatistics,
            $StorageBackend
        );
        $miscChildPid = $MiscChild->fork();
        $pids[] = $miscChildPid;
    }
}

if ($Config->isProcessPerfdataEnabled() && $Config->isOnePerfdataBackendEnabled()) {
    $BulkInsertObjectStore = new \Statusengine\BulkInsertObjectStore(
        $BulkConfig['max_bulk_delay'],
        $BulkConfig['number_of_bulk_records']
    );
    $PerfdataStorageBackends = new \Statusengine\Backends\PerfdataBackends\PerfdataStorageBackends(
        $Config,
        $BulkInsertObjectStore,
        $Syslog
    );

    for ($i = 0; $i < $Config->getNumberOfPerfdataWorkers(); $i++) {
        $Syslog->info('Fork new performance data worker');
        $PerfdataConfig = new Statusengine\Config\Perfdata();
        $PerfdataSignalHandler = new \Statusengine\ChildSignalHandler();
        $PerfdataStatistics = new \Statusengine\Redis\Statistics($Config, $Syslog);
        $PerfdataChild = new Statusengine\PerfdataChild(
            $PerfdataSignalHandler,
            $Config,
            $PerfdataConfig,
            $ParentPid,
            $PerfdataStatistics,
            $PerfdataStorageBackends
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

$QueryHandler = new \Statusengine\QueryHandler($Config);
$TaskManager = new \Statusengine\TaskManager($Config, $StorageBackend, $QueryHandler);
$ParentProcess = new \Statusengine\ParentProcess($StatisticCollector, $Config, $TaskManager, $Syslog);
foreach ($pids as $Pid) {
    $ParentProcess->addChildPid($Pid);
}

// while(true) and wait for signals
$Syslog->info('Finished daemonizing');
$ParentProcess->loop();


