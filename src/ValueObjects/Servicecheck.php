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


class Servicecheck implements DataStructInterface {

    /**
     * @var string
     */
    private $host_name;

    /**
     * @var string
     */
    private $service_description;

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
     * Statechange constructor.
     * @param \stdClass $servicecheck
     */
    public function __construct(\stdClass $servicecheck) {
        $this->host_name = $servicecheck->servicecheck->host_name;
        $this->service_description = $servicecheck->servicecheck->service_description;


        $this->current_check_attempt = (int)$servicecheck->servicecheck->current_attempt;
        $this->max_check_attempts = (int)$servicecheck->servicecheck->max_attempts;
        $this->state = (int)$servicecheck->servicecheck->state;
        $this->state_type = (int)$servicecheck->servicecheck->state_type;
        $this->start_time = (int)$servicecheck->servicecheck->start_time;
        $this->end_time = (int)$servicecheck->servicecheck->end_time;
        $this->command = $servicecheck->servicecheck->command_line;
        $this->timeout = (int)$servicecheck->servicecheck->timeout;
        $this->early_timeout = (int)$servicecheck->servicecheck->early_timeout;
        $this->execution_time = (float)$servicecheck->servicecheck->execution_time;
        $this->latency = (float)$servicecheck->servicecheck->latency;
        $this->return_code = (int)$servicecheck->servicecheck->return_code;
        $this->output = $servicecheck->servicecheck->output;
        $this->long_output = $servicecheck->servicecheck->long_output;
        $this->perfdata = $servicecheck->servicecheck->perf_data;
    }

    public function getHostName() {
        return $this->host_name;
    }

    public function getServiceDescription() {
        return $this->service_description;
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
            'service_description' => $this->service_description,

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
