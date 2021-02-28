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

use Statusengine\Exception\StorageBackendUnavailableExceptions;
use Statusengine\Mysql;
use Statusengine\ValueObjects\Acknowledgement;

class MysqlHostAcknowledgement extends Mysql\MysqlModel {

    /**
     * @var string
     */
    protected $baseQuery = 'INSERT INTO statusengine_host_acknowledgements (hostname, state, author_name, comment_data, entry_time, entry_time_usec, acknowledgement_type, is_sticky, persistent_comment, notify_contacts)VALUES%s';

    /**
     * @var string
     */
    protected $baseValue = '(?,?,?,?,?,?,?,?,?,?)';

    /**
     * @var Mysql\MySQL
     */
    protected $MySQL;

    /**
     * @var Acknowledgement
     */
    protected $Acknowledgement;


    /**
     * MysqlHostAcknowledgement constructor.
     * @param Mysql\MySQL $MySQL
     * @param Acknowledgement $Acknowledgement
     */
    public function __construct(Mysql\MySQL $MySQL, Acknowledgement $Acknowledgement) {
        $this->MySQL = $MySQL;
        $this->Acknowledgement = $Acknowledgement;
    }

    /**
     * @param bool $isRecursion
     * @return bool
     */
    public function insert($isRecursion = false) {
        $baseQuery = $this->buildQuery();

        $query = $this->MySQL->prepare($baseQuery);
        $i = 1;

        $query->bindValue($i++, $this->Acknowledgement->getHostName());
        $query->bindValue($i++, $this->Acknowledgement->getState());
        $query->bindValue($i++, $this->Acknowledgement->getAuthorName());
        $query->bindValue($i++, $this->Acknowledgement->getCommentData());
        $query->bindValue($i++, $this->Acknowledgement->getTimestamp());
        $query->bindValue($i++, $this->Acknowledgement->getTimestampUsec());
        $query->bindValue($i++, $this->Acknowledgement->getAcknowledgementType());
        $query->bindValue($i++, (int)$this->Acknowledgement->isSticky());
        $query->bindValue($i++, (int)$this->Acknowledgement->isPersistentComment());
        $query->bindValue($i++, (int)$this->Acknowledgement->isNotifyContacts());

        try {
            return $this->MySQL->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->insert(true);
            }
        }
    }

    /**
     * @return string
     */
    public function buildQuery(){
        return sprintf($this->baseQuery, $this->baseValue);
    }

}
