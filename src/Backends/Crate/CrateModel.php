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

namespace Statusengine\Crate;

class CrateModel {

    /**
     * @var string
     */
    protected $baseValue;

    /**
     * @param \Crate\PDO\PDOStatement $query
     * @throws \Crate\PDO\Exception\PDOException
     */
    /*public function executeQuery(\Crate\PDO\PDOStatement $query) {
        $query->execute();
    }*/

    /**
     * @return string
     */
    public function buildQuery(){
        $numberOfObjects = $this->BulkInsertObjectStore->getObjectCount();

        $values = [];
        for($i=1; $i<=$numberOfObjects; $i++){
            $values[] = $this->baseValue;
        }

        $values = implode(', ', $values);

        return sprintf($this->baseQuery, $values);
    }

}
