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

namespace Statusengine\Crate\SqlObjects;

use Crate\PDO\PDO;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Crate;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\ValueObjects\Logentry;

class CrateLogentry extends Crate\CrateModel {

    /**
     * @var string
     */
    protected $baseQuery = 'INSERT INTO statusengine_logentries (logentry_time, entry_time, logentry_type, logentry_data, node_name)VALUES%s';

    /**
     * @var string
     */
    protected $baseValue = '(?, ?, ?, ?, ?)';

    /**
     * @var BulkInsertObjectStore
     */
    protected $BulkInsertObjectStore;


    /**
     * @var Crate\Crate
     */
    protected $CrateDB;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * CrateLogentry constructor.
     * @param Crate\Crate $CrateDB
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     * @param string $nodeName
     */
    public function __construct(Crate\Crate $CrateDB, BulkInsertObjectStore $BulkInsertObjectStore, $nodeName){
        $this->CrateDB = $CrateDB;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
        $this->nodeName = $nodeName;
    }


    /**
     * @param bool $isRecursion
     * @return bool
     */
    public function insert($isRecursion = false){
        /**
         * @var Logentry $Logentry
         */

        $baseQuery = $this->buildQuery();

        $query = $this->CrateDB->prepare($baseQuery);
        $i = 1;
        foreach ($this->BulkInsertObjectStore->getObjects() as $key => $Logentry) {
            $query->bindValue($i++, $Logentry->getLogentryTime());
            $query->bindValue($i++, $Logentry->getEntryTime());
            $query->bindValue($i++, $Logentry->getLogentryType());
            $query->bindValue($i++, $Logentry->getLogentryData());
            $query->bindValue($i++, $this->nodeName);
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
