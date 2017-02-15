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


class Hoststatus implements StatusInterface {

    /**
     * @var string
     */
    private $host_name;

    /**
     * @var string
     */
    private $plugin_output;

    /**
     * @var string
     */
    private $long_plugin_output;

    /**
     * @var int
     */
    private $current_state;

    /**
     * @var int
     */
    private $current_attempt;

    /**
     * @var int
     */
    private $max_attempts;

    /**
     * @var int
     */
    private $last_check;

    /**
     * @var int
     */
    private $next_check;

    /**
     * @var int
     */
    private $check_type;

    /**
     * @var int
     */
    private $last_state_change;

    /**
     * @var int
     */
    private $last_hard_state_change;

    /**
     * @var int
     */
    private $last_hard_state;

    /**
     * @var bool
     */
    private $notifications_enabled;

    /**
     * @var bool
     */
    private $problem_has_been_acknowledged;

    /**
     * @var int
     */
    private $acknowledgement_type;

    /**
     * @var bool
     */
    private $accept_passive_checks;

    /**
     * @var bool
     */
    private $event_handler_enabled;

    /**
     * @var bool
     */
    private $checks_enabled;

    /**
     * @var bool
     */
    private $flap_detection_enabled;

    /**
     * @var bool
     */
    private $is_flapping;

    /**
     * @var float
     */
    private $latency;

    /**
     * @var float
     */
    private $execution_time;

    /**
     * @var int
     */
    private $scheduled_downtime_depth;

    /**
     * @var bool
     */
    private $process_performance_data;

    /**
     * @var bool
     */
    private $obsess;

    /**
     * @var float
     */
    private $check_interval;

    /**
     * @var float
     */
    private $retry_interval;

    /**
     * @var int
     */
    private $status_update_time;

    /**
     * @var string
     */
    private $perf_data;

    /**
     * @var int
     */
    private $last_notification;

    /**
     * @var int
     */
    private $next_notification;

    /**
     * @var bool
     */
    private $state_type;

    /**
     * @var string
     */
    private $check_period;

    /**
     * Hoststatus constructor.
     * @param \stdClass $hoststatus
     */
    public function __construct(\stdClass $hoststatus) {
        $this->host_name = $hoststatus->hoststatus->name;

        $this->status_update_time = (int)$hoststatus->timestamp;
        $this->plugin_output = $hoststatus->hoststatus->plugin_output;
        $this->long_plugin_output = $hoststatus->hoststatus->long_plugin_output;
        $this->current_state = (int)$hoststatus->hoststatus->current_state;
        $this->current_attempt = (int)$hoststatus->hoststatus->current_attempt;
        $this->max_attempts = (int)$hoststatus->hoststatus->max_attempts;
        $this->last_check = (int)$hoststatus->hoststatus->last_check;
        $this->next_check = (int)$hoststatus->hoststatus->next_check;
        $this->check_type = (int)$hoststatus->hoststatus->check_type;
        $this->last_state_change = (int)$hoststatus->hoststatus->last_state_change;
        $this->last_hard_state_change = (int)$hoststatus->hoststatus->last_hard_state_change;
        $this->last_hard_state = (int)$hoststatus->hoststatus->last_hard_state;
        $this->notifications_enabled = (bool)$hoststatus->hoststatus->notifications_enabled;
        $this->problem_has_been_acknowledged = (bool)$hoststatus->hoststatus->problem_has_been_acknowledged;
        $this->acknowledgement_type = (int)$hoststatus->hoststatus->acknowledgement_type;
        $this->accept_passive_checks = (bool)$hoststatus->hoststatus->accept_passive_checks;
        $this->event_handler_enabled = (bool)$hoststatus->hoststatus->event_handler_enabled;
        $this->checks_enabled = (bool)$hoststatus->hoststatus->checks_enabled;
        $this->flap_detection_enabled = (bool)$hoststatus->hoststatus->flap_detection_enabled;
        $this->is_flapping = (bool)$hoststatus->hoststatus->is_flapping;
        $this->latency = (float)$hoststatus->hoststatus->latency;
        $this->execution_time = (float)$hoststatus->hoststatus->execution_time;
        $this->scheduled_downtime_depth = (int)$hoststatus->hoststatus->scheduled_downtime_depth;
        $this->process_performance_data = (bool)$hoststatus->hoststatus->process_performance_data;
        $this->obsess = (bool)$hoststatus->hoststatus->obsess;
        $this->check_interval = (float)$hoststatus->hoststatus->check_interval;
        $this->retry_interval = (float)$hoststatus->hoststatus->retry_interval;
        $this->perf_data = $hoststatus->hoststatus->perf_data;
        $this->last_notification = (int)$hoststatus->hoststatus->last_notification;
        $this->next_notification = (int)$hoststatus->hoststatus->next_notification;
        $this->state_type = (bool)$hoststatus->hoststatus->state_type;
        $this->check_period = $hoststatus->hoststatus->check_period;
    }

    /**
     * @return int
     */
    public function getExpires() {
        return ($this->next_check - time()) + 300;
    }

