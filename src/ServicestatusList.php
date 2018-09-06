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


use Statusengine\ValueObjects\Servicestatus;
use Statusengine\Redis\Redis;

class ServicestatusList {

    /**
     * @var Redis
     */
    private $Redis;


    /**
     * @var array
     */
    private $states = ['ok', 'warning', 'critical', 'unknown'];

    public function __construct(Redis $Redis) {
        $this->Redis = $Redis;
    }

    public function updateList(Servicestatus $servicestatus) {
        //Delete old status record
        $this->removeFromAll($servicestatus->getPlainKey());

        //Save new status record
        $this->add($servicestatus);
    }

    /**
     * @param string $hostNameAndServiceDesc you like to remove
     */
    public function removeFromAll($hostNameAndServiceDesc) {
        foreach ($this->states as $state) {
            $key = $this->getKey($state);
            $this->remove($key, $hostNameAndServiceDesc);
        }
    }

    /**
     * @param string $key of the list with all services in given state key
     * @param string $hostNameAndServiceDesc
     */
    public function remove($key, $hostNameAndServiceDesc) {
        $this->Redis->removeRecordFromSet($key, $hostNameAndServiceDesc);
    }

    /**
     * @param Servicestatus $servicestatus
     */
    public function add(Servicestatus $servicestatus) {
        $prefix = $this->getStateSuffix($servicestatus->getCurrentState());
        $key = $this->getKey($prefix);
        $this->Redis->addRecordToSet($key, $servicestatus->getPlainKey());
    }

    /**
     * @param string $suffix
     * @return string
     */
    public function getKey($suffix) {
        return sprintf('services_%s', $suffix);
    }

    /**
     * @param int $currentState (0,1,2)
     * @return string
     */
    public function getStateSuffix($currentState) {
        if ($currentState === 0) {
            return 'ok';
        }

        if ($currentState === 1) {
            return 'warning';
        }

        if ($currentState === 2) {
            return 'critical';
        }

        return 'unknown';
    }

}