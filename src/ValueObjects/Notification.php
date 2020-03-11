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


class Notification implements DataStructInterface {

    const NEBTYPE_CONTACTNOTIFICATIONMETHOD_END = 605;

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
    private $ack_author;

    /**
     * @var string
     */
    private $ack_data;

    /**
     * @var string
     */
    private $contact_name;

    /**
     * @var string
     */
    private $command_name;

    /**
     * @var string
     */
    private $command_args;

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
    private $start_time;

    /**
     * @var int
     */
    private $end_time;

    /**
     * Notification constructor.
     * @param \stdClass $notification
     */
    public function __construct(\stdClass $notification) {
        $this->type = (int)$notification->type;
        $this->timestamp = (int)$notification->timestamp;
        $this->timestamp_usec = isset($notification->timestamp_usec) ? (int)$notification->timestamp_usec : 0;
        $this->host_name = $notification->contactnotificationmethod->host_name;
        $this->service_description = $notification->contactnotificationmethod->service_description;
        $this->output = $notification->contactnotificationmethod->output;
        $this->ack_author = $notification->contactnotificationmethod->ack_author;
        $this->ack_data = $notification->contactnotificationmethod->ack_data;
        $this->contact_name = $notification->contactnotificationmethod->contact_name;
        $this->command_name = $notification->contactnotificationmethod->command_name;
        $this->command_args = $notification->contactnotificationmethod->command_args;
        $this->reason_type = (int)$notification->contactnotificationmethod->reason_type;
        $this->state = (int)$notification->contactnotificationmethod->state;
        $this->start_time = (int)$notification->contactnotificationmethod->start_time;
        $this->end_time = (int)$notification->contactnotificationmethod->end_time;
    }

    /**
     * @return bool
     */
    public function isValidNotification() {
        //Only process to avoid end_time = 0
        return $this->type === self::NEBTYPE_CONTACTNOTIFICATIONMETHOD_END;
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
     * @return string
     */
    public function getContactName() {
        return $this->contact_name;
    }

    /**
     * @return string
     */
    public function getCommandName() {
        return $this->command_name;
    }

    /**
     * @return string
     */
    public function getCommandArgs() {
        return $this->command_args;
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
            'ack_author'          => $this->ack_author,
            'ack_data'            => $this->ack_data,
            'contact_name'        => $this->contact_name,
            'command_name'        => $this->command_name,
            'command_args'        => $this->command_args,
            'reason_type'         => $this->reason_type,
            'state'               => $this->state,
            'start_time'          => $this->start_time,
            'end_time'            => $this->end_time,
            'timestamp'           => $this->timestamp,
            'timestamp_usec'      => $this->timestamp_usec
        ];
    }

}
