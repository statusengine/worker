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
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Statusengine\Config\WorkerConfig;
use Statusengine\QueueingEngines\QueueInterface;

class RabbitMqWorker implements QueueInterface {

    /**
     * @var WorkerConfig
     */
    private $WorkerConfig;

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var mixed
     */
    private $lastJobData;

    /**
     * @var array
     */
    private $queues = [];

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
    private $consumerId;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * GearmanWorker constructor.
     * @param WorkerConfig $WorkerConfig
     * @param Config $Config
     */
    public function __construct(WorkerConfig $WorkerConfig, Config $Config, Syslog $Syslog) {
        $this->WorkerConfig = $WorkerConfig;
        $this->Config = $Config;
        $this->Syslog = $Syslog;
        $this->addQueue($this->WorkerConfig);

        //$this->consumerId = 'StatusengineWorker-' . getmygid();
        $this->consumerId = '';
    }

    public function addQueue(WorkerConfig $WorkerConfig) {
        $this->queues[] = $WorkerConfig->getQueueName();
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

        foreach ($this->queues as $queue) {
            $this->channel->queue_declare(
                $queue,
                false,                     //passive
                $config['durable_queues'], //durable
                false,                     //exclusive
                false                      //auto_delete
            );
        }

        $this->channel->exchange_declare(
            $config['exchange'],         //name
            'direct',                    //type
            false,                       //passive
            $config['durable_exchange'], //durable
            false                        //auto_delete
        );

        foreach ($this->queues as $queue) {
            $this->channel->queue_bind(
                $queue,              //Queue name
                $config['exchange'], //Exchange name
                $queue               //routing key
            );
        }

        foreach ($this->queues as $queue) {
            $this->channel->basic_consume(
                $queue,              //Queue name
                $this->consumerId,   //Consumer identifier
                false,               //no_local: Don't receive messages published by this consumer.
                false,               //no_ack: Tells the server if the consumer will acknowledge the messages.
                false,               //exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
                false,               //nowait
                [$this, 'handleJob'] //callable
            );
        }
    }

    public function disconnect() {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * @return \stdObject|null
     */
    public function getJob() {
        if (!count($this->channel->callbacks)) {
            return null;
        }

        // https://github.com/php-amqplib/php-amqplib/blob/master/demo/amqp_consumer_non_blocking.php
        try {
            $this->channel->wait(null, false, 1);
        } catch (AMQPTimeoutException $e) {
            // Catch the timeout exception
            // Timeout of 1 second is here to save CPU time
            // Basically the same as sleep(1);
        }

        $jobData = $this->lastJobData;
        $this->lastJobData = null;
        return $jobData;
    }


    /**
     * @param AMQPMessage $message
     */
    public function handleJob($message) {
        $this->lastJobData = null;
        $data = JSONUTF8::decodeJson($message->body, $this->Syslog);
        if ($data) {
            $this->lastJobData = $data;
        }

        $ch = $message->getChannel();
        $ch->basic_ack($message->getDeliveryTag());
    }
}
