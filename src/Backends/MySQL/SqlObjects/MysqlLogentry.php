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

use Statusengine\BulkInsertObjectStore;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Mysql;
use Statusengine\ValueObjects\Logentry;

class MysqlLogentry extends Mysql\MysqlModel {

    /**
     * @var string
     */
    protected $baseQuery = 'INSERT INTO statusengine_logentries (entry_time, logentry_type, logentry_data, node_name)VALUES%s';

    /**
     * @var string
     */
    protected $baseValue = '(?,?,?,?)';

    /**
     * @var Mysql\MySQL
     */
    protected $MySQL;

    /**
     * @var BulkInsertObjectStore
     */
    protected $BulkInsertObjectStore;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * MysqlLogentry constructor.
     * @param Mysql\MySQL $MySQL
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     * @param string $nodeName
     */
    public function __construct(Mysql\MySQL $MySQL, BulkInsertObjectStore $BulkInsertObjectStore, $nodeName){
        $this->MySQL = $MySQL;
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

        $query = $this->MySQL->prepare($baseQuery);
        $i = 1;
        foreach ($this->BulkInsertObjectStore->getObjects() as $key => $Logentry) {
            $query->bindValue($i++, $Logentry->getEntryTime());
            $query->bindValue($i++, $Logentry->getLogentryType());
            $query->bindValue($i++, $Logentry->getLogentryData());
            $query->bindValue($i++, $this->nodeName);
        }

        try {
            return $this->MySQL->executeQuery($query, 'MysqlLogentry');
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->insert(true);
            }
        }
    }

}
