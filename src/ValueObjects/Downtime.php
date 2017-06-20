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

namespace Statusengine\ValueObjects;


class Downtime implements DataStructInterface {

    const NEBTYPE_DOWNTIME_ADD = 1100;
    const NEBTYPE_DOWNTIME_DELETE = 1101;
    const NEBTYPE_DOWNTIME_LOAD = 1102;
    const NEBTYPE_DOWNTIME_START = 1103;
    const NEBTYPE_DOWNTIME_STOP = 1104;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $attr;

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
    private $author_name;

    /**
     * @var string
     */
    private $comment_data;

    /**
     * @var string
     * 1 == Service
     * 2 == Host
     */
    private $downtime_type;

    /**
     * @var int
     */
    private $entry_time;

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
    private $triggered_by;

    /**
     * @var int
     */
    private $downtime_id;

    /**
     * @var bool
     */
    private $fixed;

    /**
     * @var int
     */
    private $duration;

    /**
     * @var int
     */
    private $timestamp;


    /**
     * Downtime constructor.
     * @param \stdClass $downtime
     */
    public function __construct(\stdClass $downtime) {
        $this->type = (int)$downtime->type;
        $this->attr = (int)$downtime->attr;
        $this->host_name = $downtime->downtime->host_name;
        $this->service_description = $downtime->downtime->service_description;
        $this->author_name = $downtime->downtime->author_name;
        $this->comment_data = $downtime->downtime->comment_data;
        $this->downtime_type = (int)$downtime->downtime->downtime_type;
        $this->entry_time = (int)$downtime->downtime->entry_time;
        $this->start_time = (int)$downtime->downtime->start_time;
        $this->end_time = (int)$downtime->downtime->end_time;
        $this->triggered_by = (int)$downtime->downtime->triggered_by;
        $this->downtime_id = (int)$downtime->downtime->downtime_id;
        $this->fixed = (bool)$downtime->downtime->fixed;
        $this->duration = (int)$downtime->downtime->duration;

        $this->timestamp = (int)$downtime->timestamp;
    }

    /**
     * @return bool
     */
    public function isHostDowntime() {
        return $this->downtime_type === 2;
    }

    /**
     * @return bool
     */
    public function isServiceDowntime() {
        return !$this->isHostDowntime();
    }

    /**
     * @return bool
     */
    public function wasDowntimeAdded(){
        return $this->type === self::NEBTYPE_DOWNTIME_ADD;
    }

    /**
     * @return bool
     */
    public function wasDowntimeDeleted(){
        return $this->type === self::NEBTYPE_DOWNTIME_DELETE;
    }

    /**
     * @return bool
     */
    public function wasRestoredFromRetentionDat(){
        return $this->type === self::NEBTYPE_DOWNTIME_LOAD;
    }

    /**
     * @return bool
     */
    public function wasDowntimeStarted(){
        return $this->type === self::NEBTYPE_DOWNTIME_START;
    }

    /**
     * @return bool
     */
    public function wasDowntimeStopped(){
        return $this->type === self::NEBTYPE_DOWNTIME_STOP;
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
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAuthorName() {
        return $this->author_name;
    }

    /**
     * @return string
     */
    public function getCommentData() {
        return $this->comment_data;
    }

    /**
     * @return string
     */
    public function getDowntimeType() {
        return $this->downtime_type;
    }

    /**
     * @return int
     */
    public function getEntryTime() {
        return $this->entry_time;
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
    public function getTriggeredBy() {
        return $this->triggered_by;
    }

    /**
     * @return int
     */
    public function getDowntimeId() {
        return $this->downtime_id;
    }

    /**
     * @return bool
     */
    public function isFixed() {
        return $this->fixed;
    }

    /**
     * @return int
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @return int
     */
    public function getScheduledStartTime(){
        return $this->start_time;
    }

    /**
     * @return int
     */
    public function getScheduledEndTime(){
        return $this->end_time;
    }

    /**
     * @return int
     */
    public function getActualStartTime(){
        if ($this->wasDowntimeStarted()){
            return $this->timestamp;
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getActualEndTime(){
        if ($this->wasDowntimeStopped() || $this->wasDowntimeDeleted()){
            return $this->timestamp;
        }
        return 0;
    }

    /**
     * @return bool
     */
    public function wasCancelled(){
        return $this->attr === 2;
    }

    /**
     * @return bool
     */
    public function wasStarted(){
        return $this->wasDowntimeStarted();
    }

    /**
     * @return array
     */
    public function serialize() {
        return [
            'host_name' => $this->host_name,
            'service_description' => $this->service_description,
            'author_name' => $this->author_name,
            'comment_data' => $this->comment_data,
            'downtime_type' => $this->downtime_type,
            'entry_time' => $this->entry_time,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'triggered_by' => $this->triggered_by,
            'downtime_id' => $this->downtime_id,
            'fixed' => $this->fixed,
            'duration' => $this->duration
        ];
    }

}
