<?php
/**
 * Statusengine UI
 * Copyright (C) 2018  Daniel Ziegler
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

namespace Statusengine\QueueingEngines;


use PhpAmqpLib\Exception\AMQPNotImplementedException;
use Statusengine\Config;
use Statusengine\GearmanClient;
use Statusengine\GearmanWorker;
use Statusengine\RabbitMqWorker;
use Statusengine\Syslog;

class QueueingEngine {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Config\WorkerConfig
     */
    private $WorkerConfig;

    /**
     * @var Syslog
     */
    private $Syslog;

    public function __construct(Config $Config, Config\WorkerConfig $WorkerConfig, Syslog $Syslog) {
        $this->Config = $Config;
        $this->WorkerConfig = $WorkerConfig;
        $this->Syslog = $Syslog;
    }

    /**
     * @return QueueInterface
     */
    public function getQueue() {
        if ($this->Config->isGearmanEnabled()) {
            return new GearmanWorker($this->WorkerConfig, $this->Config, $this->Syslog);
        }

        if ($this->Config->isRabbitMqEnabled()) {
            return new RabbitMqWorker($this->WorkerConfig, $this->Config, $this->Syslog);
        }
    }

    /**
     * @param string $payload
     */
    public function sendExternalCommand($payload) {
        if ($this->Config->isGearmanEnabled()) {
            $this->Syslog->info(sprintf('Execute external command (via Gearman Queue): %s', $payload));
            $GearmanClient = new GearmanClient($this->WorkerConfig->getQueueName(), $this->Config, $this->Syslog);
            $GearmanClient->connect();
            $GearmanClient->sendBackgroundJob($payload);
            $GearmanClient->disconnect();
        }

        // @todo implement rabbitmq
        if ($this->Config->isRabbitMqEnabled()) {
            throw new AMQPNotImplementedException('RabbitMq Support is not implemented yet.');
        }
    }

}
