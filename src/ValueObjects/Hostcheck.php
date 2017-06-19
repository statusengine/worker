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


class Hostcheck implements DataStructInterface {

    /**
     * @var string
     */
    private $host_name;

    /**
     * @var bool
     */
    private $is_raw_check;

    /**
     * @var int
     */
    private $current_check_attempt;

    /**
     * @var int
     */
    private $max_check_attempts;

    /**
     * @var int
     */
    private $state;

    /**
     * @var int
     */
    private $state_type;

    /**
     * @var int
     */
    private $start_time;

    /**
     * @var int
     */
    private $end_time;

    /**
     * @var string
     */
    private $command;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var int
     */
    private $early_timeout;

    /**
     * @var float
     */
    private $execution_time;

    /**
     * @var float
     */
    private $latency;

    /**
     * @var int
     */
    private $return_code;

    /**
     * @var string
     */
    private $output;

    /**
     * @var string
     */
    private $long_output;

    /**
     * @var string
     */
    private $perfdata;

    /**
     * @const int
     */
    const NEBTYPE_HOSTCHECK_RAW_START = 802;

    /**
     * @const int
     */
    const NEBTYPE_HOSTCHECK_RAW_END = 803;


    /**
     * Hostcheck constructor.
     * @param \stdClass $hostcheck
     */
    public function __construct(\stdClass $hostcheck) {
        $this->host_name = $hostcheck->hostcheck->host_name;

        $this->is_raw_check = false;
        if ($hostcheck->type === self::NEBTYPE_HOSTCHECK_RAW_START || $hostcheck->type == self::NEBTYPE_HOSTCHECK_RAW_END) {
            $this->is_raw_check = true;
        }

        $this->current_check_attempt = (int)$hostcheck->hostcheck->current_attempt;
        $this->max_check_attempts = (int)$hostcheck->hostcheck->max_attempts;
        $this->state = (int)$hostcheck->hostcheck->state;
        $this->state_type = (int)$hostcheck->hostcheck->state_type;
        $this->start_time = (int)$hostcheck->hostcheck->start_time;
        $this->end_time = (int)$hostcheck->hostcheck->end_time;
        $this->command = $hostcheck->hostcheck->command_line;
        $this->timeout = (int)$hostcheck->hostcheck->timeout;
        $this->early_timeout = (int)$hostcheck->hostcheck->early_timeout;
        $this->execution_time = (float)$hostcheck->hostcheck->execution_time;
        $this->latency = (float)$hostcheck->hostcheck->latency;
        $this->return_code = (int)$hostcheck->hostcheck->return_code;
        $this->output = $hostcheck->hostcheck->output;
        $this->long_output = $hostcheck->hostcheck->long_output;
        $this->perfdata = $hostcheck->hostcheck->perf_data;
    }

    public function getHostName() {
        return $this->host_name;
    }

    public function getIsRawCheck() {
        return $this->is_raw_check;
    }

    /**
     * @return int
     */
    public function getCurrentCheckAttempt() {
        return $this->current_check_attempt;
    }

    /**
     * @return int
     */
    public function getMaxCheckAttempts() {
        return $this->max_check_attempts;
    }

    /**
     * @return int
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @return int
     */
    public function getStateType() {
        return $this->state_type;
    }

    /**
     * @return int
     */
    public function getStartTime() {
        return $this->start_time;
    }

    /**
     * @return int
     */
    public function getEndTime() {
        return $this->end_time;
    }

    /**
     * @return string
     */
    public function getCommand() {
        return $this->command;
    }

    /**
     * @return int
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * @return int
     */
    public function getEarlyTimeout() {
        return $this->early_timeout;
    }

    /**
     * @return float
     */
    public function getExecutionTime() {
        return $this->execution_time;
    }

    /**
     * @return float
     */
    public function getLatency() {
        return $this->latency;
    }

    /**
     * @return int
     */
    public function getReturnCode() {
        return $this->return_code;
    }

    /**
     * @return string
     */
    public function getOutput() {
        return $this->output;
    }

    /**
     * @return string
     */
    public function getLongOutput() {
        return $this->long_output;
    }

    /**
     * @return string
     */
    public function getPerfdata() {
        return $this->perfdata;
    }


    /**
     * @return array
     */
    public function serialize() {
        return [
            'host_name' => $this->host_name,
            'is_raw_check' => $this->is_raw_check,

            'current_check_attempt' => $this->current_check_attempt,
            'max_check_attempts' => $this->max_check_attempts,
            'state' => $this->state,
            'state_type' => $this->state_type,
            'command' => $this->command,
            'timeout' => $this->timeout,
            'early_timeout' => $this->early_timeout,
            'execution_time' => $this->execution_time,
            'latency' => $this->latency,
            'return_code' => $this->return_code,
            'output' => $this->output,
            'long_output' => $this->long_output,
            'perfdata' => $this->perfdata
        ];
    }

}
