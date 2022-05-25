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
use Statusengine\Exception\UnknownTypeException;
use Statusengine\Mysql;
use Statusengine\ValueObjects\Downtime;

class MysqlServiceDowntimehistory extends Mysql\MysqlModel {

    /**
     * @var string
     */
    protected $baseQuery = "INSERT INTO statusengine_service_downtimehistory
    (hostname, service_description, entry_time, entry_time_usec, author_name, comment_data, internal_downtime_id, triggered_by_id, is_fixed,
    duration, scheduled_start_time, scheduled_end_time, node_name %s)
    VALUES%s
    ON DUPLICATE KEY UPDATE entry_time=VALUES(entry_time), entry_time_usec=VALUES(entry_time_usec), author_name=VALUES(author_name), comment_data=VALUES(comment_data),
    triggered_by_id=VALUES(triggered_by_id), is_fixed=VALUES(is_fixed), duration=VALUES(duration), scheduled_end_time=VALUES(scheduled_end_time) %s";


    /**
     * @var string
     */
    protected $baseValue = '?,?,?,?,?,?,?,?,?,?,?,?,?';

    /**
     * @var \PDO
     */
    protected $MySQL;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * MysqlServiceDowntimehistory constructor.
     * @param Mysql\MySQL $MySQL
     * @param string $nodeName
     */
    public function __construct(Mysql\MySQL $MySQL, $nodeName) {
        $this->MySQL = $MySQL;
        $this->nodeName = $nodeName;
    }


