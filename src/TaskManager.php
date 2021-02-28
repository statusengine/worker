<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2018  Daniel Ziegler
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
use Statusengine\QueueingEngines\QueueingEngine;
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
     * @var ExternalCommandFile
     */
    private $ExternalCommandFile;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var int
     */
    private $checkInterval;

    /**
     * @var int
     */
    private $lastCheck = 0;

    /**
     * @var QueueingEngine
     */
    private $QueueingEngine;

    /**
     * TaskManager constructor.
     * @param Config $Config
     * @param StorageBackend $StorageBackend
     * @param QueryHandler $QueryHandler
     * @param Syslog $Syslog
     */
    public function __construct(
        Config $Config,
        StorageBackend $StorageBackend,
        QueryHandler $QueryHandler,
        ExternalCommandFile $ExternalCommandFile,
        Syslog $Syslog,
        QueueingEngine $QueueingEngine
    ) {
        $this->Config = $Config;
        $this->StorageBackend = $StorageBackend;
        $this->QueryHandler = $QueryHandler;
        $this->ExternalCommandFile = $ExternalCommandFile;
        $this->Syslog = $Syslog;
        $this->QueueingEngine = $QueueingEngine;

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
                        $this->Syslog->error(sprintf('Error while executing external command: %s', $e->getMessage()));
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
                if ($this->Config->getSubmitMethod() === 'broker') {
                    $payload = [
                        'Command' => 'raw',
                        'Data'    => $task->getPayload()
                    ];

                    $this->QueueingEngine->sendExternalCommand(json_encode($payload));
                }

                if ($this->Config->getSubmitMethod() === 'qh') {
                    $this->QueryHandler->connect();
                    $this->QueryHandler->runCommand($task->getPayload());
                    $this->QueryHandler->disconnect();
                }

                if ($this->Config->getSubmitMethod() === 'cmd') {
                    $this->ExternalCommandFile->connect();
                    $this->ExternalCommandFile->runCommand($task->getPayload());
                    $this->ExternalCommandFile->disconnect();
                }
                break;

            default:
                throw new UnknownTaskTypeException('Unsupported command type');

        }

    }

}