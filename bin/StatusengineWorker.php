#!/usr/bin/php
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

$ChildFactory = new \Statusengine\ChildFactory($Config, $Syslog, $ParentPid);

if ($Config->isRedisEnabled() || $Config->storeLiveDateInArchive()) {
    for ($i = 0; $i < $Config->getNumberOfHoststatusWorkers(); $i++) {
        $pids[] = $ChildFactory->forkHoststatusChild();
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
        $pids[] = $ChildFactory->forkLogentryChild();
    }

    for ($i = 0; $i < $Config->getNumberOfStatechangeWorkers(); $i++) {
        $pids[] = $ChildFactory->forkStatechangeChild();
    }

    for ($i = 0; $i < $Config->getNumberOfHostcheckWorkers(); $i++) {
        $pids[] = $ChildFactory->forkHostcheckChild();
    }

    for ($i = 0; $i < $Config->getNumberOfServicecheckWorkers(); $i++) {
        $pids[] = $ChildFactory->forkServicecheckChild();
    }

    for ($i = 0; $i < $Config->getNumberOfMiscWorkers(); $i++) {
        $pids[] = $ChildFactory->forkMiscChild();
    }
}

if ($Config->isProcessPerfdataEnabled() && $Config->isOnePerfdataBackendEnabled()) {
    for ($i = 0; $i < $Config->getNumberOfPerfdataWorkers(); $i++) {
        $pids[] = $ChildFactory->forkPerfdataChild();
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
    $StorageBackend,
    $ChildFactory
);
foreach ($pids as $Pid) {
    $ParentProcess->addChildPid($Pid);
}

// while(true) and wait for signals
$Syslog->info('Finished daemonizing');
$ParentProcess->loop();


