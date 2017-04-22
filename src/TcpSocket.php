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


use Statusengine\Exception\InvalidArgumentException;

class TcpSocket {

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var resource
     */
    private $socket;

    /**
     * @var null
     */
    private $lastErrNo = null;

    /**
     * TcpSocket constructor.
     * @param string $host
     * @param int $port
     * @throws InvalidArgumentException
     */
    public function __construct($host, $port) {
        if(!is_int($port) || $port < 0){
            throw new InvalidArgumentException(sprintf('Port number must be a integer'));
        }

        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function connect(){
        $this->lastErrNo = null;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, IPPROTO_IP);
        if(!@socket_connect($this->socket, $this->host, $this->port)){
            throw new \Exception(sprintf(
                'Could not connect: [%s] %s',
                $this->getLastErrNo(),
                $this->getLastError()
            ));
        }
        return true;
    }

    public function disconnect(){
        if(is_resource($this->socket)){
            socket_close($this->socket);
        }
        $this->socket = null;
    }

    /**
     * @param string $data
     * @return bool
     * @throws \Exception
     */
    public function send($data){
        $this->lastErrNo = null;
        if(!@socket_send($this->socket, $data, strlen($data), 0)){
            throw new \Exception(sprintf(
                'Error while sending data: [%s] %s',
                $this->getLastErrNo(),
                $this->getLastError()
            ));
        }
        return true;
    }

    /**
     * @return bool|int|null
     */
    public function getLastErrNo(){
        if(is_resource($this->socket)){
            $this->lastErrNo = socket_last_error($this->socket);
            return $this->lastErrNo;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getLastError(){
        return socket_strerror($this->lastErrNo);
    }

}
