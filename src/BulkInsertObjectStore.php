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


use Crate\PDO\Exception\InvalidArgumentException;
use Statusengine\Exception\UnknownTypeException;

class BulkInsertObjectStore {

    /**
     * @var mixed
     */
    private $objects;

    /**
     * @var int
     */
    private $objectCount = 0;

    /**
     * @var int
     */
    private $lastAction;

    /**
     * @var int
     */
    private $maxDelay;

    /**
     * @var int
     */
    private $maxObjects;

    /**
     * BulkInsertObjectStore constructor.
     * @param int $maxDelay
     * @param int $maxObjects
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($maxDelay, $maxObjects) {
        if (!is_numeric($maxDelay)) {
            throw new \Statusengine\Exception\InvalidArgumentException('$maxDelay needs to be an integer');
        }
        if (!is_numeric($maxObjects)) {
            throw new \Statusengine\Exception\InvalidArgumentException('$maxObjects needs to be an integer');
        }

        $this->maxObjects = $maxObjects;
        $this->maxDelay = $maxDelay;
        $this->lastAction = 0;
    }

    public function addObject($object) {
        $this->objects[] = $object;
        $this->objectCount++;
        $this->lastAction = time();
    }

    /**
     * @return int
     */
    public function getObjectCount() {
        return $this->objectCount;
    }

    public function getObjects() {
        return $this->objects;
    }

    public function reset() {
        $this->objects = [];
        $this->objectCount = 0;
        $this->lastAction = time();
    }

    /**
     * @return string
     * @throws UnknownTypeException
     */
    public function getStoredType(){
        if(isset($this->objects[0])){
            return get_class($this->objects[0]);
        }
        throw new UnknownTypeException('No objects in storage');
    }

    /**
     * @return bool
     */
    public function hasRaisedTimeout() {
        if ($this->objectCount >= $this->maxObjects || time() - $this->lastAction > $this->maxDelay) {
            if ($this->objectCount > 0) {
                return true;
            }
        }
        return false;
    }

}