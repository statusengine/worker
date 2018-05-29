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
use PhpAmqpLib\Message\AMQPMessage;
use Statusengine\Config\WorkerConfig;
use Statusengine\QueueingEngines\QueueInterface;

class RabbitMqWorker implements QueueInterface {

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
     * GearmanWorker constructor.
     * @param WorkerConfig $WorkerConfig
     * @param Config $Config
     */
    public function __construct(WorkerConfig $WorkerConfig, Config $Config) {
        $this->WorkerConfig = $WorkerConfig;
        $this->Config = $Config;
        $this->addQueue($this->WorkerConfig);

        //$this->consumerId = 'StatusengineWorker-' . getmygid();
        $this->consumerId = '';
    }

    public function addQueue(WorkerConfig $WorkerConfig) {
        $this->queues[] = $WorkerConfig->getQueueName();
    }

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param \PhpAmqpLib\Connection\AbstractConnection $connection
     */
    /*
    public function shutdown($channel, $connection) {
        try {
            $channel->close();
            $connection->close();
        }catch (\Exception $e){
            debug($e->getMessage());
        }
    }*/

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

        //register_shutdown_function([$this, 'shutdown'], $this->channel, $this->connection);

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
        try {
            $this->channel->close();
            $this->connection->close();
        }catch (\Exception $e){
            debug($e->getMessage());
            debug($this->WorkerConfig);
        }
    }

    /**
     * @return \stdObject|null
     */
    public function getJob() {
        $read = [$this->connection->getSocket()];
        $write = null;
        $except = null;
        if (($changeStreamsCount = stream_select($read, $write, $except, 1)) === false) {
            return null;
        } elseif ($changeStreamsCount > 0 || $this->channel->hasPendingMethods()) {
            $this->channel->wait();
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
        $this->lastJobData = json_decode($message->body);
        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
    }

}