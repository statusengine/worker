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
 *
 *********
 *
 * Time complexity information is taken from https://redis.io/documentation
 */

namespace Statusengine\Redis;

use Statusengine\Config;
use Statusengine\Syslog;


class Redis {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var \Redis
     */
    private $Redis;

    /**
     * Redis constructor.
     * @param Config $Config
     */
    public function __construct(Config $Config, Syslog $Syslog) {
        $this->Config = $Config;
        $this->Syslog = $Syslog;
    }

    public function connect() {
        $this->Redis = new \Redis();

        $config = $this->Config->getRedisConfig();
        $this->Redis->connect($config['address'], $config['port']);
        $this->Redis->select($config['db']);
    }

    /**
     * @param string $key
     * @param array $data
     * @param int $expire
     * Time complexity: O(N) where N is the number of fields being set.
     * + Time complexity: O(1) (For expire)
     */
    public function save($key, $data, $expire = 0) {
        try {
            $this->Redis->hMset($key, $data);
            if ($expire > 0) {
                $this->Redis->expire($key, $expire);
            }
        } catch (\RedisException $e) {
            $this->Syslog->error($e->getMessage());
        }
    }

    /**
     *
     * @param string $key
     * @return mixed
     * Time complexity: O(N) where N is the size of the hash.
     */
    public function getHash($key) {
        return $this->Redis->hGetAll($key);
    }


    /**
     * @param string $setKey
     * @param string $value
     * @return int
     * Time complexity: O(1) for each element added
     */
    public function addRecordToSet($setKey, $value) {
        try {
            return $this->Redis->sAdd($setKey, $value);
        } catch (\RedisException $e) {
            $this->Syslog->error($e->getMessage());
        }
    }

    /**
     * @param string $setKey
     * @param string $record
     * @return int
     * Time complexity: O(N) where N is the number of members to be removed.
     */
    public function removeRecordFromSet($setKey, $record) {
        try {
            return $this->Redis->sRem($setKey, $record);
        } catch (\RedisException $e) {
            $this->Syslog->error($e->getMessage());
        }
    }

    /**
     * @param string $setKey
     * @return array
     * Time complexity: O(N) where N is the set cardinality.
     */
    public function getAllRecordsFromSet($setKey) {
        try {
            return $this->Redis->sMembers($setKey);
        } catch (\RedisException $e) {
            $this->Syslog->error($e->getMessage());
        }
    }


}