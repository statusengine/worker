<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2017  Daniel Ziegler
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


use Statusengine\Exception\TimeoutException;

class QueryHandler {


    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var string
     */
    private $queryHandlerFile;

    /**
     * @var resource
     */
    private $socket;

    /**
     * @var int
     */
    private $lastErrorNo;

    /**
     * @var string
     */
    private $lastError;

    /**
     * QueryHandler timeout in seconds
     * @var int
     */
    private $timeout = 5;

    public function __construct(Config $Config, Syslog $Syslog) {
        $this->Config = $Config;
        $this->Syslog = $Syslog;

        $this->queryHandlerFile = $Config->getQueryHandler();
    }

    public function connect() {
        $this->socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        $result = socket_connect($this->socket, $this->queryHandlerFile);

        if ($result === false) {
            $this->lastErrorNo = socket_last_error($this->socket);
            $this->lastError = socket_strerror($this->lastErrorNo);
            return false;
        }
        socket_set_nonblock($this->socket);
        return true;
    }

    /**
     * @return bool
     */
    public function disconnect() {
        return socket_close($this->socket);
    }

    /**
     * @param $commandstring
     * @return null|string
     */
    public function runCommand($commandstring) {
        $this->Syslog->info(sprintf('Execute external command: %s', $commandstring));
        $command = sprintf('%s %s%s', '#command run', $commandstring, "\0");
        return $this->executeQuery($command);
    }

    /**
     * @return bool
     */
    public function ping() {
        $command = sprintf('%s %s%s', '#echo', 'Ping', "\0");
        try {
            $result = $this->executeQuery($command);
        } catch (TimeoutException $e) {
            return false;
        }

        if (trim($result) == 'Ping') {
            return true;
        }

        return false;
    }


    /**
     * @param $query
     * @param bool $getResult
     * @return null|string
     * @throws TimeoutException
     */
    private function executeQuery($query, $getResult = true) {
        socket_write($this->socket, $query);
        if ($getResult === false) {
            return null;
        }

        $startTime = time();
        while (true) {
            $read = [$this->socket];
            $write = null;
            $except = null;
            if (socket_select($read, $write, $except, 0) < 1) {
                if ((time() - $startTime) > $this->timeout) {
                    throw new TimeoutException(sprintf('Query Handler timeout after %s seconds', $this->timeout));
                }
                continue;
            }
            if (in_array($this->socket, $read)) {
                break;
            }
        }
        return trim(socket_read($this->socket, 1024, PHP_NORMAL_READ));
    }


    /**
     * @return string
     */
    public function getLastError() {
        $lastError = $this->lastError;
        $this->lastError = '';
        return $lastError;
    }

    /**
     * @return int
     */
    public function getLastErrorNo() {
        $lastErrorNo = $this->lastErrorNo;
        $this->lastErrorNo = null;
        return $lastErrorNo;
    }

}