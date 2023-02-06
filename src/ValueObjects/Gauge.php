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

namespace Statusengine\ValueObjects;


class Gauge {

    /**
     * @var
     */
    private $host_name;

    /**
     * @var string
     */
    private $service_desc;

    /**
     * @var string
     */
    private $label;

    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var int|null
     */
    private $warning;

    /**
     * @var int|null
     */
    private $critical;

    /**
     * @var int|null
     */
    private $min;

    /**
     * @var int|null
     */
    private $max;


    /**
     * Gauge constructor.
     * @param $host_name
     * @param $service_desc
     * @param string $label
     * @param float $value
     * @param $timestamp
     * @param string $unit
     * @param null $warning
     * @param null $critical
     * @param null $min
     * @param null $max
     */
    public function __construct(
        string $host_name,
        string $service_desc,
        string $label = '',
        float $value = 0.0,
        int $timestamp = 0,
        ?string $unit = '',
        ?float $warning = null,
        ?float $critical = null,
        ?float $min = null,
        ?float $max = null
    ) {
        $this->host_name = $host_name;
        $this->service_desc = $service_desc;
        $this->label = $label;
        $this->value = $value;
        $this->warning = $warning;
        $this->critical = $critical;
        $this->min = $min;
        $this->max = $max;
        $this->unit = $unit;
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getHostName() {
        return $this->host_name;
    }

    /**
     * @return string
     */
    public function getServiceDescription() {
        return $this->service_desc;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @return float
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return int|null
     */
    public function getWarning() {
        return $this->warning;
    }

    /**
     * @return int|null
     */
    public function getCritical() {
        return $this->critical;
    }

    /**
     * @return int|null
     */
    public function getMin() {
        return $this->min;
    }

    /**
     * @return string
     */
    public function getUnit() {
        return $this->unit;
    }

    /**
     * @return int
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * @return int|null
     */
    public function getMax() {
        return $this->max;
    }
}
