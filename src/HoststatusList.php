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

namespace Statusengine;


use Statusengine\ValueObjects\Hoststatus;
use Statusengine\Redis\Redis;

class HoststatusList {

    /**
     * @var Redis
     */
    private $Redis;


    /**
     * @var array
     */
    private $states = ['up', 'down', 'unreachable'];

    public function __construct(Redis $Redis) {
        $this->Redis = $Redis;
    }

    public function updateList(Hoststatus $hoststatus) {
        //Delete old status record
        $this->removeFromAll($hoststatus->getHostname());

        //Save new status record
        $this->add($hoststatus);
    }

    /**
     * @param string $hostname you like to remove
     */
    public function removeFromAll($hostname) {
        foreach ($this->states as $state) {
            $key = $this->getKey($state);
            $this->remove($key, $hostname);
        }
    }

    /**
     * @param string $key of the list with all hostnames in given state key
     * @param string $hostname
     */
    public function remove($key, $hostname) {
        $this->Redis->removeRecordFromSet($key, $hostname);
    }

    /**
     * @param Hoststatus $hoststatus
     */
    public function add(Hoststatus $hoststatus) {
        $prefix = $this->getStateSuffix($hoststatus->getCurrentState());
        $key = $this->getKey($prefix);
        $this->Redis->addRecordToSet($key, $hoststatus->getHostname());
    }

    /**
     * @param string $suffix
     * @return string
     */
    public function getKey($suffix) {
        return sprintf('hosts_%s', $suffix);
    }

    /**
     * @param int $currentState (0,1,2)
     * @return string
     */
    public function getStateSuffix($currentState) {
        if ($currentState === 0) {
            return 'up';
        }

        if ($currentState === 1) {
            return 'down';
        }

        return 'unreachable';
    }

}