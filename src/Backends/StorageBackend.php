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

namespace Statusengine;

interface StorageBackend {

    public function connect();

    public function dispatch();

    /**
     * @param int $timeout in seconds
     */
    public function setTimeout($timeout);

    public function saveNodeName();

    public function saveLogentry(ValueObjects\Logentry $Logentry);

    public function saveStatechange(ValueObjects\Statechange $Statechange);

    public function saveHostcheck(\Statusengine\ValueObjects\Hostcheck $Hostcheck);

    public function saveServicecheck(\Statusengine\ValueObjects\Servicecheck $Servicecheck);

    public function saveHoststatus(\Statusengine\ValueObjects\Hoststatus $Hoststatus);

    public function saveServicestatus(\Statusengine\ValueObjects\Servicestatus $Servicestatus);

    public function saveNotification(\Statusengine\ValueObjects\Notification $Notification);

    public function saveAcknowledgement(\Statusengine\ValueObjects\Acknowledgement $Acknowledgement);

    public function deleteHostchecksOlderThan($timestamp);

    public function deleteHostAcknowledgementsOlderThan($timestamp);

    public function deleteHostNotificationsOlderThan($timestamp);

    public function deleteHostStatehistoryOlderThan($timestamp);

    public function deleteServicechecksOlderThan($timestamp);

    public function deleteServiceAcknowledgementsOlderThan($timestamp);

    public function deleteServiceNotificationsOlderThan($timestamp);

    public function deleteServiceStatehistoryOlderThan($timestamp);

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

}