    /**
     * @return string
     */
    public function getKey() {
        return 'hoststatus_' . $this->host_name;
    }

    /**
     * @return string
     */
    public function getHostname() {
        return $this->host_name;
    }

    /**
     * @return int
     */
    public function getCurrentState() {
        return $this->current_state;
    }

    /**
     * @return string
     */
    public function getPluginOutput() {
        return $this->plugin_output;
    }

    /**
     * @return string
     */
    public function getLongPluginOutput() {
        return $this->long_plugin_output;
    }

    /**
     * @return int
     */
    public function getCurrentAttempt() {
        return $this->current_attempt;
    }

    /**
     * @return int
     */
    public function getMaxAttempts() {
        return $this->max_attempts;
    }

    /**
     * @return int
     */
    public function getLastCheck() {
        return $this->last_check;
    }

    /**
     * @return int
     */
    public function getNextCheck() {
        return $this->next_check;
    }

    /**
     * @return bool
     */
    public function getIsActiveCheckResult(){
        if($this->check_type == 0){
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getLastStateChange() {
        return $this->last_state_change;
    }

    /**
     * @return int
     */
    public function getLastHardStateChange() {
        return $this->last_hard_state_change;
    }

    /**
     * @return int
     */
    public function getLastHardState() {
        return $this->last_hard_state;
    }

    /**
     * @return bool
     */
    public function isNotificationsEnabled() {
        return $this->notifications_enabled;
    }

    /**
     * @return bool
     */
    public function isProblemHasBeenAcknowledged() {
        return $this->problem_has_been_acknowledged;
    }

    /**
     * @return int
     */
    public function getAcknowledgementType() {
        return $this->acknowledgement_type;
    }

    /**
     * @return bool
     */
    public function getAcceptPassiveChecks() {
        return $this->accept_passive_checks;
    }

    /**
     * @return bool
     */
    public function getEventHandlerEnabled() {
        return $this->event_handler_enabled;
    }

    /**
     * @return bool
     */
    public function getChecksEnabled() {
        return $this->checks_enabled;
    }

    /**
     * @return bool
     */
    public function getFlapDetectionEnabled() {
        return $this->flap_detection_enabled;
    }

    /**
     * @return bool
     */
    public function getIsFlapping() {
        return $this->is_flapping;
    }

    /**
     * @return float
     */
    public function getLatency() {
        return $this->latency;
    }

    /**
     * @return float
     */
    public function getExecutionTime() {
        return $this->execution_time;
    }

    /**
     * @return int
     */
    public function getScheduledDowntimeDepth() {
        return $this->scheduled_downtime_depth;
    }

    /**
     * @return bool
     */
    public function isProcessPerformanceData() {
        return $this->process_performance_data;
    }

    /**
     * @return bool
     */
    public function isObsess() {
        return $this->obsess;
    }

    /**
     * @return float
     */
    public function getCheckInterval() {
        return $this->check_interval;
    }

    /**
     * @return float
     */
    public function getRetryInterval() {
        return $this->retry_interval;
    }

    /**
     * @return int
     */
    public function getStatusUpdateTime() {
        return $this->status_update_time;
    }

    /**
     * @return string
     */
    public function getPerfData() {
        return $this->perf_data;
    }

    /**
     * @return int
     */
    public function getLastNotification() {
        return $this->last_notification;
    }

    /**
     * @return int
     */
    public function getNextNotification() {
        return $this->next_notification;
    }

    /**
     * @return bool
     */
    public function isHardState() {
        return $this->state_type;
    }

    /**
     * @return string
     */
    public function getCheckPeriod() {
        return $this->check_period;
    }

    /**
     * @return array
     */
    public function serialize() {
        return [
            'plugin_output' => $this->plugin_output,
            'long_plugin_output' => $this->long_plugin_output,
            'current_state' => $this->current_state,
            'current_attempt' => $this->current_attempt,
            'max_attempts' => $this->max_attempts,
            'last_check' => $this->last_check,
            'next_check' => $this->next_check,
            'last_state_change' => $this->last_state_change,
            'last_hard_state_change' => $this->last_hard_state_change,
            'last_hard_state' => $this->last_hard_state,
            'notifications_enabled' => $this->notifications_enabled,
            'problem_has_been_acknowledged' => (int)$this->problem_has_been_acknowledged,
            'acknowledgement_type' => $this->acknowledgement_type,
            'accept_passive_checks' => (int)$this->accept_passive_checks,
            'event_handler_enabled' => (int)$this->event_handler_enabled,
            'checks_enabled' => (int)$this->checks_enabled,
            'flap_detection_enabled' => (int)$this->flap_detection_enabled,
            'is_flapping' => (int)$this->is_flapping,
            'latency' => $this->latency,
            'execution_time' => $this->execution_time,
            'scheduled_downtime_depth' => $this->scheduled_downtime_depth,
            'process_performance_data' => (int)$this->process_performance_data,
            'obsess' => (int)$this->obsess,
            'check_interval' => $this->check_interval,
            'retry_interval' => $this->retry_interval
        ];
    }

}
