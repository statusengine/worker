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

namespace Statusengine\Mysql\SqlObjects;

use Statusengine\BulkInsertObjectStore;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Mysql\MySQL;
use Statusengine\Mysql\MysqlModel;
use Statusengine\ValueObjects\Servicestatus;

class MysqlServicestatus extends MysqlModel {

    /**
     * @var string
     */
    protected $baseQuery = "INSERT INTO statusengine_servicestatus
    (hostname, service_description, status_update_time, output, long_output, perfdata, current_state, current_check_attempt, max_check_attempts, last_check, next_check, is_passive_check, last_state_change, last_hard_state_change, last_hard_state, is_hardstate, last_notification, next_notification, notifications_enabled, problem_has_been_acknowledged, acknowledgement_type, passive_checks_enabled, active_checks_enabled, event_handler_enabled, flap_detection_enabled, is_flapping, latency, execution_time, scheduled_downtime_depth, process_performance_data, obsess_over_service, normal_check_interval, retry_check_interval, check_timeperiod, node_name)
    VALUES%s
    ON DUPLICATE KEY UPDATE status_update_time=VALUES(status_update_time), output=VALUES(output), long_output=VALUES(long_output), perfdata=VALUES(perfdata), current_state=VALUES(current_state), current_check_attempt=VALUES(current_check_attempt), max_check_attempts=VALUES(max_check_attempts), last_check=VALUES(last_check), next_check=VALUES(next_check), is_passive_check=VALUES(is_passive_check), last_state_change=VALUES(last_state_change), last_hard_state_change=VALUES(last_hard_state_change), last_hard_state=VALUES(last_hard_state), is_hardstate=VALUES(is_hardstate), last_notification=VALUES(last_notification), next_notification=VALUES(next_notification), notifications_enabled=VALUES(notifications_enabled), problem_has_been_acknowledged=VALUES(problem_has_been_acknowledged), acknowledgement_type=VALUES(acknowledgement_type), passive_checks_enabled=VALUES(passive_checks_enabled), active_checks_enabled=VALUES(active_checks_enabled), event_handler_enabled=VALUES(event_handler_enabled), flap_detection_enabled=VALUES(flap_detection_enabled), is_flapping=VALUES(is_flapping), latency=VALUES(latency), execution_time=VALUES(execution_time), scheduled_downtime_depth=VALUES(scheduled_downtime_depth), process_performance_data=VALUES(process_performance_data), obsess_over_service=VALUES(obsess_over_service), normal_check_interval=VALUES(normal_check_interval), retry_check_interval=VALUES(retry_check_interval), check_timeperiod=VALUES(check_timeperiod), node_name=VALUES(node_name)";

    /**
     * @var string
     */
    protected $baseValue = '(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var MySQL
     */
    protected $MySQL;

    /**
     * @var BulkInsertObjectStore
     */
    protected $BulkInsertObjectStore;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * MysqlServicestatus constructor.
     * @param MySQL $MySQL
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     * @param string $nodeName
     */
    public function __construct(MySQL $MySQL, BulkInsertObjectStore $BulkInsertObjectStore, $nodeName){
        $this->MySQL = $MySQL;
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
        $query = $this->MySQL->prepare($baseQuery);

        $i = 1;
        foreach ($this->BulkInsertObjectStore->getObjects() as $key => $Servicestatus) {
            $query->bindValue($i++, $Servicestatus->getHostname());
            $query->bindValue($i++, $Servicestatus->getServicedescription());
            $query->bindValue($i++, $this->datetime($Servicestatus->getStatusUpdateTime()));
            $query->bindValue($i++, trim($Servicestatus->getPluginOutput()));
            $query->bindValue($i++, trim($Servicestatus->getLongPluginOutput()));
            $query->bindValue($i++, trim($Servicestatus->getPerfdata()));
            $query->bindValue($i++, $Servicestatus->getCurrentState());
            $query->bindValue($i++, $Servicestatus->getCurrentAttempt());
            $query->bindValue($i++, $Servicestatus->getMaxAttempts());
            $query->bindValue($i++, $this->datetime($Servicestatus->getLastCheck()));
            $query->bindValue($i++, $this->datetime($Servicestatus->getNextCheck()));
            $query->bindValue($i++, (int)$Servicestatus->getIsActiveCheckResult());
            $query->bindValue($i++, $this->datetime($Servicestatus->getLastStateChange()));
            $query->bindValue($i++, $this->datetime($Servicestatus->getLastHardStateChange()));
            $query->bindValue($i++, $Servicestatus->getLastHardState());
            $query->bindValue($i++, (int)$Servicestatus->isHardState());
            $query->bindValue($i++, $this->datetime($Servicestatus->getLastNotification()));
            $query->bindValue($i++, $this->datetime($Servicestatus->getNextNotification()));
            $query->bindValue($i++, (int)$Servicestatus->isNotificationsEnabled());
            $query->bindValue($i++, (int)$Servicestatus->isProblemHasBeenAcknowledged());
            $query->bindValue($i++, $Servicestatus->getAcknowledgementType());
            $query->bindValue($i++, (int)$Servicestatus->getAcceptPassiveChecks());
            $query->bindValue($i++, (int)$Servicestatus->getChecksEnabled());
            $query->bindValue($i++, (int)$Servicestatus->getEventHandlerEnabled());
            $query->bindValue($i++, (int)$Servicestatus->getFlapDetectionEnabled());
            $query->bindValue($i++, (int)$Servicestatus->getIsFlapping());
            $query->bindValue($i++, $Servicestatus->getLatency());
            $query->bindValue($i++, $Servicestatus->getExecutionTime());
            $query->bindValue($i++, $Servicestatus->getScheduledDowntimeDepth());
            $query->bindValue($i++, (int)$Servicestatus->isProcessPerformanceData());
            $query->bindValue($i++, (int)$Servicestatus->isObsess());
            $query->bindValue($i++, $Servicestatus->getCheckInterval());
            $query->bindValue($i++, $Servicestatus->getRetryInterval());
            $query->bindValue($i++, $Servicestatus->getCheckPeriod());
            $query->bindValue($i++, $this->nodeName);

        }

        try {
            return $this->MySQL->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->insert(true);
            }
        }
    }

}
