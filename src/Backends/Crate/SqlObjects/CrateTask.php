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
use Statusengine\Crate;
use Statusengine\ValueObjects\Task;

class CrateTask extends Crate\CrateModel {

    /**
     * @var string
     */
    protected $baseQuery = 'SELECT * FROM statusengine_tasks where node_name = ?';

    /**
     * @var string
     */
    protected $baseValue = '';

    /**
     * @var Crate\Crate
     */
    protected $CrateDB;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * CrateTask constructor.
     * @param Crate\Crate $CrateDB
     * @param $nodeName
     */
    public function __construct(Crate\Crate $CrateDB, $nodeName) {
        $this->CrateDB = $CrateDB;
        $this->nodeName = $nodeName;
    }

    /**
     * @return array
     */
    public function getTasks() {
        $query = $this->CrateDB->prepare($this->baseQuery);
        $query->bindValue(1, $this->nodeName);
        $dbResult = $this->CrateDB->fetchAll($query);
        $result = [];
        foreach ($dbResult as $record) {
            $result[] = new Task($record['entry_time'], $record['node_name'], $record['payload'], $record['type'], $record['uuid']);
        }

        return $result;
    }

    /**
     * @param array $uuids
     * @return array|bool
     */
    public function deleteTaskByUuids($uuids = []) {
        if (empty($uuids)) {
            return true;
        }

        $placeholders = [];
        foreach ($uuids as $uuid) {
            $placeholders[] = '?';
        }

        $baseQuery = sprintf('DELETE FROM statusengine_tasks where uuid IN(%s)', implode(',', $placeholders));
        $query = $this->CrateDB->prepare($baseQuery);
        $i = 1;
        foreach ($uuids as $uuid) {
            $query->bindValue($i++, $uuid);
        }

        return $this->CrateDB->fetchAll($query);
    }

}
