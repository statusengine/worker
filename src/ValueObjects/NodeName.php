<?php
/**
 * Statusengine UI
 * Copyright (C) 2017  Daniel Ziegler
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


class NodeName {

    /**
     * @var string
     */
    private $nodeName;

    /**
     * @var string
     */
    private $nodeVersion;

    /**
     * @var int
     */
    private $nodeStartTime;

    /**
     * NodeName constructor.
     * @param $nodeName
     * @param $nodeVersion
     * @param $nodeStartTime
     */
    private function __construct($nodeName, $nodeVersion, $nodeStartTime) {
        $this->nodeName = $nodeName;
        $this->nodeVersion = $nodeVersion;
        $this->nodeStartTime = $nodeStartTime;
    }

    /**
     * @param $record
     * @return NodeName
     */
    public static function fromCrateDb($record) {
        return new self($record['node_name'], $record['node_version'], $record['node_start_time']);
    }

    /**
     * @param $record
     * @return NodeName
     */
    public static function fromMysqlDb($record) {
        return new self($record['node_name'], $record['node_version'], $record['node_start_time']);
    }

    /**
     * @return string
     */
    public function getNodeName() {
        return $this->nodeName;
    }

    /**
     * @return string
     */
    public function getNodeVersion() {
        return $this->nodeVersion;
    }

    /**
     * @return int
     */
    public function getNodeStartTime() {
        return $this->nodeStartTime;
    }

    /**
     * @return false|string
     */
    public function getNodeStartTimeHuman() {
        if ($this->nodeStartTime > 0) {
            return date('Y-m-d H:i:s', $this->nodeStartTime);
        }

        return 'never';
    }

}