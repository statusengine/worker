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


class Statechange implements DataStructInterface {

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
    private $state_time;

    /**
     * @var int
     */
    private $state_change;

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
    private $current_check_attempt;

    /**
     * @var int
     */
    private $max_check_attempts;

    /**
     * @var int
     */
    private $last_state;

    /**
     * @var int
     */
    private $last_hard_state;

    /**
     * @var int
     */
    private $output;

    /**
     * @var int
     */
    private $long_output;

    /**
     * Statechange constructor.
     * @param \stdClass $statechange
     */
    public function __construct(\stdClass $statechange) {
        $this->host_name = $statechange->statechange->host_name;
        $this->service_description = $statechange->statechange->service_description;

        $this->state_time = (int)$statechange->timestamp;

        $this->state_change = (int)$statechange->statechange->statechange_type;
        $this->state = (int)$statechange->statechange->state;
        $this->state_type = (int)$statechange->statechange->state_type;
        $this->current_check_attempt = (int)$statechange->statechange->current_attempt;
        $this->max_check_attempts = (int)$statechange->statechange->max_attempts;
        $this->last_state = (int)$statechange->statechange->last_state;
        $this->last_hard_state = (int)$statechange->statechange->last_hard_state;
        $this->output = $statechange->statechange->output;
        $this->long_output = $statechange->statechange->long_output;

    }

    /**
     * @return boolean
     */
    public function isHostRecord() {
        if ($this->service_description === null || $this->service_description === '') {
            return true;
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function isServiceRecord() {
        return !$this->isHostRecord();
    }

    /**
     * @return string
     */
    public function getHostname() {
        return $this->host_name;
    }

    /**
     * @return string|null
     */
    public function getServiceDescription() {
        return $this->service_description;
    }

    /**
     * @return int
     */
    public function getStateChange() {
        return $this->state_change;
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
    public function getCurrentCheckAttempt() {
        return $this->current_check_attempt;
    }

    /**
     * @return int
     */
    public function getMaxCheckAttempt() {
        return $this->max_check_attempts;
    }

    /**
     * @return int
     */
    public function getLastState() {
        return $this->last_state;
    }

    /**
     * @return int
     */
    public function getLastHardState() {
        return $this->last_hard_state;
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
     * @return int
     */
    public function getStateTime() {
        return $this->state_time;
    }

    /**
     * @return array
     */
    public function serialize() {
        return [
            'host_name' => $this->host_name,
            'service_description' => $this->service_description,

            'state_change' => $this->state_change,
            'state' => $this->state,
            'state_type' => $this->state_type,
            'current_check_attempt' => $this->current_check_attempt,
            'max_check_attempts' => $this->max_check_attempts,
            'last_state' => $this->last_state,
            'last_hard_state' => $this->last_hard_state,
            'output' => $this->output,
            'long_output' => $this->long_output
        ];
    }

}
