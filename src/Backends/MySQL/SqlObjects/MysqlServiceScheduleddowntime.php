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
use Statusengine\ValueObjects\Downtime;

class MysqlServiceScheduleddowntime extends Mysql\MysqlModel {

    /**
     * @var string
     */
    protected $baseQuery = "INSERT INTO statusengine_service_scheduleddowntimes
    (hostname, service_description, entry_time, author_name, comment_data, internal_downtime_id, triggered_by_id, is_fixed,
    duration, scheduled_start_time, scheduled_end_time, node_name %s)
    VALUES%s
    ON CONFLICT DO UPDATE SET entry_time=VALUES(entry_time), author_name=VALUES(author_name), comment_data=VALUES(comment_data),
    triggered_by_id=VALUES(triggered_by_id), is_fixed=VALUES(is_fixed), duration=VALUES(duration), scheduled_end_time=VALUES(scheduled_end_time) %s";


    /**
     * @var string
     */
    protected $baseValue = '?,?,?,?,?,?,?,?,?,?,?,?';

    /**
     * @var \PDO
     */
    protected $MySQL;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * MysqlServiceScheduleddowntime constructor.
     * @param Mysql\MySQL $MySQL
     * @param $nodeName
     */
    public function __construct(Mysql\MySQL $MySQL, $nodeName){
        $this->MySQL = $MySQL;
        $this->nodeName = $nodeName;
    }

    /**
     * @param Downtime $Downtime
     * @param bool $isRecursion
     * @return bool
     */
    public function saveDowntime(Downtime $Downtime, $isRecursion = false) {
        $query = $this->MySQL->prepare($this->getQuery($Downtime));
        $i = 1;
        $query->bindValue($i++, $Downtime->getHostName());
        $query->bindValue($i++, $Downtime->getServiceDescription());
        $query->bindValue($i++, $Downtime->getEntryTime());
        $query->bindValue($i++, $Downtime->getAuthorName());
        $query->bindValue($i++, $Downtime->getCommentData());
        $query->bindValue($i++, $Downtime->getDowntimeId());
        $query->bindValue($i++, $Downtime->getTriggeredBy());
        $query->bindValue($i++, (int)$Downtime->isFixed());
        $query->bindValue($i++, $Downtime->getDuration());
        $query->bindValue($i++, $Downtime->getScheduledStartTime());
        $query->bindValue($i++, $Downtime->getScheduledEndTime());
        $query->bindValue($i++, $this->nodeName);

        //Add dynamic Fields
        if ($Downtime->wasDowntimeAdded() || $Downtime->wasRestoredFromRetentionDat() || $Downtime->wasDowntimeStarted()) {
            $query->bindValue($i++, (int)$Downtime->wasStarted());
            $query->bindValue($i++, $Downtime->getActualStartTime());
        }

        try {
            return $this->MySQL->executeQuery($query);
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
    public function deleteDowntime(Downtime $Downtime, $isRecursion = false){
        $sql = "DELETE FROM statusengine_service_scheduleddowntimes 
        WHERE hostname=? AND service_description=? AND node_name=? AND scheduled_start_time=? AND internal_downtime_id=?";

        $query = $this->MySQL->prepare($sql);
        $query->bindValue(1, $Downtime->getHostName());
        $query->bindValue(2, $Downtime->getServiceDescription());
        $query->bindValue(3, $this->nodeName);
        $query->bindValue(4, $Downtime->getScheduledStartTime());
        $query->bindValue(5, $Downtime->getDowntimeId());

        try {
            return $this->MySQL->executeQuery($query);
        } catch (StorageBackendUnavailableExceptions $Exceptions) {
            //Retry
            if ($isRecursion === false) {
                $this->deleteDowntime($Downtime, true);
            }
        }
    }

    /**
     * @param Downtime $Downtime
     * @return string
     */
    public function getQuery(Downtime $Downtime) {
        if ($Downtime->wasDowntimeAdded() || $Downtime->wasRestoredFromRetentionDat() || $Downtime->wasDowntimeStarted()) {
            $dynamicFields = [
                'insert' => ['was_started', 'actual_start_time'],
                'update' => ['was_started=VALUES(was_started)', 'actual_start_time=VALUES(actual_start_time)']
            ];

            $placeholdersToAdd = [];
            foreach ($dynamicFields['insert'] as $field) {
                $placeholdersToAdd[] = '?';
            }


            return sprintf(
                $this->baseQuery,
                ', ' . implode(', ', $dynamicFields['insert']),
                '(' . $this->baseValue . ', ' . implode(',', $placeholdersToAdd) . ')',
                ', ' . implode(', ', $dynamicFields['update'])
            );
        }

        return sprintf(
            $this->baseQuery,
            '',
            '(' . $this->baseValue . ')',
            ''
        );

    }


}

