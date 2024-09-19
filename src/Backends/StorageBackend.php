<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2024  Daniel Ziegler
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

namespace Statusengine;

/**
 * Do not mix different object types into one instance of StorageBackend
 * The primary goal of this call is to provide a unified interface for all storage backends and to enable bulk inserts
 * For performance reasons, only the first element of the BulkInsertObjectStore gets verified and then all elements are inserted
 * based in the first one.
 * So if the first element is a Hoststatus, all other elements will be inserted as Hoststatus objects
 *
 * DO NOT MIX DIFFERENT OBJECT TYPES INTO ONE INSTANCE OF StorageBackend
 *
 */
interface StorageBackend {

    public function connect();

    public function dispatch();

    /**
     * @param int $timeout in seconds
     */
    public function setTimeout($timeout);

    /**
     * @param null|string $nodeName
     * @param null|int $startTime
     * @return mixed
     */
    public function saveNodeName($nodeName = null, $startTime = null);

    public function getNodes();

    /**
     * @param $nodeName
     * @return mixed
     */
    public function deleteNodeByName($nodeName);

    public function saveLogentry(ValueObjects\Logentry $Logentry);

    public function saveStatechange(ValueObjects\Statechange $Statechange);

    public function saveHostcheck(\Statusengine\ValueObjects\Hostcheck $Hostcheck);

    public function saveServicecheck(\Statusengine\ValueObjects\Servicecheck $Servicecheck);

    public function saveHoststatus(\Statusengine\ValueObjects\Hoststatus $Hoststatus);

    public function saveServicestatus(\Statusengine\ValueObjects\Servicestatus $Servicestatus);

    public function saveNotification(\Statusengine\ValueObjects\Notification $Notification);

    public function saveNotificationLog(\Statusengine\ValueObjects\NotificationLog $NotificationLog);

    public function saveAcknowledgement(\Statusengine\ValueObjects\Acknowledgement $Acknowledgement);

    public function deleteHostchecksOlderThan($timestamp);

    public function deleteHostAcknowledgementsOlderThan($timestamp);

    public function deleteHostNotificationsOlderThan($timestamp);

    public function deleteHostNotificationsLogOlderThan($timestamp);

    public function deleteHostStatehistoryOlderThan($timestamp);

    public function deleteHostDowntimeHistoryOlderThan($timestamp);

    public function deleteServicechecksOlderThan($timestamp);

    public function deleteServiceAcknowledgementsOlderThan($timestamp);

    public function deleteServiceNotificationsOlderThan($timestamp);

    public function deleteServiceNotificationsLogOlderThan($timestamp);

    public function deleteServiceStatehistoryOlderThan($timestamp);

    public function deleteServiceDowntimeHistoryOlderThan($timestamp);

    public function deleteLogentriesOlderThan($timestamp);

    public function deleteTasksOlderThan($timestamp);


    /**
     * @return array
     */
    public function getTasks();

    /**
     * @param array $uuids
     * @return array|bool
     */
    public function deleteTaskByUuids($uuids = []);

    public function getHostDowntimehistoryBackend();

    public function getHostScheduleddowntimeBackend();

    public function getServiceDowntimehistoryBackend();

    public function getServiceScheduleddowntimeBackend();

    public function monitoringengineWasRestarted();

}
