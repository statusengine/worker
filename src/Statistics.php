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

use Statusengine\Config;
use Statusengine\Config\StatisticType;
use Statusengine\Syslog;
use Statusengine\ValueObjects\Pid;

class Statistics {

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     *
     * @var Redis
     */
    private $Redis;

    /**
     * @var Pid
     */
    private $Pid;

    /**
     *
     * @var int
     */
    private $expire = 60;

    /**
     *
     * @var int
     */
    private $lastSave = 0;

    /**
     *
     * @var int
     */
    private $totalProcessedLastMinute = 0;

    /**
     *
     * @var int
     */
    private $totalProcessedRecords = 0;

    /**
     *
     * @var int
     */
    private $lastMinuteStart = 0;

    /**
     *
     * @var StatisticType
     */
    private $Type;

    /**
     * Statistics constructor.
     * @param Config $Config
     * @param Syslog $Syslog
     */
    public function __construct(Config $Config, Syslog $Syslog) {
        $this->Syslog = $Syslog;

        try {
            $this->Redis = new \Statusengine\Redis\Redis($Config, $Syslog);
            $this->Redis->connect();
        }catch (\Exception $e){
            $this->Syslog->emergency($e->getMessage());
        }
    }

    public function setPid(Pid $Pid) {
        $this->Pid = $Pid;
    }

    /**
     *
     * @param StatisticType $type
     */
    public function setStatisticType(StatisticType $type) {
        $this->Type = $type;
    }

    public function getKey() {
        $baseKey = 'worker_statistics_%s';
        return sprintf($baseKey, $this->Pid->getPid());
    }

    public function increase() {
        $this->totalProcessedRecords++;
        $this->totalProcessedLastMinute++;
    }

    public function dispatch() {
        if ((time() - $this->lastSave > 10)) {
            $this->save();
            if (time() - $this->lastMinuteStart > 60) {
                $this->totalProcessedLastMinute = 0;
                $this->lastMinuteStart = time();
            }
        }
    }

    public function save() {
        $data = [
            'total_processed_records_last_minute' => $this->totalProcessedLastMinute,
            'total_processed_records' => $this->totalProcessedRecords,
            'statistic_type' => $this->Type->getType(),
            'pid' => $this->Pid->getPid(),
            'last_update' => time()
        ];
        $this->Redis->save($this->getKey(), $data, $this->expire);
        $this->lastSave = time();
    }

}
