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


class Servicestatus implements StatusInterface {

    /**
     * @var string
     */
    private $host_name;

    /**
     * @var string
     */
    private $description;

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
     * @var int
     */
    private $last_time_ok;

    /**
     * @var int
     */
    private $last_time_warning;

    /**
     * @var int
     */
    private $last_time_unknown;

    /**
     * @var int
     */
    private $last_time_critical;

    /**
     * @var int
     */
    private $current_notification_number;

    /**
     * @var double
     */
    private $percent_state_change;

    /**
     * @var string
     */
    private $event_handler;

    /**
     * @var string
     */
    private $check_command;

    /**
     * Servicestatus constructor.
     * @param \stdClass $servicestatus
     */
    public function __construct(\stdClass $servicestatus) {
        $this->host_name = $servicestatus->servicestatus->host_name;
        $this->description = $servicestatus->servicestatus->description;

        $this->status_update_time = (int)$servicestatus->timestamp;
        $this->plugin_output = $servicestatus->servicestatus->plugin_output;
        $this->long_plugin_output = $servicestatus->servicestatus->long_plugin_output;
        $this->current_state = (int)$servicestatus->servicestatus->current_state;
        $this->current_attempt = (int)$servicestatus->servicestatus->current_attempt;
        $this->max_attempts = (int)$servicestatus->servicestatus->max_attempts;
        $this->last_check = (int)$servicestatus->servicestatus->last_check;
        $this->next_check = (int)$servicestatus->servicestatus->next_check;
        $this->check_type = (int)$servicestatus->servicestatus->check_type;
        $this->last_state_change = (int)$servicestatus->servicestatus->last_state_change;
        $this->last_hard_state_change = (int)$servicestatus->servicestatus->last_hard_state_change;
        $this->last_hard_state = (int)$servicestatus->servicestatus->last_hard_state;
        $this->notifications_enabled = (bool)$servicestatus->servicestatus->notifications_enabled;
        $this->problem_has_been_acknowledged = (bool)$servicestatus->servicestatus->problem_has_been_acknowledged;

        // 0 = ACKNOWLEDGEMENT_NONE
        // 1 = ACKNOWLEDGEMENT_NORMAL
        // 2 = ACKNOWLEDGEMENT_STICKY
        $this->acknowledgement_type = (int)$servicestatus->servicestatus->acknowledgement_type;
        $this->accept_passive_checks = (bool)$servicestatus->servicestatus->accept_passive_checks;
        $this->event_handler_enabled = (bool)$servicestatus->servicestatus->event_handler_enabled;
        $this->checks_enabled = (bool)$servicestatus->servicestatus->checks_enabled;
        $this->flap_detection_enabled = (bool)$servicestatus->servicestatus->flap_detection_enabled;
        $this->is_flapping = (bool)$servicestatus->servicestatus->is_flapping;
        $this->latency = (float)$servicestatus->servicestatus->latency;
        $this->execution_time = (float)$servicestatus->servicestatus->execution_time;
        $this->scheduled_downtime_depth = (int)$servicestatus->servicestatus->scheduled_downtime_depth;
        $this->process_performance_data = (bool)$servicestatus->servicestatus->process_performance_data;
        $this->obsess = (bool)$servicestatus->servicestatus->obsess;
        $this->check_interval = (float)$servicestatus->servicestatus->check_interval;
        $this->retry_interval = (float)$servicestatus->servicestatus->retry_interval;
        $this->perf_data = $servicestatus->servicestatus->perf_data;
        $this->last_notification = (int)$servicestatus->servicestatus->last_notification;
        $this->next_notification = (int)$servicestatus->servicestatus->next_notification;

        $this->state_type = (bool)$servicestatus->servicestatus->state_type;
        $this->check_period = $servicestatus->servicestatus->check_period;

        $this->last_time_ok = (int)$servicestatus->servicestatus->last_time_ok;
        $this->last_time_warning = (int)$servicestatus->servicestatus->last_time_warning;
        $this->last_time_critical = (int)$servicestatus->servicestatus->last_time_critical;
        $this->last_time_unknown = (int)$servicestatus->servicestatus->last_time_unknown;
        $this->current_notification_number = (int)$servicestatus->servicestatus->current_notification_number;
        $this->percent_state_change = (double)$servicestatus->servicestatus->percent_state_change;
        $this->event_handler = $servicestatus->servicestatus->event_handler;
        $this->check_command = $servicestatus->servicestatus->check_command;
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
        return 'servicestatus_' . $this->host_name . '_' . $this->description;
    }

    /**
     * @return string
     */
    public function getPlainKey() {
        return $this->host_name . '_' . $this->description;
    }

    /**
     * @return string
     */
    public function getHostname() {
        return $this->host_name;
    }

