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

namespace Statusengine\ValueObjects;


class PerfdataRaw implements DataStructInterface {

    /**
     * @var string
     */
    private $host_name;

    /**
     * @var string
     */
    private $service_description;


    /**
     * @var string
     */
    private $perfdata;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * PerfdataRaw constructor.
     * @param \stdClass $perfdata
     */
    public function __construct(\stdClass $perfdata) {
        $this->host_name = $perfdata->servicecheck->host_name;
        $this->service_description = $perfdata->servicecheck->service_description;
        $this->perfdata = trim($perfdata->servicecheck->perf_data);
        $this->timestamp = $perfdata->servicecheck->start_time;
    }

    /**
     * @return bool
     */
    public function isEmpty(){
        if($this->perfdata == ''){
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getHostName(){
        return $this->host_name;
    }

    /**
     * @return string
     */
    public function getServiceDescription(){
        return $this->service_description;
    }

    /**
     * @return string
     */
    public function getPerfdata(){
        return $this->perfdata;
    }

    /**
     * @return int
     */
    public function getTimestamp(){
        return $this->timestamp;
    }

    /**
     * @return array
     */
    public function serialize(){
        return [
            'hostname' => $this->host_name,
            'service_description' => $this->service_description,
            'perfdata' => $this->perfdata
        ];
    }

}