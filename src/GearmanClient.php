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

use Statusengine\QueueingEngines\QueueClientInterface;

class GearmanClient implements QueueClientInterface {
    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var \GearmanClient
     */
    private $client;

    /**
     * @var string
     */
    private $queueName;

    /**
     * GearmanClient constructor.
     * @param string $queueName
     * @param Config $Config
     * @param Syslog $Syslog
     */
    public function __construct($queueName, Config $Config, Syslog $Syslog) {
        $this->queueName = $queueName;
        $this->Config = $Config;
        $this->Syslog = $Syslog;
    }

    public function connect() {
        $config = $this->Config->getGearmanConfig();

        $this->client = new \GearmanClient();
        $this->client->addServer($config['address'], $config['port']);
        $this->client->setTimeout($config['timeout']);
    }

    public function disconnect() {
        unset($this->worker);
    }

    /**
     * @param string $payload
     */
    public function sendBackgroundJob($payload) {
        $this->client->doBackground($this->queueName, $payload);
    }
}