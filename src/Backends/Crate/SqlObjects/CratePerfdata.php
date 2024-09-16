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

use Statusengine\BulkInsertObjectStore;
use Statusengine\Crate;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\ValueObjects\Gauge;

class CratePerfdata extends Crate\CrateModel {

    /**
     * @var string
     */
    protected $baseQuery = 'INSERT INTO statusengine_perfdata (hostname, service_description, label, timestamp, timestamp_unix, value, unit)VALUES%s';

    /**
     * @var string
     */
    protected $baseValue = '(?, ?, ?, ?, ?, ?, ?)';

    /**
     * @var BulkInsertObjectStore
     */
    protected $BulkInsertObjectStore;


    /**
     * @var Crate\Crate
     */
    protected $CrateDB;

    /**
     * CratePerfdata constructor.
     * @param Crate\Crate $CrateDB
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     */
    public function __construct(Crate\Crate $CrateDB, BulkInsertObjectStore $BulkInsertObjectStore) {
        $this->CrateDB = $CrateDB;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
    }


    /**
     * @param bool $isRecursion
     * @return bool
     */
    public function insert($isRecursion = false) {
        /**
         * @var Gauge $Gauge
         */
        $baseQuery = $this->buildQuery();

        $query = $this->CrateDB->prepare($baseQuery);
        $i = 1;
        foreach ($this->BulkInsertObjectStore->getObjects() as $key => $Gauge) {
            $query->bindValue($i++, $Gauge->getHostName());
            $query->bindValue($i++, $Gauge->getServiceDescription());
            $query->bindValue($i++, $Gauge->getLabel());
            $query->bindValue($i++, ($Gauge->getTimestamp() * 1000));
            $query->bindValue($i++, $Gauge->getTimestamp());
            $query->bindValue($i++, (double)$Gauge->getValue());
            $query->bindValue($i++, $Gauge->getUnit());
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
