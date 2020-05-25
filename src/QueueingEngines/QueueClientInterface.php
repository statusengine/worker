<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2019  Daniel Ziegler
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

namespace Statusengine\QueueingEngines;


use Statusengine\Config;
use Statusengine\Syslog;

interface QueueClientInterface {

    /**
     * QueueInterface constructor.
     * @param string $queueName
     * @param Config\WorkerConfig $WorkerConfig
     * @param Config $Config
     */
    public function __construct($queueName, Config $Config, Syslog $Syslog);

    /**
     * @return void
     */
    public function connect();

    /**
     * @param string $playload
     */
    public function sendBackgroundJob($playload);

    /**
     * @return void
     */
    public function disconnect();

}