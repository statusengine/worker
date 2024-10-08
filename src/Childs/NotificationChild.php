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
use Statusengine\ValueObjects\NotificationLog;
use Statusengine\ValueObjects\Servicecheck;
use Statusengine\ValueObjects\Pid;
use Statusengine\Redis\Statistics;

/**
 * This child will handle the notification queue and save records into the
 * statusengine_host_notifications_log and statusengine_service_notifications_log tables
 *
 * If you are looking for statusengine_host_notifications and statusengine_service_notifications they are processed
 * by the MiscChild.
 */
class NotificationChild extends Child {

    /**
     * @var QueueInterface
     */
    private $Queue;

    /**
     * @var WorkerConfig
     */
    private $NotificationLogConfig;

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var ChildSignalHandler
     */
    private $SignalHandler;

    /**
     * @var Statistics
     */
    private $Statistics;

    /**
     * Storage Backend
     */
    private $StorageBackend;

    /**
     * @var QueueingEngine
     */
    private $QueueingEngine;

    /**
     * @var string
     */
    protected $childName = 'NotificationChild';

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * ServicecheckChild constructor.
     * @param Config $Config
     * @param Pid $Pid
     * @param Syslog $Syslog
     */
    public function __construct(
        Config $Config,
        Pid $Pid,
        Syslog $Syslog
    ) {
        $this->Config = $Config;
        $this->parentPid = $Pid->getPid();
        $this->Syslog = $Syslog;
    }

    public function setup() {
        $this->SignalHandler = new ChildSignalHandler();
        $this->NotificationLog = new \Statusengine\Config\NotificationLog();
        $this->Statistics = new Statistics($this->Config, $this->Syslog);

        $BulkConfig = $this->Config->getBulkSettings();
        $BulkInsertObjectStore = new \Statusengine\BulkInsertObjectStore(
            $BulkConfig['max_bulk_delay'],
            $BulkConfig['number_of_bulk_records']
        );
        $BackendSelector = new BackendSelector($this->Config, $BulkInsertObjectStore, $this->Syslog);
        $this->StorageBackend = $BackendSelector->getStorageBackend();

        $this->SignalHandler->bind();

        $this->QueueingEngine = new QueueingEngine($this->Config, $this->NotificationLog, $this->Syslog);
        $this->Queue = $this->QueueingEngine->getQueue();
        $this->Queue->connect();
    }

    public function loop() {
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isNotificationLogStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        //Connect to backend
        $this->StorageBackend->connect();

        while (true) {
            $jobData = $this->Queue->getJob();
            if ($jobData !== null) {
                $jobData = $this->convertJobToBulkJobObject($jobData);
                foreach ($jobData->messages as $jobJson) {
                    $NotificationLog = new NotificationLog($jobJson);
                    if ($NotificationLog->isValidNotification()) {
                        $this->StorageBackend->saveNotificationLog(
                            $NotificationLog
                        );
                        $this->Statistics->increase();
                    }

                    // Dispatch for bulk messages from queue
                    $this->StorageBackend->dispatch();
                }
            }

            // Dispatch for single messages and timeout checks
            $this->StorageBackend->dispatch();

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
