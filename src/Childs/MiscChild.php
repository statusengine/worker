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

use Statusengine\Config\Downtime;
use Statusengine\Config\WorkerConfig;
use Statusengine\QueueingEngines\QueueingEngine;
use Statusengine\ValueObjects\Acknowledgement;
use Statusengine\ValueObjects\Notification;
use Statusengine\ValueObjects\Pid;
use Statusengine\Redis\Statistics;


class MiscChild extends Child {

    /**
     * @var QueueingEngines\QueueInterface
     */
    private $Queue;

    /**
     * @var WorkerConfig
     */
    private $NotificationConfig;

    /**
     * @var WorkerConfig
     */
    private $AcknowledgementConfig;

    /**
     * @var WorkerConfig
     */
    private $DowntimeConfig;

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
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var string
     */
    protected $childName = 'MiscChild';

    /**
     * MiscChild constructor.
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

        $this->NotificationConfig = new \Statusengine\Config\Notification();
        $this->AcknowledgementConfig = new \Statusengine\Config\Acknowledgement();
        $this->DowntimeConfig = new Downtime();

        $this->Statistics = new Statistics($this->Config, $this->Syslog);

        $BulkConfig = $this->Config->getBulkSettings();
        $BulkInsertObjectStore = new \Statusengine\BulkInsertObjectStore(
            $BulkConfig['max_bulk_delay'],
            $BulkConfig['number_of_bulk_records']
        );
        $BackendSelector = new BackendSelector($this->Config, $BulkInsertObjectStore, $this->Syslog);
        $this->StorageBackend = $BackendSelector->getStorageBackend();

        $this->SignalHandler->bind();


        $this->QueueingEngine = new QueueingEngine($this->Config, $this->NotificationConfig);
        $this->Queue = $this->QueueingEngine->getQueue();
        $this->Queue->addQueue($this->AcknowledgementConfig);
        $this->Queue->addQueue($this->DowntimeConfig);
        $this->Queue->connect();
    }


    public function loop() {
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isMiscStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        //Connect to backend
        $this->StorageBackend->connect();

        while (true) {
            $jobData = $this->Queue->getJob();
            if ($jobData !== null) {
                if(property_exists($jobData, 'messages')){
                    foreach($jobData->messages as $jobJson){
                        if (property_exists($jobData, 'contactnotificationmethod')) {
                            $this->handleNotifications($jobJson);
                        }
                        if (property_exists($jobData, 'acknowledgement')) {
                            $this->handleAcknowledgements($jobJson);
                        }
                        if (property_exists($jobData, 'downtime')) {
                            $this->handleDowntime($jobJson);
                        }
                    }
                }else{
                    //Non bulk data
                    if (property_exists($jobData, 'contactnotificationmethod')) {
                        $this->handleNotifications($jobData);
                    }
                    if (property_exists($jobData, 'acknowledgement')) {
                        $this->handleAcknowledgements($jobData);
                    }
                    if (property_exists($jobData, 'downtime')) {
                        $this->handleDowntime($jobData);
                    }
                }
            }

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

    /**
     * @param \stdClass $jobData
     */
    private function handleNotifications($jobData) {
        $Notification = new Notification($jobData);
        if ($Notification->isValidNotification()) {
            $this->StorageBackend->saveNotification(
                $Notification
            );
            $this->Statistics->increase();
        }

    }

    /**
     * @param \stdClass $jobData
     */
    private function handleAcknowledgements($jobData) {
        $Acknowledgement = new Acknowledgement($jobData);
        $this->StorageBackend->saveAcknowledgement(
            $Acknowledgement
        );
        $this->Statistics->increase();
    }

    /**
     * @param \stdClass $jobData
     */
    private function handleDowntime($jobData) {

        $Downtime = new \Statusengine\ValueObjects\Downtime($jobData);

        if ($Downtime->isHostDowntime()) {
            $DowntimehistoryBackend = $this->StorageBackend->getHostDowntimehistoryBackend();
            $ScheduleddowntimeBackend = $this->StorageBackend->getHostScheduleddowntimeBackend();
        } else {
            $DowntimehistoryBackend = $this->StorageBackend->getServiceDowntimehistoryBackend();
            $ScheduleddowntimeBackend = $this->StorageBackend->getServiceScheduleddowntimeBackend();
        }

        if (!$Downtime->wasDowntimeDeleted() && !$Downtime->wasRestoredFromRetentionDat()) {
            //Filter delete and load events
            $DowntimehistoryBackend->saveDowntime($Downtime);
        }

        if ($Downtime->wasDowntimeStopped() || $Downtime->wasDowntimeDeleted()) {
            //User delete the downtime or it is expired
            $ScheduleddowntimeBackend->deleteDowntime($Downtime);
            
            if($Downtime->wasDowntimeNeverStarted()) {
                //Downtime got deleted, before scheduled start_time was reached.
                //Downtime had no effect - delete from downtime history
                $DowntimehistoryBackend->deleteDowntime($Downtime);
            }
            
        } else {
            if (!$Downtime->wasDowntimeDeleted() && !$Downtime->wasRestoredFromRetentionDat()) {
                //Filter delete and load events
                $ScheduleddowntimeBackend->saveDowntime($Downtime);
            }
        }
    }
}
