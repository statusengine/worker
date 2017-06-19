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


class Logentry implements DataStructInterface {

    /**
     * @var int
     */
    private $logentry_time;

    /**
     * @var int
     */
    private $entry_time;

    /**
     * @var int
     */
    private $logentry_type;

    /**
     * @var string
     */
    private $logentry_data;

    /**
     * Logentry constructor.
     * @param \stdClass $logentry
     */
    public function __construct(\stdClass $logentry) {
        $this->logentry_time = (int)$logentry->timestamp;
        $this->entry_time = (int)$logentry->logentry->entry_time;
        $this->logentry_type = (int)$logentry->logentry->data_type;
        $this->logentry_data = $logentry->logentry->data;

    }

    /**
     * @return int
     */
    public function getLogentryTime() {
        return $this->logentry_time;
    }

    /**
     * @return int
     */
    public function getEntryTime() {
        return $this->entry_time;
    }

    /**
     * @return int
     */
    public function getLogentryType() {
        return $this->logentry_type;
    }

    /**
     * @return string
     */
    public function getLogentryData() {
        return $this->logentry_data;
    }

    /**
     * @return array
     */
    public function serialize() {
        return [
            'logentry_time' => $this->logentry_time,
            'entry_time' => $this->entry_time,
            'logentry_type' => $this->logentry_type,
            'logentry_data' => $this->logentry_data
        ];
    }

}
