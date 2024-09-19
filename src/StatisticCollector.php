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

namespace Statusengine\Redis;

use Statusengine\Config\StatisticType;
use Statusengine\StatisticsMatcher;

class StatisticCollector {
    /**
     * @var Redis
     */
    private $Redis;

    /**
     * @var $StatisticType
     */
    private $StatisticType;

    /**
     * @var array
     */
    private $pids;

    /**
     * @var int
     */
    private $updateInterval = 5;

    /**
     * @var int
     */
    private $programmStart = 0;

    /**
     * @var int
     */
    private $lastUpdate = 0;

    public function __construct(Redis $Redis, StatisticType $StatisticType) {
        $this->programmStart = time();
        $this->Redis = $Redis;
        $this->StatisticType = $StatisticType;
    }

    /**
     *
     * @param array
     */
    public function setPids($pids) {
        $this->pids = $pids;
    }

    public function dispatch() {
        if ((time() - $this->lastUpdate) > $this->updateInterval) {
            $this->fetch();
            $this->lastUpdate = time();
        }
    }

    public function fetch() {
        $totalProcessedHoststatusRecords = 0;
        $totalProcessedHoststatusRecordsLastMinute = 0;
        $totalProcessedServicestatusRecords = 0;
        $totalProcessedServicestatusRecordsLastMinute = 0;


        $totalProcessedLogentryRecords = 0;
        $totalProcessedLogentryRecordsLastMinute = 0;
        $totalProcessedStatechangeRecords = 0;
        $totalProcessedStatechangeRecordsLastMinute = 0;
        $totalProcessedHostcheckRecords = 0;
        $totalProcessedHostcheckRecordsLastMinute = 0;
        $totalProcessedServicecheckRecords = 0;
        $totalProcessedServicecheckRecordsLastMinute = 0;
        $totalProcessedPerfdataRecords = 0;
        $totalProcessedPerfdataRecordsLastMinute = 0;
        $totalProcessedMiscRecords = 0;
        $totalProcessedMiscRecordsLastMinute = 0;
        $totalProcessedNotificationLogRecords = 0;
        $totalProcessedNotificationLogRecordsLastMinute = 0;

        foreach ($this->pids as $pid) {
            $result = $this->Redis->getHash(
                sprintf('worker_statistics_%s', $pid->getPid())
            );
            if (isset($result['statistic_type'])) {
                if ($result['statistic_type'] == $this->StatisticType->isHoststatusStatistic()) {
                    if (isset($result['total_processed_records'])) {
                        $totalProcessedHoststatusRecords += (int)$result['total_processed_records'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedHoststatusRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }

                if ($result['statistic_type'] == $this->StatisticType->isServicestatusStatistic()) {
                    if (isset($result['total_processed_records'])) {
                        $totalProcessedServicestatusRecords += (int)$result['total_processed_records'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedServicestatusRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }

                if ($result['statistic_type'] == $this->StatisticType->isLogentryStatistic()) {
                    if (isset($result['total_processed_records'])) {
                        $totalProcessedLogentryRecords += (int)$result['total_processed_records'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedLogentryRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }

                if ($result['statistic_type'] == $this->StatisticType->isStatechangeStatistic()) {
                    if (isset($result['total_processed_records'])) {
                        $totalProcessedStatechangeRecords += (int)$result['total_processed_records'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedStatechangeRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }

                if ($result['statistic_type'] == $this->StatisticType->isHostcheckStatistic()) {
                    if (isset($result['total_processed_records'])) {
                        $totalProcessedHostcheckRecords += (int)$result['total_processed_records'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedHostcheckRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }

                if ($result['statistic_type'] == $this->StatisticType->isServicecheckStatistic()) {
                    if (isset($result['total_processed_records'])) {
                        $totalProcessedServicecheckRecords += (int)$result['total_processed_records'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedServicecheckRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }

                if ($result['statistic_type'] == $this->StatisticType->isPerfdataStatistic()) {
                    if (isset($result['total_processed_records'])) {
                        $totalProcessedPerfdataRecords += (int)$result['total_processed_records'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedPerfdataRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }

                if ($result['statistic_type'] == $this->StatisticType->isMiscStatistic()) {
                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedMiscRecords += (int)$result['total_processed_records_last_minute'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedMiscRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }

                if ($result['statistic_type'] == $this->StatisticType->isNotificationLogStatistic()) {
                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedNotificationLogRecords += (int)$result['total_processed_records_last_minute'];
                    }

                    if (isset($result['total_processed_records_last_minute'])) {
                        $totalProcessedNotificationLogRecordsLastMinute += (int)$result['total_processed_records_last_minute'];
                    }
                }
            }

            /**
             * @see StatisticsMatcher::$keys
             */
            $this->Redis->save('statusengine_statistics', [
                'total_processed_hoststatus_records' => $totalProcessedHoststatusRecords,
                'total_processed_hoststatus_records_last_minute' => $totalProcessedHoststatusRecordsLastMinute,

                'total_processed_servicestatus_records' => $totalProcessedServicestatusRecords,
                'total_processed_servicestatus_records_last_minute' => $totalProcessedServicestatusRecordsLastMinute,

                'total_processed_logentry_records' => $totalProcessedLogentryRecords,
                'total_processed_logentry_records_last_minute' => $totalProcessedLogentryRecordsLastMinute,

                'total_processed_statechange_records' => $totalProcessedStatechangeRecords,
                'total_processed_statechange_records_last_minute' => $totalProcessedStatechangeRecordsLastMinute,

                'total_processed_hostcheck_records' => $totalProcessedHostcheckRecords,
                'total_processed_hostcheck_records_last_minute' => $totalProcessedHostcheckRecordsLastMinute,

                'total_processed_servicecheck_records' => $totalProcessedServicecheckRecords,
                'total_processed_servicecheck_records_last_minute' => $totalProcessedServicecheckRecordsLastMinute,

                'total_processed_perfdata_records' => $totalProcessedPerfdataRecords,
                'total_processed_perfdata_records_last_minute' => $totalProcessedPerfdataRecordsLastMinute,

                'total_processed_misc_records' => $totalProcessedMiscRecords,
                'total_processed_misc_records_last_minute' => $totalProcessedMiscRecordsLastMinute,

                'total_processed_notificationlog_records' => $totalProcessedNotificationLogRecords,
                'total_processed_notificationlog_records_last_minute' => $totalProcessedNotificationLogRecordsLastMinute,

                'number_of_workers' => sizeof($this->pids),
                'total_number_of_processes' => sizeof($this->pids) + 1, //+1 is the parent itself

                'last_update' => time(),
                'programm_runtime' => time() - $this->programmStart
            ], 15);

        }
    }

}
