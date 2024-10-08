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

namespace Statusengine\Crate\SqlObjects;

use Crate\PDO\PDOCrateDB;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Crate;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\ValueObjects\Servicecheck;

class CrateServicecheck extends Crate\CrateModel {

    /**
     * @var string
     */
    protected $baseQuery = "INSERT INTO statusengine_servicechecks
    (hostname, service_description, state, is_hardstate, start_time, end_time, output, timeout, early_timeout, latency, execution_time, perfdata, command, current_check_attempt, max_check_attempts, long_output)
    VALUES%s";

    /**
     * @var string
     */
    protected $baseValue = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var Crate\Crate
     */
    protected $CrateDB;

    /**
     * @var BulkInsertObjectStore
     */
    protected $BulkInsertObjectStore;

    /**
     * CrateServicecheck constructor.
     * @param Crate\Crate $CrateDB
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     */
    public function __construct(Crate\Crate $CrateDB, BulkInsertObjectStore $BulkInsertObjectStore){
        $this->CrateDB = $CrateDB;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
    }

    /**
     * @param bool $isRecursion
     * @return mixed
     */
    public function insert($isRecursion = false){
        /**
         * @var Servicecheck $Servicecheck
         */

        $baseQuery = $this->buildQuery();
        $query = $this->CrateDB->prepare($baseQuery);

        $i = 1;
        foreach ($this->BulkInsertObjectStore->getObjects() as $key => $Servicecheck) {
            $query->bindValue($i++, $Servicecheck->getHostName());
            $query->bindValue($i++, $Servicecheck->getServiceDescription());
            $query->bindValue($i++, $Servicecheck->getState());
            $query->bindValue($i++, (bool)$Servicecheck->getStateType(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Servicecheck->getStartTime());
            $query->bindValue($i++, $Servicecheck->getEndTime());
            $query->bindValue($i++, $Servicecheck->getOutput());
            $query->bindValue($i++, $Servicecheck->getTimeout());
            $query->bindValue($i++, (bool)$Servicecheck->getEarlyTimeout(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Servicecheck->getLatency());
            $query->bindValue($i++, $Servicecheck->getExecutionTime());
            $query->bindValue($i++, $Servicecheck->getPerfdata());
            $query->bindValue($i++, $Servicecheck->getCommand());
            $query->bindValue($i++, $Servicecheck->getCurrentCheckAttempt());
            $query->bindValue($i++, $Servicecheck->getMaxCheckAttempts());
            $query->bindValue($i++, $Servicecheck->getLongOutput());
        }

        try {
            return $this->CrateDB->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->insert(true);
            }
        }
    }

}
