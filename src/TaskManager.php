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

use Statusengine\Exception\UnknownTaskTypeException;
use Statusengine\ValueObjects\Statistic;
use Statusengine\ValueObjects\Task;

class TaskManager {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var StorageBackend
     */
    private $StorageBackend;

    /**
     * @var QueryHandler
     */
    private $QueryHandler;

    /**
     * @var int
     */
    private $checkInterval;

    /**
     * @var int
     */
    private $lastCheck = 0;

    /**
     * TaskManager constructor.
     * @param Config $Config
     * @param StorageBackend $StorageBackend
     * @param QueryHandler $QueryHandler
     */
    public function __construct(Config $Config, StorageBackend $StorageBackend, QueryHandler $QueryHandler) {
        $this->Config = $Config;
        $this->StorageBackend = $StorageBackend;
        $this->QueryHandler = $QueryHandler;

        $this->checkInterval = $Config->getCommandCheckInterval();
    }

    public function checkAndProcessTasks() {
        if (time() - $this->lastCheck > $this->checkInterval) {
            $this->lastCheck = time();
            $tasks = $this->StorageBackend->getTasks();

            if (!empty($tasks)) {
                $taskUuids = [];
                foreach ($tasks as $task) {
                    try {
                        $this->processTask($task);
                    } catch (UnknownTaskTypeException $e) {
                        //todo implement error handling
                    }

                    $taskUuids[] = $task->getUuid();
                }
                $this->StorageBackend->deleteTaskByUuids($taskUuids);

            }

        }
    }

    /**
     * @param Task $task
     * @return bool
     * @throws UnknownTaskTypeException
     */
    private function processTask(Task $task) {
        switch ($task->getType()) {

            case 'externalcommand':
                $this->QueryHandler->connect();
                $this->QueryHandler->runCommand($task->getPayload());
                $this->QueryHandler->disconnect();
                break;

            default:
                throw new UnknownTaskTypeException();

        }

    }

}