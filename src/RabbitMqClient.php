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

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Statusengine\QueueingEngines\QueueClientInterface;

class RabbitMqClient implements QueueClientInterface {
    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $queueName;

    /**
     * RabbitMqClient constructor.
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
        $config = $this->Config->getRabbitMqConfig();
        $this->connection = new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config['vhost'],
            false,      //$insist
            'AMQPLAIN', //$login_method
            null,       //$login_response
            'en_US',    //$locale
            3.0,        //$connection_timeout
            3.0,        //$read_write_timeout
            null,       //$context
            false,      //$keepalive
            0           //$heartbeat
        );

        $this->channel = $this->connection->channel();

        $this->channel->queue_declare(
            $this->queueName,
            false,                     //passive
            $config['durable_queues'], //durable
            false,                     //exclusive
            false                      //auto_delete
        );

        $this->channel->exchange_declare(
            $config['exchange'],         //name
            'direct',                    //type
            false,                       //passive
            $config['durable_exchange'], //durable
            false                        //auto_delete
        );
    }

    public function disconnect() {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * @param string $payload
     */
    public function sendBackgroundJob($payload) {
        $config = $this->Config->getRabbitMqConfig();
        $this->channel->basic_publish(new AMQPMessage($payload), $config['exchange'], $this->queueName);
    }
}