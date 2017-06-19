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


class Acknowledgement implements DataStructInterface {

    /*
     *  Acknowledgement types
     */
    const ACKNOWLEDGEMENT_NONE = 0;
    const ACKNOWLEDGEMENT_NORMAL = 1;
    const ACKNOWLEDGEMENT_STICKY = 2;

    /**
     * @var int
     */
    private $timestamp;

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
     * @var int
     * @link https://github.com/ageric/nagios/blob/fc452505aca3aa27809f5c5212b67f31b001df61/include/common.h#L404-L406
     * @link https://github.com/naemon/naemon-core/blob/d77b41b0f4e171a7d62afa9d15b2624d3ae1405d/src/naemon/common.h#L371-L373
     */
    private $acknowledgement_type;

    /**
     * @var int
     */
    private $state;

    /**
     * @var bool
     */
    private $is_sticky;

    /**
     * @var bool
     */
    private $persistent_comment;

    /**
     * @var bool
     */
    private $notify_contacts;


    /**
     * Acknowledgement constructor.
     * @param \stdClass $acknowledgement
     */
    public function __construct(\stdClass $acknowledgement) {
        $this->timestamp = (int)$acknowledgement->timestamp;
        $this->host_name = $acknowledgement->acknowledgement->host_name;
        $this->service_description = $acknowledgement->acknowledgement->service_description;
        $this->author_name = $acknowledgement->acknowledgement->author_name;
        $this->comment_data = $acknowledgement->acknowledgement->comment_data;
        $this->acknowledgement_type = (int)$acknowledgement->acknowledgement->acknowledgement_type;
        $this->state = (int)$acknowledgement->acknowledgement->state;
        $this->is_sticky = (bool)$acknowledgement->acknowledgement->is_sticky;
        $this->persistent_comment = (bool)$acknowledgement->acknowledgement->persistent_comment;
        $this->notify_contacts = (bool)$acknowledgement->acknowledgement->notify_contacts;

    }

    /**
     * @return bool
     */
    public function isHostAcknowledgement(){
        if($this->service_description === '' || $this->service_description === null){
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isServiceAcknowledgement(){
        return !$this->isHostAcknowledgement();
    }

    /**
     * @return int
     */
    public function getTimestamp() {
        return $this->timestamp;
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
     * @return int
     */
    public function getAcknowledgementType() {
        return $this->acknowledgement_type;
    }

    /**
     * @return int
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @return bool
     */
    public function isSticky() {
        return $this->is_sticky;
    }

    /**
     * @return bool
     */
    public function isPersistentComment() {
        return $this->persistent_comment;
    }

    /**
     * @return bool
     */
    public function isNotifyContacts() {
        return $this->notify_contacts;
    }

    /**
     * @return array
     */
    public function serialize() {
        return [
            'timestamp' => $this->timestamp,
            'host_name' => $this->host_name,
            'service_description' => $this->service_description,
            'author_name' => $this->author_name,
            'comment_data' => $this->comment_data,
            'state' => $this->state,
            'is_sticky' => $this->is_sticky,
            'persistent_comment' => $this->persistent_comment,
            'notify_contacts' => $this->notify_contacts
        ];
    }

}
