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

use Statusengine\Config\WorkerConfig;
use Statusengine\ValueObjects\Acknowledgement;
use Statusengine\ValueObjects\Notification;
use Statusengine\ValueObjects\Pid;
use Statusengine\Redis\Statistics;

class MiscChild extends Child {

    /**
     * @var GearmanWorker
     */
    private $NotificationGearmanWorker;

    /**
     * @var GearmanWorker
     */
    private $AcknowledgementGearmanWorker;

    /**
     * @var WorkerConfig
     */
    private $NotificationConfig;

    /**
     * @var WorkerConfig
     */
    private $AcknowledgementConfig;

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
     * MiscChild constructor.
     * @param ChildSignalHandler $SignalHandler
     * @param Config $Config
     * @param $NotificationConfig
     * @param Pid $Pid
     * @param Statistics $Statistics
     * @param $StorageBackend
     */
    public function __construct(
        ChildSignalHandler $SignalHandler,
        Config $Config,
        $NotificationConfig,
        $AcknowledgementConfig,
        Pid $Pid,
        Statistics $Statistics,
        $StorageBackend
    ) {
        $this->SignalHandler = $SignalHandler;
        $this->Config = $Config;

        $this->NotificationConfig = $NotificationConfig;
        $this->AcknowledgementConfig = $AcknowledgementConfig;

        $this->parentPid = $Pid->getPid();
        $this->Statistics = $Statistics;
        $this->StorageBackend = $StorageBackend;

        $this->SignalHandler->bind();

        $this->NotificationGearmanWorker = new GearmanWorker($this->NotificationConfig, $Config);
        $this->NotificationGearmanWorker->connect();

        $this->AcknowledgementGearmanWorker = new GearmanWorker($this->AcknowledgementConfig, $Config);
        $this->AcknowledgementGearmanWorker->connect();
    }


    public function loop() {
        $this->Statistics->setPid($this->Pid);
        $StatisticType = new Config\StatisticType();
        $StatisticType->isMiscStatistic();
        $this->Statistics->setStatisticType($StatisticType);

        //Connect to backend
        $this->StorageBackend->connect();

        while (true) {
            $this->handleNotifications();
            $this->handleAcknowledgements();

            $this->StorageBackend->dispatch();

            $this->Statistics->dispatch();

            $this->SignalHandler->dispatch();
            $this->checkIfParentIsAlive();
        }
    }

    private function handleNotifications() {
        $jobData = $this->NotificationGearmanWorker->getJob();
        if ($jobData !== null) {
            $Notification = new Notification($jobData);
            if ($Notification->isValidNotification()) {
                $this->StorageBackend->saveNotification(
                    $Notification
                );
                $this->Statistics->increase();
            }
        }
    }

    private function handleAcknowledgements() {
        $jobData = $this->AcknowledgementGearmanWorker->getJob();
        if ($jobData !== null) {
            $Acknowledgement = new Acknowledgement($jobData);
            $this->StorageBackend->saveAcknowledgement(
                $Acknowledgement
            );
            $this->Statistics->increase();
        }
    }
}
