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

namespace Statusengine\ValueObjects;


class NotificationLog implements DataStructInterface {

    const NEBTYPE_NOTIFICATION_END = 601;

    const HOST_NOTIFICATION = 0;
    const SERVICE_NOTIFICATION = 1;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var int
     */
    private $timestamp_usec;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $host_name;

    /**
     * @var string
     */
    private $service_description;

    /**
     * @var string
     */
    private $output;

    /**
     * @var string
     */
    private $long_output;

    /**
     * @var string
     */
    private $ack_author;

    /**
     * @var string
     */
    private $ack_data;

    /**
     * @var int
     */
    private $notification_type;

    /**
     * @var int
     */
    private $start_time;

    /**
     * @var int
     */
    private $end_time;

    /**
     * @var int
     */
    private $reason_type;

    /**
     * @var int
     */
    private $state;

    /**
     * @var int
     */
    private $escalated;

    /**
     * @var int
     */
    private $contacts_notified;

    /**
     * Notification constructor.
     * @param \stdClass $notificationLog
     */
    public function __construct(\stdClass $notificationLog) {
        $this->type = (int)$notificationLog->type;
        $this->timestamp = (int)$notificationLog->timestamp;
        $this->timestamp_usec = isset($notificationLog->timestamp_usec) ? (int)$notificationLog->timestamp_usec : 0;
        $this->host_name = $notificationLog->notification_data->host_name;
        $this->service_description = $notificationLog->notification_data->service_description;
        $this->output = $notificationLog->notification_data->output;

        // Notifications do not have any long_output so we do not need to store this data
        // It's the same value as output has
        $this->long_output = $notificationLog->notification_data->long_output;
        $this->ack_author = $notificationLog->notification_data->ack_author;
        $this->ack_data = $notificationLog->notification_data->ack_data;
        $this->notification_type = (int)$notificationLog->notification_data->notification_type;
        $this->start_time = (int)$notificationLog->notification_data->start_time;
        $this->end_time = (int)$notificationLog->notification_data->end_time;
        $this->reason_type = (int)$notificationLog->notification_data->reason_type;
        $this->state = (int)$notificationLog->notification_data->state;
        $this->escalated = (int)$notificationLog->notification_data->escalated;
        $this->contacts_notified = (int)$notificationLog->notification_data->contacts_notified;
    }

    /**
     * @return bool
     */
    public function isValidNotification() {
        return $this->type === self::NEBTYPE_NOTIFICATION_END && $this->contacts_notified > 0;
    }

    /**
     * @return bool
     */
    public function isHostNotification() {
        if ($this->service_description === '' || $this->service_description === null) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isServiceNotification() {
        return !$this->isHostNotification();
    }

    /**
     * @return string
     */
    public function getHostName() {
        return $this->host_name;
    }

    /**
     * @return string
     */
    public function getServiceDescription() {
        return $this->service_description;
    }

    /**
     * @return string
     */
    public function getOutput() {
        return $this->output;
    }

    /**
     * @return string
     */
    public function getLongOutput() {
        return $this->long_output;
    }

    /**
     * @return string
     */
    public function getAckAuthor() {
        return $this->ack_author;
    }

    /**
     * @return string
     */
    public function getAckData() {
        return $this->ack_data;
    }

    /**
     * @return int
     */
    public function getNotificationType() {
        return $this->notification_type;
    }


    /**
     * @return int
     */
    public function getStartTime() {
        return $this->start_time;
    }

    /**
     * @return int
     */
    public function getEndTime() {
        return $this->end_time;
    }

    /**
     * @return int
     *
     * Reason type values:
     * 0 = Normal notification
     * 1 = Acknowledgement was set
     * 2 = Flapping start
     * 3 = Flapping stop
     * 4 = Flapping disabled
     * 5 = Downtime start
     * 6 = Downtime end
     * 7 = Downtime was cancelled
     * 8 = Custom Notification
     * @link: https://github.com/NagiosEnterprises/nagioscore/blob/463087a2c89647fdb9e20d89eaddb5c19ef25bac/include/nagios.h#L313-L321
     * @link: https://github.com/naemon/naemon-core/blob/d77b41b0f4e171a7d62afa9d15b2624d3ae1405d/src/naemon/notifications.h#L66-L77
     */
    public function getReasonType() {
        return $this->reason_type;
    }

    /**
     * @return int
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @return int
     */
    public function isEscalated() {
        return $this->escalated;
    }


    /**
     * Amount of contacts notified
     * @return int
     */
    public function getContactsNotified() {
        return $this->contacts_notified;
    }

    /**
     * @return int
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getTimestampUsec() {
        return $this->timestamp_usec;
    }

    /**
     * @return array
     */
    public function serialize() {
        return [
            'host_name'           => $this->host_name,
            'service_description' => $this->service_description,
            'output'              => $this->output,
            'long_output'         => $this->long_output,
            'ack_author'          => $this->ack_author,
            'ack_data'            => $this->ack_data,
            'notification_type'   => $this->notification_type,
            'start_time'          => $this->start_time,
            'end_time'            => $this->end_time,
            'reason_type'         => $this->reason_type,
            'state'               => $this->state,
            'escalated'           => $this->escalated,
            'contacts_notified'   => $this->contacts_notified,
            'timestamp'           => $this->timestamp,
            'timestamp_usec'      => $this->timestamp_usec
        ];
    }

}
