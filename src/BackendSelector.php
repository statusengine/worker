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

namespace Statusengine;

use Statusengine\Exception\NoBackendFoundException;

class BackendSelector {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var BulkInsertObjectStore
     */
    private $BulkInsertObjectStore;

    /**
     * BackendSelector constructor.
     * @param Config $Config
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     */
    public function __construct(Config $Config, BulkInsertObjectStore $BulkInsertObjectStore) {
        $this->Config = $Config;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
    }

    /**
     * @return \Statusengine\Mysql\MySQL|\Statusengine\Crate\Crate
     * @throws NoBackendFoundException
     */
    public function getStorageBackend() {
        if ($this->Config->isCrateEnabled()) {
            return new Crate\Crate($this->Config, $this->BulkInsertObjectStore);
        }

        if ($this->Config->isMysqlEnabled()) {
            return new Mysql\MySQL($this->Config, $this->BulkInsertObjectStore);
        }

        throw new NoBackendFoundException('No matching backend found to store historical data');
    }

}
