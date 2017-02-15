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

namespace Statusengine;


use Statusengine\ValueObjects\Statistic;

class StatisticsMatcher {

    /**
     * @var array
     */
    private $statistics;

    /**
     * @var array
     */
    private $keys = [
        'total_processed_hoststatus_records' => 'Total processed host status records',
        'total_processed_hoststatus_records_last_minute' => 'Total processed host status records last minute',
        'total_processed_servicestatus_records' => 'Total processed service status records',
        'total_processed_servicestatus_records_last_minute' => 'Total processed service status records last minute',
        'total_processed_logentry_records' => 'Total processed log entry records',
        'total_processed_logentry_records_last_minute' => 'Total processed log entry records last minute',
        'total_processed_statechange_records' => 'Total processed state change records',
        'total_processed_statechange_records_last_minute' => 'Total processed state change records last minute',
        'total_processed_hostcheck_records' => 'Total processed host check records',
        'total_processed_hostcheck_records_last_minute' => 'Total processed host check records last minute',
        'total_processed_servicecheck_records' => 'Total processed service check records',
        'total_processed_servicecheck_records_last_minute' => 'Total processed service check records last minute',
        'total_processed_perfdata_records' => 'Total processed performance data records',
        'total_processed_perfdata_records_last_minute' => 'Total processed performance data records last minute',
        'number_of_workers' => 'Number of workers',
        'total_number_of_processes' => 'Total number of running Statusengine processes',
        'last_update' => 'Last update of this statistics',
        'programm_runtime' => 'Seconds since Statusengine is running'
    ];

    /**
     * Statistics constructor.
     * @param array $statistics
     */
    public function __construct($statistics) {
        $this->statistics = $statistics;
    }

    /**
     * @return Statistic
     */
    public function getProcessedHoststatusRecords() {
        $value = 0;
        if (isset($this->statistics['total_processed_hoststatus_records'])) {
            $value = $this->statistics['total_processed_hoststatus_records'];
        }
        return new Statistic(
            'total_processed_hoststatus_records',
            $this->keys['total_processed_hoststatus_records'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedHoststatusRecordsLastMinute() {
        $value = 0;
        if (isset($this->statistics['total_processed_hoststatus_records_last_minute'])) {
            $value = $this->statistics['total_processed_hoststatus_records_last_minute'];
        }
        return new Statistic(
            'total_processed_hoststatus_records_last_minute',
            $this->keys['total_processed_hoststatus_records_last_minute'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedServicestatusRecords() {
        $value = 0;
        if (isset($this->statistics['total_processed_servicestatus_records'])) {
            $value = $this->statistics['total_processed_servicestatus_records'];
        }
        return new Statistic(
            'total_processed_servicestatus_records',
            $this->keys['total_processed_servicestatus_records'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedServicestatusRecordsLastMinute() {
        $value = 0;
        if (isset($this->statistics['total_processed_servicestatus_records_last_minute'])) {
            $value = $this->statistics['total_processed_servicestatus_records_last_minute'];
        }
        return new Statistic(
            'total_processed_servicestatus_records_last_minute',
            $this->keys['total_processed_servicestatus_records_last_minute'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedLogentryRecords() {
        $value = 0;
        if (isset($this->statistics['total_processed_logentry_records'])) {
            $value = $this->statistics['total_processed_logentry_records'];
        }
        return new Statistic(
            'total_processed_logentry_records',
            $this->keys['total_processed_logentry_records'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedLogentryRecordsLastMinute() {
        $value = 0;
        if (isset($this->statistics['total_processed_logentry_records_last_minute'])) {
            $value = $this->statistics['total_processed_logentry_records_last_minute'];
        }
        return new Statistic(
            'total_processed_logentry_records_last_minute',
            $this->keys['total_processed_logentry_records_last_minute'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedStatechangeRecords() {
        $value = 0;
        if (isset($this->statistics['total_processed_statechange_records'])) {
            $value = $this->statistics['total_processed_statechange_records'];
        }
        return new Statistic(
            'total_processed_statechange_records',
            $this->keys['total_processed_statechange_records'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedStatechangeRecordsLastMinute() {
        $value = 0;
        if (isset($this->statistics['total_processed_statechange_records_last_minute'])) {
            $value = $this->statistics['total_processed_statechange_records_last_minute'];
        }
        return new Statistic(
            'total_processed_statechange_records_last_minute',
            $this->keys['total_processed_statechange_records_last_minute'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedHostcheckRecords() {
        $value = 0;
        if (isset($this->statistics['total_processed_hostcheck_records'])) {
            $value = $this->statistics['total_processed_hostcheck_records'];
        }
        return new Statistic(
            'total_processed_hostcheck_records',
            $this->keys['total_processed_hostcheck_records'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedHostcheckRecordsLastMinute() {
        $value = 0;
        if (isset($this->statistics['total_processed_hostcheck_records_last_minute'])) {
            $value = $this->statistics['total_processed_hostcheck_records_last_minute'];
        }
        return new Statistic(
            'total_processed_hostcheck_records_last_minute',
            $this->keys['total_processed_hostcheck_records_last_minute'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedServicecheckRecords() {
        $value = 0;
        if (isset($this->statistics['total_processed_servicecheck_records'])) {
            $value = $this->statistics['total_processed_servicecheck_records'];
        }
        return new Statistic(
            'total_processed_servicecheck_records',
            $this->keys['total_processed_servicecheck_records'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedServicecheckRecordsLastMinute() {
        $value = 0;
        if (isset($this->statistics['total_processed_servicecheck_records_last_minute'])) {
            $value = $this->statistics['total_processed_servicecheck_records_last_minute'];
        }
        return new Statistic(
            'total_processed_servicecheck_records_last_minute',
            $this->keys['total_processed_servicecheck_records_last_minute'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedPerfdataRecords() {
        $value = 0;
        if (isset($this->statistics['total_processed_perfdata_records'])) {
            $value = $this->statistics['total_processed_perfdata_records'];
        }
        return new Statistic(
            'total_processed_perfdata_records',
            $this->keys['total_processed_perfdata_records'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProcessedPerfdataRecordsLastMinute() {
        $value = 0;
        if (isset($this->statistics['total_processed_perfdata_records_last_minute'])) {
            $value = $this->statistics['total_processed_perfdata_records_last_minute'];
        }
        return new Statistic(
            'total_processed_perfdata_records_last_minute',
            $this->keys['total_processed_perfdata_records_last_minute'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getNumberOfWorkers() {
        $value = 0;
        if (isset($this->statistics['number_of_workers'])) {
            $value = $this->statistics['number_of_workers'];
        }
        return new Statistic(
            'number_of_workers',
            $this->keys['number_of_workers'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getNumberOfTotalProcesses() {
        $value = 0;
        if (isset($this->statistics['total_number_of_processes'])) {
            $value = $this->statistics['total_number_of_processes'];
        }
        return new Statistic(
            'total_number_of_processes',
            $this->keys['total_number_of_processes'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getLastUpdate() {
        $value = 0;
        if (isset($this->statistics['last_update'])) {
            $value = $this->statistics['last_update'];
        }
        return new Statistic(
            'last_update',
            $this->keys['last_update'],
            $value
        );
    }

    /**
     * @return Statistic
     */
    public function getProgrammRuntime() {
        $value = 0;
        if (isset($this->statistics['programm_runtime'])) {
            $value = $this->statistics['programm_runtime'];
        }
        return new Statistic(
            'programm_runtime',
            $this->keys['programm_runtime'],
            $value
        );
    }

}