    /**
     * @param Downtime $Downtime
     * @param bool $isRecursion
     * @return bool
     * @throws UnknownTypeException
     */
    public function saveDowntime(Downtime $Downtime, $isRecursion = false) {
        if ($Downtime->wasDowntimeAdded() || $Downtime->wasRestoredFromRetentionDat()) {
            $query = $this->getQueryForCreatedOrLoadedDowntime($Downtime);
        }

        if ($Downtime->wasDowntimeStarted()) {
            $query = $this->getQueryForStartedDowntime($Downtime);
        }

        if ($Downtime->wasDowntimeStopped() || $Downtime->wasDowntimeDeleted()) {
            $query = $this->getQueryForStoppedOrDeletedDowntime($Downtime);
        }

        try {
            return $this->MySQL->executeQuery($query, 'MysqlServiceDowntimehistory');
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->saveDowntime($Downtime, true);
            }
        }
    }

    /**
     * @param Downtime $Downtime
     * @param bool $isRecursion
     * @return bool
     */
    public function deleteDowntime(Downtime $Downtime, $isRecursion = false) {
        $sql = "DELETE FROM statusengine_service_downtimehistory 
        WHERE hostname=? AND service_description=? AND node_name=? AND scheduled_start_time=? AND internal_downtime_id=?";

        $query = $this->MySQL->prepare($sql);
        $query->bindValue(1, $Downtime->getHostName());
        $query->bindValue(2, $Downtime->getServiceDescription());
        $query->bindValue(3, $this->nodeName);
        $query->bindValue(4, $Downtime->getScheduledStartTime());
        $query->bindValue(5, $Downtime->getDowntimeId());

        try {
            return $this->MySQL->executeQuery($query, 'MysqlServiceDowntimehistory');
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->deleteDowntime($Downtime, true);
            }
        }
    }

    /**
     * @param Downtime $Downtime
     * @return bool|\PDOStatement
     * @throws UnknownTypeException
     */
    private function getQueryForCreatedOrLoadedDowntime(Downtime $Downtime) {
        $query = $this->MySQL->prepare($this->getQuery($Downtime));
        $i = 1;
        $query->bindValue($i++, $Downtime->getHostName());
        $query->bindValue($i++, $Downtime->getServiceDescription());
        $query->bindValue($i++, $Downtime->getEntryTime());
        $query->bindValue($i++, $Downtime->getTimestampUsec());
        $query->bindValue($i++, $Downtime->getAuthorName());
        $query->bindValue($i++, $Downtime->getCommentData());
        $query->bindValue($i++, $Downtime->getDowntimeId());
        $query->bindValue($i++, $Downtime->getTriggeredBy());
        $query->bindValue($i++, (int)$Downtime->isFixed());
        $query->bindValue($i++, $Downtime->getDuration());
        $query->bindValue($i++, $Downtime->getScheduledStartTime());
        $query->bindValue($i++, $Downtime->getScheduledEndTime());
        $query->bindValue($i++, $this->nodeName);

        //Add dynamic fields
        $query->bindValue($i++, (int)$Downtime->wasStarted());
        $query->bindValue($i++, $Downtime->getActualStartTime());
        $query->bindValue($i++, $Downtime->getActualEndTime());
        $query->bindValue($i++, (int)$Downtime->wasCancelled());

        return $query;
    }

    /**
     * @param Downtime $Downtime
     * @return bool|\PDOStatement
     */
    private function getQueryForStartedDowntime(Downtime $Downtime) {
        $sql = "UPDATE statusengine_service_downtimehistory SET
                was_started=?, actual_start_time=?
                WHERE hostname=? AND service_description=? AND node_name=? AND scheduled_start_time=? AND internal_downtime_id=?";


        $query = $this->MySQL->prepare($sql);
        //SET
        $query->bindValue(1, (int)$Downtime->wasStarted());
        $query->bindValue(2, $Downtime->getActualStartTime());

        //WHERE
        $query->bindValue(3, $Downtime->getHostName());
        $query->bindValue(4, $Downtime->getServiceDescription());
        $query->bindValue(5, $this->nodeName);
        $query->bindValue(6, $Downtime->getScheduledStartTime());
        $query->bindValue(7, $Downtime->getDowntimeId());

        return $query;
    }

    /**
     * @param Downtime $Downtime
     * @return bool|\PDOStatement
     */
    private function getQueryForStoppedOrDeletedDowntime(Downtime $Downtime) {
        $sql = "UPDATE statusengine_service_downtimehistory SET
                actual_end_time=?, was_cancelled=?
                WHERE hostname=? AND service_description=? AND node_name=? AND scheduled_start_time=? AND internal_downtime_id=?";


        $query = $this->MySQL->prepare($sql);
        //SET
        $query->bindValue(1, $Downtime->getActualEndTime());
        $query->bindValue(2, (int)$Downtime->wasCancelled());

        //WHERE
        $query->bindValue(3, $Downtime->getHostName());
        $query->bindValue(4, $Downtime->getServiceDescription());
        $query->bindValue(5, $this->nodeName);
        $query->bindValue(6, $Downtime->getScheduledStartTime());
        $query->bindValue(7, $Downtime->getDowntimeId());

        return $query;
    }

    /**
     * @param Downtime $Downtime
     * @return string
     * @throws UnknownTypeException
     */
    private function getQuery(Downtime $Downtime) {
        if ($Downtime->wasDowntimeAdded() || $Downtime->wasRestoredFromRetentionDat()) {
            $dynamicFields = [
                'insert' => ['was_started', 'actual_start_time', 'actual_end_time', 'was_cancelled'],
                'update' => [
                    'was_started=VALUES(was_started)',
                    'actual_start_time=VALUES(actual_start_time)',
                    'actual_end_time=VALUES(actual_end_time)',
                    'was_cancelled=VALUES(was_cancelled)'
                ]
            ];
            return $this->buildQueryString($dynamicFields);
        }

        throw new UnknownTypeException('Downtime action/type is not supported');
    }

    /**
     * @param array $dynamicFields
     * @return string
     */
    public function buildQueryString($dynamicFields = []) {
        $placeholdersToAdd = [];
        foreach ($dynamicFields['insert'] as $field) {
            $placeholdersToAdd[] = '?';
        }
        return sprintf(
            $this->baseQuery,
            ', ' . implode(', ', $dynamicFields['insert']),
            '(' . $this->baseValue . ',' . implode(',', $placeholdersToAdd) . ')',
            ', ' . implode(', ', $dynamicFields['update'])
        );
    }


}
