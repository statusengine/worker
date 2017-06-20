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

namespace Statusengine\Crate\SqlObjects;

use Crate\PDO\PDO;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Crate;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\ValueObjects\Hoststatus;

class CrateHoststatus extends Crate\CrateModel {

    /**
     * @var string
     */
    protected $baseQuery = "INSERT INTO statusengine_hoststatus
    (hostname, status_update_time, output, long_output, perfdata, current_state, current_check_attempt, max_check_attempts, last_check, next_check, is_passive_check, last_state_change, last_hard_state_change, last_hard_state, is_hardstate, last_notification, next_notification, notifications_enabled, problem_has_been_acknowledged, acknowledgement_type, passive_checks_enabled, active_checks_enabled, event_handler_enabled, flap_detection_enabled, is_flapping, latency, execution_time, scheduled_downtime_depth, process_performance_data, obsess_over_host, normal_check_interval, retry_check_interval, check_timeperiod, node_name)
    VALUES%s
    ON DUPLICATE KEY UPDATE status_update_time=VALUES(status_update_time), output=VALUES(output), long_output=VALUES(long_output), perfdata=VALUES(perfdata), current_state=VALUES(current_state), current_check_attempt=VALUES(current_check_attempt), max_check_attempts=VALUES(max_check_attempts), last_check=VALUES(last_check), next_check=VALUES(next_check), is_passive_check=VALUES(is_passive_check), last_state_change=VALUES(last_state_change), last_hard_state_change=VALUES(last_hard_state_change), last_hard_state=VALUES(last_hard_state), is_hardstate=VALUES(is_hardstate), last_notification=VALUES(last_notification), next_notification=VALUES(next_notification), notifications_enabled=VALUES(notifications_enabled), problem_has_been_acknowledged=VALUES(problem_has_been_acknowledged), acknowledgement_type=VALUES(acknowledgement_type), passive_checks_enabled=VALUES(passive_checks_enabled), active_checks_enabled=VALUES(active_checks_enabled), event_handler_enabled=VALUES(event_handler_enabled), flap_detection_enabled=VALUES(flap_detection_enabled), is_flapping=VALUES(is_flapping), latency=VALUES(latency), execution_time=VALUES(execution_time), scheduled_downtime_depth=VALUES(scheduled_downtime_depth), process_performance_data=VALUES(process_performance_data), obsess_over_host=VALUES(obsess_over_host), normal_check_interval=VALUES(normal_check_interval), retry_check_interval=VALUES(retry_check_interval), check_timeperiod=VALUES(check_timeperiod), node_name=VALUES(node_name)";

    /**
     * @var string
     */
    protected $baseValue = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

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
            $query->bindValue($i++, $Hoststatus->getIsActiveCheckResult(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getLastStateChange());
            $query->bindValue($i++, $Hoststatus->getLastHardStateChange());
            $query->bindValue($i++, $Hoststatus->getLastHardState());
            $query->bindValue($i++, $Hoststatus->isHardState(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getLastNotification());
            $query->bindValue($i++, $Hoststatus->getNextNotification());
            $query->bindValue($i++, $Hoststatus->isNotificationsEnabled(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->isProblemHasBeenAcknowledged(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getAcknowledgementType());
            $query->bindValue($i++, $Hoststatus->getAcceptPassiveChecks(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getChecksEnabled(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getEventHandlerEnabled(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getFlapDetectionEnabled(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getIsFlapping(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getLatency());
            $query->bindValue($i++, $Hoststatus->getExecutionTime());
            $query->bindValue($i++, $Hoststatus->getScheduledDowntimeDepth());
            $query->bindValue($i++, $Hoststatus->isProcessPerformanceData(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->isObsess(), PDO::PARAM_BOOL);
            $query->bindValue($i++, $Hoststatus->getCheckInterval());
            $query->bindValue($i++, $Hoststatus->getRetryInterval());
            $query->bindValue($i++, $Hoststatus->getCheckPeriod());
            $query->bindValue($i++, $this->nodeName);
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
