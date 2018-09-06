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

namespace Statusengine\Mysql\SqlObjects;

use Statusengine\BulkInsertObjectStore;
use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Mysql;
use Statusengine\ValueObjects\Notification;

class MysqlNotification extends Mysql\MysqlModel {

    /**
     * @var string
     */
    protected $baseQueryHost = "
      INSERT INTO statusengine_host_notifications
      (hostname, contact_name, command_name, command_args, state, start_time, end_time, reason_type, output, ack_author, ack_data )
      VALUES%s";

    /**
     * @var string
     */
    protected $baseValueHost = '(?,?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var string
     */
    protected $baseQueryService = "
      INSERT INTO statusengine_service_notifications
      (hostname, service_description, contact_name, command_name, command_args, state, start_time, end_time, reason_type, output, ack_author, ack_data )
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
     * MysqlNotification constructor.
     * @param Mysql\MySQL $MySQL
     * @param BulkInsertObjectStore $BulkInsertObjectStore
     */
    public function __construct(Mysql\MySQL $MySQL, BulkInsertObjectStore $BulkInsertObjectStore) {
        $this->MySQL = $MySQL;
        $this->BulkInsertObjectStore = $BulkInsertObjectStore;
    }


    public function insert() {
        /**
         * @var Notification $Notification
         */

        //Cache for Bulk inserts
        $hostNotificationCache = [];
        $serviceNotificationCache = [];

        foreach ($this->BulkInsertObjectStore->getObjects() as $Notification) {
            if ($Notification->isHostNotification()) {
                $hostNotificationCache[] = $Notification;
            } else {
                $serviceNotificationCache[] = $Notification;
            }
        }

        if (!empty($hostNotificationCache)) {
            $this->getHostQuery($hostNotificationCache);
        }

        if (!empty($serviceNotificationCache)) {
            $this->getServiceQuery($serviceNotificationCache);
        }
    }

    /**
     * @param array $hostNotificationCache
     * @param bool $isRecursion
     * @return bool
     */
    public function getHostQuery($hostNotificationCache, $isRecursion = false) {
        /**
         * @var Notification $Notification
         */

        $baseQuery = $this->buildQueryString(sizeof($hostNotificationCache), $this->baseValueHost, $this->baseQueryHost);
        $query = $this->MySQL->prepare($baseQuery);

        $i = 1;
        foreach ($hostNotificationCache as $Notification) {
            $query->bindValue($i++, $Notification->getHostName());
            $query->bindValue($i++, $Notification->getContactName());
            $query->bindValue($i++, $Notification->getCommandName());
            $query->bindValue($i++, $Notification->getCommandArgs());
            $query->bindValue($i++, $Notification->getState());
            $query->bindValue($i++, $Notification->getStartTime());
            $query->bindValue($i++, $Notification->getEndTime());
            $query->bindValue($i++, $Notification->getReasonType());
            $query->bindValue($i++, $Notification->getOutput());
            $query->bindValue($i++, $Notification->getAckAuthor());
            $query->bindValue($i++, $Notification->getAckData());
        }

        try {
            return $this->MySQL->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->getHostQuery($hostNotificationCache, true);
            }
        }
    }

    /**
     * @param array $serviceNotificationCache
     * @param bool $isRecursion
     * @return bool
     */
    public function getServiceQuery($serviceNotificationCache, $isRecursion = false) {
        /**
         * @var Notification $Notification
         */

        $baseQuery = $this->buildQueryString(sizeof($serviceNotificationCache), $this->baseValueService, $this->baseQueryService);
        $query = $this->MySQL->prepare($baseQuery);

        $i = 1;
        foreach ($serviceNotificationCache as $Notification) {
            $query->bindValue($i++, $Notification->getHostName());
            $query->bindValue($i++, $Notification->getServiceDescription());
            $query->bindValue($i++, $Notification->getContactName());
            $query->bindValue($i++, $Notification->getCommandName());
            $query->bindValue($i++, $Notification->getCommandArgs());
            $query->bindValue($i++, $Notification->getState());
            $query->bindValue($i++, $Notification->getStartTime());
            $query->bindValue($i++, $Notification->getEndTime());
            $query->bindValue($i++, $Notification->getReasonType());
            $query->bindValue($i++, $Notification->getOutput());
            $query->bindValue($i++, $Notification->getAckAuthor());
            $query->bindValue($i++, $Notification->getAckData());
        }

        try {
            return $this->MySQL->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->getHostQuery($serviceNotificationCache, true);
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