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

use Crate\PDO\PDOCrateDB;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Crate;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\ValueObjects\Hoststatus;

class CrateHoststatus extends Crate\CrateModel {

    /**
     * @var string
     */
    protected $baseQuery = "INSERT INTO statusengine_hoststatus
    (hostname, status_update_time, output, long_output, perfdata, current_state, current_check_attempt, max_check_attempts, last_check, next_check, is_passive_check, last_state_change, last_hard_state_change, last_hard_state, is_hardstate, last_notification, next_notification, notifications_enabled, problem_has_been_acknowledged, acknowledgement_type, passive_checks_enabled, active_checks_enabled, event_handler_enabled, flap_detection_enabled, is_flapping, latency, execution_time, scheduled_downtime_depth, process_performance_data, obsess_over_host, normal_check_interval, retry_check_interval, check_timeperiod, node_name, last_time_up, last_time_down, last_time_unreachable, current_notification_number, percent_state_change, event_handler, check_command)
    VALUES%s
    ON CONFLICT (hostname) DO UPDATE SET status_update_time = excluded.status_update_time, output = excluded.output, long_output = excluded.long_output, perfdata = excluded.perfdata, current_state = excluded.current_state, current_check_attempt = excluded.current_check_attempt, max_check_attempts = excluded.max_check_attempts, last_check = excluded.last_check, next_check = excluded.next_check, is_passive_check = excluded.is_passive_check, last_state_change = excluded.last_state_change, last_hard_state_change = excluded.last_hard_state_change, last_hard_state = excluded.last_hard_state, is_hardstate = excluded.is_hardstate, last_notification = excluded.last_notification, next_notification = excluded.next_notification, notifications_enabled = excluded.notifications_enabled, problem_has_been_acknowledged = excluded.problem_has_been_acknowledged, acknowledgement_type = excluded.acknowledgement_type, passive_checks_enabled = excluded.passive_checks_enabled, active_checks_enabled = excluded.active_checks_enabled, event_handler_enabled = excluded.event_handler_enabled, flap_detection_enabled = excluded.flap_detection_enabled, is_flapping = excluded.is_flapping, latency = excluded.latency, execution_time = excluded.execution_time, scheduled_downtime_depth = excluded.scheduled_downtime_depth, process_performance_data = excluded.process_performance_data, obsess_over_host = excluded.obsess_over_host, normal_check_interval = excluded.normal_check_interval, retry_check_interval = excluded.retry_check_interval, check_timeperiod = excluded.check_timeperiod, node_name = excluded.node_name, last_time_up = excluded.last_time_up, last_time_down = excluded.last_time_down, last_time_unreachable = excluded.last_time_unreachable, current_notification_number = excluded.current_notification_number, percent_state_change = excluded.percent_state_change, event_handler = excluded.event_handler, check_command = excluded.check_command";

    /**
     * @var string
     */
    protected $baseValue = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

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
     * CrateHoststatus constructor.
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
         * @var Hoststatus $Hoststatus
         */

        $baseQuery = $this->buildQuery();
        $query = $this->CrateDB->prepare($baseQuery);

        $i = 1;
        foreach ($this->BulkInsertObjectStore->getObjects() as $key => $Hoststatus) {
            $query->bindValue($i++, $Hoststatus->getHostname());
            $query->bindValue($i++, $Hoststatus->getStatusUpdateTime());
            $query->bindValue($i++, $Hoststatus->getPluginOutput());
            $query->bindValue($i++, $Hoststatus->getLongPluginOutput());
            $query->bindValue($i++, $Hoststatus->getPerfdata());
            $query->bindValue($i++, $Hoststatus->getCurrentState());
            $query->bindValue($i++, $Hoststatus->getCurrentAttempt());
            $query->bindValue($i++, $Hoststatus->getMaxAttempts());
            $query->bindValue($i++, $Hoststatus->getLastCheck());
            $query->bindValue($i++, $Hoststatus->getNextCheck());
            $query->bindValue($i++, $Hoststatus->getIsActiveCheckResult(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getLastStateChange());
            $query->bindValue($i++, $Hoststatus->getLastHardStateChange());
            $query->bindValue($i++, $Hoststatus->getLastHardState());
            $query->bindValue($i++, $Hoststatus->isHardState(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getLastNotification());
            $query->bindValue($i++, $Hoststatus->getNextNotification());
            $query->bindValue($i++, $Hoststatus->isNotificationsEnabled(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->isProblemHasBeenAcknowledged(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getAcknowledgementType());
            $query->bindValue($i++, $Hoststatus->getAcceptPassiveChecks(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getChecksEnabled(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getEventHandlerEnabled(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getFlapDetectionEnabled(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getIsFlapping(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getLatency());
            $query->bindValue($i++, $Hoststatus->getExecutionTime());
            $query->bindValue($i++, $Hoststatus->getScheduledDowntimeDepth());
            $query->bindValue($i++, $Hoststatus->isProcessPerformanceData(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->isObsess(), PDOCrateDB::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getCheckInterval());
            $query->bindValue($i++, $Hoststatus->getRetryInterval());
            $query->bindValue($i++, $Hoststatus->getCheckPeriod());
            $query->bindValue($i++, $this->nodeName);

            $query->bindValue($i++, $Hoststatus->getLastTimeUp());
            $query->bindValue($i++, $Hoststatus->getLastTimeDown());
            $query->bindValue($i++, $Hoststatus->getLastTimeUnreachable());
            $query->bindValue($i++, $Hoststatus->getCurrentNotificationNumber());
            $query->bindValue($i++, $Hoststatus->getPercentStateChange());
            $query->bindValue($i++, $Hoststatus->getEventHandler());
            $query->bindValue($i++, $Hoststatus->getCheckCommand());
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
        $query = $this->CrateDB->prepare('DELETE FROM statusengine_hoststatus WHERE node_name=?');
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