    /**
     * @return string
     */
    public function getServicedescription() {
        return $this->description;
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
    public function getDescription() {
        return $this->description;
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
    public function getIsActiveCheckResult() {
        if ($this->check_type == 0) {
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
     * @return boolean
     */
    public function isNotificationsEnabled() {
        return $this->notifications_enabled;
    }

    /**
     * @return boolean
     */
    public function isProblemHasBeenAcknowledged() {
        return $this->problem_has_been_acknowledged;
    }

    /**
     * 0 = ACKNOWLEDGEMENT_NONE
     * 1 = ACKNOWLEDGEMENT_NORMAL
     * 2 = ACKNOWLEDGEMENT_STICKY
     * @return int
     */
    public function getAcknowledgementType() {
        return $this->acknowledgement_type;
    }

    /**
     * @return boolean
     */
    public function getAcceptPassiveChecks() {
        return $this->accept_passive_checks;
    }

    /**
     * @return boolean
     */
    public function getEventHandlerEnabled() {
        return $this->event_handler_enabled;
    }

    /**
     * @return boolean
     */
    public function getChecksEnabled() {
        return $this->checks_enabled;
    }

    /**
     * @return boolean
     */
    public function getFlapDetectionEnabled() {
        return $this->flap_detection_enabled;
    }

    /**
     * @return boolean
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
     * @return boolean
     */
    public function isProcessPerformanceData() {
        return $this->process_performance_data;
    }

    /**
     * @return boolean
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
    public function getPerfdata() {
        return $this->perf_data;
    }

    /**
     * @return int
     */
    public function getNextNotification() {
        return $this->next_notification;
    }

    /**
     * @return int
     */
    public function getLastNotification() {
        return $this->last_notification;
    }

    /**
     * @return bool
     */
    public function isHardState() {
        return $this->state_type;
    }

    public function getCheckPeriod() {
        return $this->check_period;
    }

    /**
     * @return int
     */
    public function getLastTimeOk() {
        return $this->last_time_ok;
    }

    /**
     * @return int
     */
    public function getLastTimeWarning() {
        return $this->last_time_warning;
    }

    /**
     * @return int
     */
    public function getLastTimeUnknown() {
        return $this->last_time_unknown;
    }

    /**
     * @return int
     */
    public function getLastTimeCritical() {
        return $this->last_time_critical;
    }

    /**
     * @return int
     */
    public function getCurrentNotificationNumber() {
        return $this->current_notification_number;
    }

    /**
     * @return float
     */
    public function getPercentStateChange() {
        return $this->percent_state_change;
    }

    /**
     * @return string
     */
    public function getEventHandler() {
        return $this->event_handler;
    }

    /**
     * @return string
     */
    public function getCheckCommand() {
        return $this->check_command;
    }

    /**
     * @return array
     */
    public function serialize() {
        return [
            'plugin_output'                 => $this->plugin_output,
            'long_plugin_output'            => $this->long_plugin_output,
            'current_state'                 => $this->current_state,
            'current_attempt'               => $this->current_attempt,
            'max_attempts'                  => $this->max_attempts,
            'last_check'                    => $this->last_check,
            'next_check'                    => $this->next_check,
            'last_state_change'             => $this->last_state_change,
            'last_hard_state_change'        => $this->last_hard_state_change,
            'last_hard_state'               => $this->last_hard_state,
            'notifications_enabled'         => $this->notifications_enabled,
            'problem_has_been_acknowledged' => (int)$this->problem_has_been_acknowledged,
            'acknowledgement_type'          => $this->acknowledgement_type,
            'accept_passive_checks'         => (int)$this->accept_passive_checks,
            'event_handler_enabled'         => (int)$this->event_handler_enabled,
            'checks_enabled'                => (int)$this->checks_enabled,
            'flap_detection_enabled'        => (int)$this->flap_detection_enabled,
            'is_flapping'                   => (int)$this->is_flapping,
            'latency'                       => $this->latency,
            'execution_time'                => $this->execution_time,
            'scheduled_downtime_depth'      => $this->scheduled_downtime_depth,
            'process_performance_data'      => (int)$this->process_performance_data,
            'obsess'                        => (int)$this->obsess,
            'check_interval'                => $this->check_interval,
            'retry_interval'                => $this->retry_interval,
            'check_period'                  => $this->check_period,
            'last_time_ok'                  => $this->last_time_ok,
            'last_time_warning'             => $this->last_time_warning,
            'last_time_critical'            => $this->last_time_critical,
            'last_time_unknown'             => $this->last_time_unknown,
            'current_notification_number'   => $this->current_notification_number,
            'percent_state_change'          => $this->percent_state_change,
            'event_handler'                 => $this->event_handler,
            'check_command'                 => $this->check_command
        ];
    }
}
