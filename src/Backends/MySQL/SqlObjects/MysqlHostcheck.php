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

namespace Statusengine\Mysql\SqlObjects;

use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Mysql;
use Statusengine\BulkInsertObjectStore;
use Statusengine\ValueObjects\Hostcheck;


class MysqlHostcheck extends Mysql\MysqlModel {

    /**
     * @var string
     */
    protected $baseQuery = "INSERT INTO statusengine_hostchecks
    (hostname, state, is_hardstate, start_time, end_time, output, timeout, early_timeout, latency, execution_time, perfdata, command, current_check_attempt, max_check_attempts, long_output)
    VALUES%s";

    /**
     * @var string
     */
    protected $baseValue = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var \PDO
     */
    protected $MySQL;


    /**
     * @var BulkInsertObjectStore
     */
    protected $BulkInsertObjectStore;

    /**
     * MysqlHostcheck constructor.
     * @param Mysql\MySQL $MySQL
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     */
    public function __construct(Mysql\MySQL $MySQL, BulkInsertObjectStore $BulkInsertObjectStore){
        $this->MySQL = $MySQL;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
    }

    /**
     * @param bool $isRecursion
     * @return bool
     */
    public function insert($isRecursion = false){
        /**
         * @var Hostcheck $Hostcheck
         */

        $baseQuery = $this->buildQuery();
        $query = $this->MySQL->prepare($baseQuery);

        $i = 1;
        foreach ($this->BulkInsertObjectStore->getObjects() as $key => $Hostcheck) {
            $query->bindValue($i++, $Hostcheck->getHostName());
            $query->bindValue($i++, $Hostcheck->getState());
            $query->bindValue($i++, $Hostcheck->getStateType());
            $query->bindValue($i++, $Hostcheck->getStartTime());
            $query->bindValue($i++, $Hostcheck->getEndTime());
            $query->bindValue($i++, $Hostcheck->getOutput());
            $query->bindValue($i++, $Hostcheck->getTimeout());
            $query->bindValue($i++, $Hostcheck->getEarlyTimeout());
            $query->bindValue($i++, $Hostcheck->getLatency());
            $query->bindValue($i++, $Hostcheck->getExecutionTime());
            $query->bindValue($i++, $Hostcheck->getPerfdata());
            $query->bindValue($i++, $Hostcheck->getCommand());
            $query->bindValue($i++, $Hostcheck->getCurrentCheckAttempt());
            $query->bindValue($i++, $Hostcheck->getMaxCheckAttempts());
            $query->bindValue($i++, $Hostcheck->getLongOutput());
        }

        try {
            return $this->MySQL->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->insert(true);
            }
        }
    }

}
