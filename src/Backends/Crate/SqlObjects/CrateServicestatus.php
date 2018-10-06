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

namespace Statusengine\Crate\SqlObjects;

use Crate\PDO\PDO;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Crate;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\ValueObjects\Servicestatus;

class CrateServicestatus extends Crate\CrateModel {

    /**
     * @var string
     */
    protected $baseQuery = "INSERT INTO statusengine_servicestatus
    (hostname, service_description, status_update_time, output, long_output, perfdata, current_state, current_check_attempt, max_check_attempts, last_check, next_check, is_passive_check, last_state_change, last_hard_state_change, last_hard_state, is_hardstate, last_notification, next_notification, notifications_enabled, problem_has_been_acknowledged, acknowledgement_type, passive_checks_enabled, active_checks_enabled, event_handler_enabled, flap_detection_enabled, is_flapping, latency, execution_time, scheduled_downtime_depth, process_performance_data, obsess_over_service, normal_check_interval, retry_check_interval, check_timeperiod, node_name, last_time_ok, last_time_warning, last_time_critical, last_time_unknown, current_notification_number, percent_state_change, event_handler, check_command)
    VALUES%s
    ON CONFLICT (hostname, service_description) DO UPDATE SET status_update_time = excluded.status_update_time, output = excluded.output, long_output = excluded.long_output, perfdata = excluded.perfdata, current_state = excluded.current_state, current_check_attempt = excluded.current_check_attempt, max_check_attempts = excluded.max_check_attempts, last_check = excluded.last_check, next_check = excluded.next_check, is_passive_check = excluded.is_passive_check, last_state_change = excluded.last_state_change, last_hard_state_change = excluded.last_hard_state_change, last_hard_state = excluded.last_hard_state, is_hardstate = excluded.is_hardstate, last_notification = excluded.last_notification, next_notification = excluded.next_notification, notifications_enabled = excluded.notifications_enabled, problem_has_been_acknowledged = excluded.problem_has_been_acknowledged, acknowledgement_type = excluded.acknowledgement_type, passive_checks_enabled = excluded.passive_checks_enabled, active_checks_enabled = excluded.active_checks_enabled, event_handler_enabled = excluded.event_handler_enabled, flap_detection_enabled = excluded.flap_detection_enabled, is_flapping = excluded.is_flapping, latency = excluded.latency, execution_time = excluded.execution_time, scheduled_downtime_depth = excluded.scheduled_downtime_depth, process_performance_data = excluded.process_performance_data, obsess_over_service = excluded.obsess_over_service, normal_check_interval = excluded.normal_check_interval, retry_check_interval = excluded.retry_check_interval, check_timeperiod = excluded.check_timeperiod, node_name = excluded.node_name, last_time_ok = excluded.last_time_ok, last_time_warning = excluded.last_time_warning, last_time_critical = excluded.last_time_critical, last_time_unknown = excluded.last_time_unknown, current_notification_number = excluded.current_notification_number, percent_state_change = excluded.percent_state_change, event_handler = excluded.event_handler, check_command = excluded.check_command";

    /**
     * @var string
     */
    protected $baseValue = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var Crate\Crate
     */
    protected $CrateDB;

    /**
     * @var BulkInsertObjectStore
     */
    protected $BulkInsertObjectStore;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * CrateServicestatus constructor.
     * @param Crate\Crate $CrateDB
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     * @param string $nodeName
     */
    public function __construct(Crate\Crate $CrateDB, BulkInsertObjectStore $BulkInsertObjectStore, $nodeName){
        $this->CrateDB = $CrateDB;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
        $this->nodeName = $nodeName;

    }

    /**
     * @param bool $isRecursion
     * @return bool
     */
    public function insert($isRecursion = false){
        /**
         * @var Servicestatus $Servicestatus
         */

        $baseQuery = $this->buildQuery();
        $query = $this->CrateDB->prepare($baseQuery);

        $i = 1;
        foreach ($this->BulkInsertObjectStore->getObjects() as $key => $Servicestatus) {
            $query->bindValue($i++, $Servicestatus->getHostname());
            $query->bindValue($i++, $Servicestatus->getServicedescription());
            $query->bindValue($i++, $Servicestatus->getStatusUpdateTime());
            $query->bindValue($i++, $Servicestatus->getPluginOutput());
            $query->bindValue($i++, $Servicestatus->getLongPluginOutput());
            $query->bindValue($i++, $Servicestatus->getPerfdata());
            $query->bindValue($i++, $Servicestatus->getCurrentState());
            $query->bindValue($i++, $Servicestatus->getCurrentAttempt());
            $query->bindValue($i++, $Servicestatus->getMaxAttempts());
            $query->bindValue($i++, $Servicestatus->getLastCheck());
            $query->bindValue($i++, $Servicestatus->getNextCheck());
            $query->bindValue($i++, $Servicestatus->getIsActiveCheckResult(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getLastStateChange());
            $query->bindValue($i++, $Servicestatus->getLastHardStateChange());
            $query->bindValue($i++, $Servicestatus->getLastHardState());
            $query->bindValue($i++, $Servicestatus->isHardState(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getLastNotification());
            $query->bindValue($i++, $Servicestatus->getNextNotification());
            $query->bindValue($i++, $Servicestatus->isNotificationsEnabled(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->isProblemHasBeenAcknowledged(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getAcknowledgementType());
            $query->bindValue($i++, $Servicestatus->getAcceptPassiveChecks(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getChecksEnabled(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getEventHandlerEnabled(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getFlapDetectionEnabled(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getIsFlapping(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getLatency());
            $query->bindValue($i++, $Servicestatus->getExecutionTime());
            $query->bindValue($i++, $Servicestatus->getScheduledDowntimeDepth());
            $query->bindValue($i++, $Servicestatus->isProcessPerformanceData(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->isObsess(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Servicestatus->getCheckInterval());
            $query->bindValue($i++, $Servicestatus->getRetryInterval());
            $query->bindValue($i++, $Servicestatus->getCheckPeriod());
            $query->bindValue($i++, $this->nodeName);

            $query->bindValue($i++, $Servicestatus->getLastTimeOk());
            $query->bindValue($i++, $Servicestatus->getLastTimeWarning());
            $query->bindValue($i++, $Servicestatus->getLastTimeCritical());
            $query->bindValue($i++, $Servicestatus->getLastTimeUnknown());
            $query->bindValue($i++, $Servicestatus->getCurrentNotificationNumber());
            $query->bindValue($i++, $Servicestatus->getPercentStateChange());
            $query->bindValue($i++, $Servicestatus->getEventHandler());
            $query->bindValue($i++, $Servicestatus->getCheckCommand());

        }

        try {
            return $this->CrateDB->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->insert(true);
            }
        }
    }

    /**
     * @param bool $isRecursion
     * @return bool
     */
    public function truncate($isRecursion = false){
        $query = $this->CrateDB->prepare('DELETE FROM statusengine_servicestatus WHERE node_name=?');
        $query->bindValue(1, $this->nodeName);
        try {
            return $this->CrateDB->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->insert(true);
            }
        }
    }

}
