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
use Statusengine\ValueObjects\Statechange;

class CrateStatechange extends Crate\CrateModel {

    /**
     * @var string
     */
    protected $baseQueryHost = "
      INSERT INTO statusengine_host_statehistory
      (hostname, state_time, state, state_change, is_hardstate, current_check_attempt, max_check_attempts, last_state, last_hard_state, output, long_output)
      VALUES%s";

    /**
     * @var string
     */
    protected $baseValueHost = '(?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var string
     */
    protected $baseQueryService = "
      INSERT INTO statusengine_service_statehistory
      (hostname, service_description, state_time, state, state_change, is_hardstate, current_check_attempt, max_check_attempts, last_state, last_hard_state, output, long_output)
      VALUES%s";

    /**
     * @var string
     */
    protected $baseValueService = '(?,?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var Crate\Crate
     */
    protected $CrateDB;

    /**
     * @var BulkInsertObjectStore
     */
    protected $BulkInsertObjectStore;

    /**
     * CrateStatechange constructor.
     * @param Crate\Crate $CrateDB
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     */
    public function __construct(Crate\Crate $CrateDB, BulkInsertObjectStore $BulkInsertObjectStore){
        $this->CrateDB = $CrateDB;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
    }

    public function insert(){
        /**
         * @var Statechange $Statechange
         */

        //Cache for Bulk inserts
        $hostStatechangeCache = [];
        $serviceStatechangeCache = [];

        foreach ($this->BulkInsertObjectStore->getObjects() as $Statechange) {
            if ($Statechange->isHostRecord()) {
                $hostStatechangeCache[] = $Statechange;
            } else {
                $serviceStatechangeCache[] = $Statechange;
            }
        }

        if (!empty($hostStatechangeCache)) {
            $this->getHostQuery($hostStatechangeCache);
        }

        if (!empty($serviceStatechangeCache)) {
            $this->getServiceQuery($serviceStatechangeCache);
        }
    }

    /**
     * @param $hostStatechangeCache
     * @param bool $isRecursion
     * @return mixed
     */
    public function getHostQuery($hostStatechangeCache, $isRecursion = false){
        /**
         * @var Statechange $Statechange
         */

        $baseQuery = $this->buildQueryString(sizeof($hostStatechangeCache), $this->baseValueHost, $this->baseQueryHost);
        $query = $this->CrateDB->prepare($baseQuery);

        $i = 1;
        foreach ($hostStatechangeCache as $Statechange) {
            $query->bindValue($i++, $Statechange->getHostname());
            $query->bindValue($i++, $Statechange->getStateTime());
            $query->bindValue($i++, $Statechange->getState());
            $query->bindValue($i++, (bool)$Statechange->getStateChange(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, (bool)$Statechange->getStateType(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Statechange->getCurrentCheckAttempt());
            $query->bindValue($i++, $Statechange->getMaxCheckAttempt());
            $query->bindValue($i++, $Statechange->getLastState());
            $query->bindValue($i++, $Statechange->getLastHardState());
            $query->bindValue($i++, $Statechange->getOutput());
            $query->bindValue($i++, $Statechange->getLongOutput());
        }
        try {
            return $this->CrateDB->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->getHostQuery($hostStatechangeCache, true);
            }
        }
    }

    /**
     * @param $serviceStatechangeCache
     * @param bool $isRecursion
     * @return mixed
     */
    public function getServiceQuery($serviceStatechangeCache, $isRecursion = false){
        /**
         * @var Statechange $Statechange
         */

        $baseQuery = $this->buildQueryString(sizeof($serviceStatechangeCache), $this->baseValueService, $this->baseQueryService);
        $query = $this->CrateDB->prepare($baseQuery);

        $i = 1;
        foreach ($serviceStatechangeCache as $Statechange) {
            $query->bindValue($i++, $Statechange->getHostname());
            $query->bindValue($i++, $Statechange->getServiceDescription());
            $query->bindValue($i++, $Statechange->getStateTime());
            $query->bindValue($i++, $Statechange->getState());
            $query->bindValue($i++, (bool)$Statechange->getStateChange(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, (bool)$Statechange->getStateType(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Statechange->getCurrentCheckAttempt());
            $query->bindValue($i++, $Statechange->getMaxCheckAttempt());
            $query->bindValue($i++, $Statechange->getLastState());
            $query->bindValue($i++, $Statechange->getLastHardState());
            $query->bindValue($i++, $Statechange->getOutput());
            $query->bindValue($i++, $Statechange->getLongOutput());
        }

        try {
            return $this->CrateDB->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->getServiceQuery($serviceStatechangeCache, true);
            }
        }
    }

    /**
     * @param int $numberOfObjects
     * @param string $baseValue
     * @param string $baseQuery
     * @return string
     */
    public function buildQueryString($numberOfObjects, $baseValue, $baseQuery){
        $values = [];
        for ($i = 1; $i <= $numberOfObjects; $i++) {
            $values[] = $baseValue;
        }

        $values = implode(', ', $values);

        return sprintf($baseQuery, $values);
    }

}
