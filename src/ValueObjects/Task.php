<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2018  Daniel Ziegler
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


class Task {

    /**
     * @var int
     */
    private $entry_time;

    /**
     * @var string
     */
    private $node_name;

    /**
     * @var string
     */
    private $payload;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $uuid;

    /**
     * Task constructor.
     * @param int $entry_time
     * @param string $node_name
     * @param string $payload
     * @param string $type
     * @param string $uuid
     */
    public function __construct($entry_time, $node_name, $payload, $type, $uuid) {
        $this->entry_time = $entry_time;
        $this->node_name = $node_name;
        $this->payload = $payload;
        $this->type = $type;
        $this->uuid = $uuid;
    }

    /**
     * @return int
     */
    public function getEntryTime() {
        return $this->entry_time;
    }

    /**
     * @return string
     */
    public function getNodeName() {
        return $this->node_name;
    }

    /**
     * @return string
     */
    public function getPayload() {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getUuid() {
        return $this->uuid;
    }

}