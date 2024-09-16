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

namespace Statusengine\Mysql\SqlObjects;

use Statusengine\BulkInsertObjectStore;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Mysql;
use Statusengine\ValueObjects\NotificationLog;

class MysqlNotificationLog extends Mysql\MysqlModel {

    /**
     * @var string
     */
    protected $baseQueryHost = "
      INSERT INTO statusengine_host_notifications_log
      (hostname, start_time, start_time_usec, end_time, state, reason_type, is_escalated, contacts_notified_count, output, ack_author, ack_data )
      VALUES%s";

    /**
     * @var string
     */
    protected $baseValueHost = '(?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var string
     */
    protected $baseQueryService = "
      INSERT INTO statusengine_service_notifications_log
      (hostname, service_description, start_time, start_time_usec, end_time, state, reason_type, is_escalated, contacts_notified_count, output, ack_author, ack_data )
      VALUES%s";

    /**
     * @var string
     */
    protected $baseValueService = '(?,?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var Mysql\MySQL
     */
    protected $MySQL;

    /**
     * @var BulkInsertObjectStore
     */
    private $BulkInsertObjectStore;

    /**
     * MysqlNotificationLog constructor.
     * @param Mysql\MySQL $MySQL
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     */
    public function __construct(Mysql\MySQL $MySQL, BulkInsertObjectStore $BulkInsertObjectStore) {
        $this->MySQL = $MySQL;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
    }


    public function insert() {
        /**
         * @var NotificationLog $NotificationLog
         */

        //Cache for Bulk inserts
        $hostNotificationLogCache = [];
        $serviceNotificationLogCache = [];

        foreach ($this->BulkInsertObjectStore->getObjects() as $NotificationLog) {
            if ($NotificationLog->isHostNotification()) {
                $hostNotificationLogCache[] = $NotificationLog;
            } else {
                $serviceNotificationLogCache[] = $NotificationLog;
            }
        }

        if (!empty($hostNotificationLogCache)) {
            $this->getHostQuery($hostNotificationLogCache);
        }

        if (!empty($serviceNotificationLogCache)) {
            $this->getServiceQuery($serviceNotificationLogCache);
        }
    }

    /**
     * @param array $hostNotificationLogCache
     * @param bool $isRecursion
     * @return bool
     */
    public function getHostQuery($hostNotificationLogCache, $isRecursion = false) {
        /**
         * @var NotificationLog $NotificationLog
         */

        $baseQuery = $this->buildQueryString(sizeof($hostNotificationLogCache), $this->baseValueHost, $this->baseQueryHost);
        $query = $this->MySQL->prepare($baseQuery);

        $i = 1;
        foreach ($hostNotificationLogCache as $NotificationLog) {
            $query->bindValue($i++, $NotificationLog->getHostName());
            $query->bindValue($i++, $NotificationLog->getStartTime());
            $query->bindValue($i++, $NotificationLog->getTimestampUsec());
            $query->bindValue($i++, $NotificationLog->getEndTime());
            $query->bindValue($i++, $NotificationLog->getState());
            $query->bindValue($i++, $NotificationLog->getReasonType());
            $query->bindValue($i++, $NotificationLog->isEscalated());
            $query->bindValue($i++, $NotificationLog->getContactsNotified());
            $query->bindValue($i++, $NotificationLog->getOutput());
            $query->bindValue($i++, $NotificationLog->getAckAuthor());
            $query->bindValue($i++, $NotificationLog->getAckData());

        }

        try {
            return $this->MySQL->executeQuery($query, 'MysqlNotificationLog');
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->getHostQuery($hostNotificationLogCache, true);
            }
        }
    }

    /**
     * @param array $serviceNotificationLogCache
     * @param bool $isRecursion
     * @return bool
     */
    public function getServiceQuery($serviceNotificationLogCache, $isRecursion = false) {
        /**
         * @var NotificationLog $NotificationLog
         */

        $baseQuery = $this->buildQueryString(sizeof($serviceNotificationLogCache), $this->baseValueService, $this->baseQueryService);
        $query = $this->MySQL->prepare($baseQuery);

        $i = 1;
        foreach ($serviceNotificationLogCache as $NotificationLog) {
            $query->bindValue($i++, $NotificationLog->getHostName());
            $query->bindValue($i++, $NotificationLog->getServiceDescription());
            $query->bindValue($i++, $NotificationLog->getStartTime());
            $query->bindValue($i++, $NotificationLog->getTimestampUsec());
            $query->bindValue($i++, $NotificationLog->getEndTime());
            $query->bindValue($i++, $NotificationLog->getState());
            $query->bindValue($i++, $NotificationLog->getReasonType());
            $query->bindValue($i++, $NotificationLog->isEscalated());
            $query->bindValue($i++, $NotificationLog->getContactsNotified());
            $query->bindValue($i++, $NotificationLog->getOutput());
            $query->bindValue($i++, $NotificationLog->getAckAuthor());
            $query->bindValue($i++, $NotificationLog->getAckData());
        }

        try {
            return $this->MySQL->executeQuery($query, 'MysqlNotificationLog');
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->getServiceQuery($serviceNotificationLogCache, true);
            }
        }
    }

    /**
     * @param int $numberOfObjects
     * @param string $baseValue
     * @param string $baseQuery
     * @return string
     */
    public function buildQueryString($numberOfObjects, $baseValue, $baseQuery) {
        $values = [];
        for ($i = 1; $i <= $numberOfObjects; $i++) {
            $values[] = $baseValue;
        }

        $values = implode(', ', $values);

        return sprintf($baseQuery, $values);
    }

}