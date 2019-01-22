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

namespace Statusengine;

use Statusengine\Exception\FileNotFoundException;

use Symfony\Component\Yaml\Parser;

class Config {

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $config;

    /**
     * Config constructor.
     * @param null $path
     * @throws FileNotFoundException
     */
    public function __construct($path = null) {
        //default path
        $this->path = __DIR__ . DS . '..' . DS . 'etc' . DS . 'config.yml';

        if ($path !== null) {
            $this->path = $path;
        }


        if (!file_exists($this->path)) {
            throw new FileNotFoundException(sprintf('Config file %s not found or not readable', $this->path));
        }

        $this->parse();
    }

    /**
     * @return void
     */
    public function parse() {
        $yaml = new Parser();
        $config = $yaml->parse(file_get_contents($this->path));

        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getNodeName() {
        $default = 'node_name NOT SET';
        if (isset($this->config['node_name'])) {
            return $this->config['node_name'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isRedisEnabled() {
        $default = true;
        if (isset($this->config['use_redis'])) {
            return (bool)$this->config['use_redis'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isCrateEnabled() {
        $default = false;
        if (isset($this->config['use_crate'])) {
            return (bool)$this->config['use_crate'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isMysqlEnabled() {
        $default = false;
        if (isset($this->config['use_mysql'])) {
            return (bool)$this->config['use_mysql'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isProcessPerfdataEnabled() {
        $default = false;
        if (isset($this->config['process_perfdata'])) {
            return (bool)$this->config['process_perfdata'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isCratePerfdataBackend() {
        if (!isset($this->config['perfdata_backend']) || !is_array($this->config['perfdata_backend'])) {
            return false;
        }

        return in_array('crate', $this->config['perfdata_backend'], true);
    }

    /**
     * @return bool
     */
    public function isGraphitePerfdataBackend() {
        if (!isset($this->config['perfdata_backend']) || !is_array($this->config['perfdata_backend'])) {
            return false;
        }

        return in_array('graphite', $this->config['perfdata_backend'], true);
    }

    /**
     * @return bool
     */
    public function isMysqlPerfdataBackend() {
        if (!isset($this->config['perfdata_backend']) || !is_array($this->config['perfdata_backend'])) {
            return false;
        }

        return in_array('mysql', $this->config['perfdata_backend'], true);
    }

    /**
     * @return bool
     */
    public function isElasticsearchPerfdataBackend() {
        if (!isset($this->config['perfdata_backend']) || !is_array($this->config['perfdata_backend'])) {
            return false;
        }

        return in_array('elasticsearch', $this->config['perfdata_backend'], true);
    }

    /**
     * @return bool
     */
    public function isOnePerfdataBackendEnabled() {
        if ($this->isCratePerfdataBackend()) {
            return true;
        }

        if ($this->isGraphitePerfdataBackend()) {
            return true;
        }

        if ($this->isMysqlPerfdataBackend()) {
            return true;
        }

        if ($this->isElasticsearchPerfdataBackend()) {
            return true;
        }

        //todo add more perfdata backends
        return false;
    }

    /**
     * @return array
     */
    public function getMysqlConfig() {
        $config = [
            'host'     => '127.0.0.1',
            'port'     => 3306,
            'username' => 'statusengine',
            'password' => 'password',
            'database' => 'statusengine_data'
        ];

        foreach ($config as $key => $value) {
            if (isset($this->config['mysql'][$key])) {
                $config[$key] = $this->config['mysql'][$key];
            }
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getCrateConfig() {
        $default = ['127.0.0.1:4200'];

        if (isset($this->config['crate']['nodes'])) {
            if (is_array($this->config['crate']['nodes']) && !empty($this->config['crate']['nodes'])) {
                return $this->config['crate']['nodes'];
            }
        }

        return $default;
    }

    /**
     * @return array
     */
    public function getGearmanConfig() {
        $config = [
            'address' => '127.0.0.1',
            'port'    => 4730,
            'timeout' => 1000
        ];

        if (isset($this->config['gearman']['address'])) {
            $config['address'] = $this->config['gearman']['address'];
        }

        if (isset($this->config['gearman']['port'])) {
            $config['port'] = $this->config['gearman']['port'];
        }

        if (isset($this->config['gearman']['timeout'])) {
            $config['timeout'] = $this->config['gearman']['timeout'];
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getRedisConfig() {
        $config = [
            'address' => '127.0.0.1',
            'port'    => 6379,
            'db'      => 0
        ];

        if (isset($this->config['redis']['address'])) {
            $config['address'] = $this->config['redis']['address'];
        }

        if (isset($this->config['redis']['port'])) {
            $config['port'] = $this->config['redis']['port'];
        }

        if (isset($this->config['redis']['db'])) {
            $config['db'] = (int)$this->config['redis']['db'];
        }

        return $config;
    }

    /**
     * @return int
     */
    public function getNumberOfServicestatusWorkers() {
        $default = 1;
        if (isset($this->config['number_servicestatus_worker'])) {
            if (is_numeric($this->config['number_servicestatus_worker'])) {
                return (int)$this->config['number_servicestatus_worker'];
            }
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getNumberOfHoststatusWorkers() {
        $default = 1;
        if (isset($this->config['number_hoststatus_worker'])) {
            if (is_numeric($this->config['number_hoststatus_worker'])) {
                return (int)$this->config['number_hoststatus_worker'];
            }
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getNumberOfLogentryWorkers() {
        $default = 1;
        if (isset($this->config['number_logentry_worker'])) {
            if (is_numeric($this->config['number_logentry_worker'])) {
                return (int)$this->config['number_logentry_worker'];
            }
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getNumberOfStatechangeWorkers() {
        $default = 1;
        if (isset($this->config['number_statechange_worker'])) {
            if (is_numeric($this->config['number_statechange_worker'])) {
                return (int)$this->config['number_statechange_worker'];
            }
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getNumberOfHostcheckWorkers() {
        $default = 1;
        if (isset($this->config['number_hostcheck_worker'])) {
            if (is_numeric($this->config['number_hostcheck_worker'])) {
                return (int)$this->config['number_hostcheck_worker'];
            }
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getNumberOfServicecheckWorkers() {
        $default = 1;
        if (isset($this->config['number_servicecheck_worker'])) {
            if (is_numeric($this->config['number_servicecheck_worker'])) {
                return (int)$this->config['number_servicecheck_worker'];
            }
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getNumberOfPerfdataWorkers() {
        $default = 1;
        if (isset($this->config['number_perfdata_worker'])) {
            if (is_numeric($this->config['number_perfdata_worker'])) {
                return (int)$this->config['number_perfdata_worker'];
            }
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getNumberOfMiscWorkers() {
        $default = 1;
        if (isset($this->config['number_misc_worker'])) {
            if (is_numeric($this->config['number_misc_worker'])) {
                return (int)$this->config['number_misc_worker'];
            }
        }
        return $default;
    }

    /**
     * @return array
     */
    public function getBulkSettings() {
        $config = [
            'number_of_bulk_records' => 1000,
            'max_bulk_delay'         => 15
        ];

        if (isset($this->config['number_of_bulk_records'])) {
            $config['number_of_bulk_records'] = $this->config['number_of_bulk_records'];
        }

        if (isset($this->config['max_bulk_delay'])) {
            $config['max_bulk_delay'] = $this->config['max_bulk_delay'];
        }

        return $config;
    }

    /**
     * @return bool
     */
    public function storeLiveDateInArchive() {
        $default = false;
        if (isset($this->config['store_live_data_in_archive_backend'])) {
            return (bool)$this->config['store_live_data_in_archive_backend'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function checkForCommands() {
        $default = false;
        if (isset($this->config['check_for_commands'])) {
            return (bool)$this->config['check_for_commands'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getCommandCheckInterval() {
        $default = 10;
        if (isset($this->config['command_check_interval'])) {
            $interval = (int)$this->config['command_check_interval'];
            if ($interval <= 0) {
                $interval = $default;
            }
            return $interval;
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getQueryHandler() {
        $default = '/opt/naemon/var/naemon.qh';
        if (isset($this->config['query_hander'])) {
            return $this->config['query_hander'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getExternalCommandFile() {
        $default = '/opt/naemon/var/naemon.cmd';
        if (isset($this->config['external_command_file'])) {
            return $this->config['external_command_file'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getSubmitMethod() {
        $default = 'cmd';
        if (isset($this->config['submit_method'])) {
            return $this->config['submit_method'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getGraphiteAddress() {
        $default = "localhost";
        if (isset($this->config['graphite_address'])) {
            return (string)$this->config['graphite_address'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getGraphitePort() {
        $default = 2003;
        if (isset($this->config['graphite_port'])) {
            return (int)$this->config['graphite_port'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getGraphiteIllegalCharacters() {
        $default = "/[^a-zA-Z^0-9\-\.]/";
        if (isset($this->config['graphite_illegal_characters'])) {
            return (string)$this->config['graphite_illegal_characters'];
        }
        return $default;
    }


    /**
     * @return string
     */
    public function getGraphitePrefix() {
        $default = "statusengine";
        if (isset($this->config['graphite_prefix'])) {
            return (string)$this->config['graphite_prefix'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getElasticsearchIndex() {
        $default = 'statusengine-metric';
        if (isset($this->config['elasticsearch_index'])) {
            return (string)$this->config['elasticsearch_index'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getElasticsearchPattern() {
        $default = 'none';
        $patterns = [
            'none',
            'daily',
            'weekly',
            'monthly'
        ];
        if (isset($this->config['elasticsearch_pattern'])) {
            if (in_array($this->config['elasticsearch_pattern'], $patterns, true)) {
                return $this->config['elasticsearch_pattern'];
            }
        }
        return $default;
    }

    /**
     * @return array
     */
    public function getElasticsearchTemplate() {
        $defaults = [
            'name'               => 'statusengine-metric',
            'number_of_shards'   => 1,
            'number_of_replicas' => 0,
            'refresh_interval'   => '15s',
            'codec'              => 'best_compression',
            'enable_all'         => 0,
            'enable_source'      => 1
        ];

        if (!isset($this->config['elasticsearch_template']) || !is_array($this->config['elasticsearch_template'])) {
            return $defaults;
        }


        $config = [];
        foreach ($defaults as $key => $defaultValue) {
            if (!isset($this->config['elasticsearch_template'][$key])) {
                $config[$key] = $defaultValue;
                continue;
            }

            //Use config value
            $config[$key] = $this->config['elasticsearch_template'][$key];

            //Replace integers
            if (in_array($key, ['number_of_shards', 'number_of_replicas'], true)) {
                $config[$key] = (int)$this->config['elasticsearch_template'][$key];
            }

            //Replace booleans
            if (in_array($key, ['enable_all', 'enable_source'], true)) {
                $config[$key] = (bool)$this->config['elasticsearch_template'][$key];
            }
        }
        return $config;
    }

    /**
     * @return string
     */
    public function getElasticsearchAddress() {
        $default = '127.0.0.1';
        if (isset($this->config['elasticsearch_address'])) {
            return (string)$this->config['elasticsearch_address'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getElasticsearchPort() {
        $default = 9200;
        if (isset($this->config['elasticsearch_port'])) {
            return (int)$this->config['elasticsearch_port'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isSyslogEnabled() {
        $default = true;
        if (isset($this->config['syslog_enabled'])) {
            return (bool)$this->config['syslog_enabled'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getSyslogTag() {
        $default = "statusengine-worker";
        if (isset($this->config['syslog_tag'])) {
            return (string)$this->config['syslog_tag'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeHostchecks() {
        $default = 5;
        if (isset($this->config['age_hostchecks'])) {
            return (int)$this->config['age_hostchecks'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeHostAcknowledgements() {
        $default = 60;
        if (isset($this->config['age_host_acknowledgements'])) {
            return (int)$this->config['age_host_acknowledgements'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeHostNotifications() {
        $default = 5;
        if (isset($this->config['age_host_notifications'])) {
            return (int)$this->config['age_host_notifications'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeHostStatehistory() {
        $default = 365;
        if (isset($this->config['age_host_statehistory'])) {
            return (int)$this->config['age_host_statehistory'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeServicechecks() {
        $default = 5;
        if (isset($this->config['age_servicechecks'])) {
            return (int)$this->config['age_servicechecks'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeServiceAcknowledgements() {
        $default = 60;
        if (isset($this->config['age_service_acknowledgements'])) {
            return (int)$this->config['age_service_acknowledgements'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeServiceNotifications() {
        $default = 60;
        if (isset($this->config['age_service_notifications'])) {
            return (int)$this->config['age_service_notifications'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeServiceStatehistory() {
        $default = 365;
        if (isset($this->config['age_service_statehistory'])) {
            return (int)$this->config['age_service_statehistory'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeLogentries() {
        $default = 5;
        if (isset($this->config['age_logentries'])) {
            return (int)$this->config['age_logentries'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeTasks() {
        $default = 5;
        if (isset($this->config['age_tasks'])) {
            return (int)$this->config['age_tasks'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgePerfdata() {
        $default = 90;
        if (isset($this->config['age_perfdata'])) {
            return (int)$this->config['age_perfdata'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeHostDowntimes() {
        $default = 60;
        if (isset($this->config['age_host_downtimes'])) {
            return (int)$this->config['age_host_downtimes'];
        }
        return $default;
    }

    /**
     * @return int
     */
    public function getAgeServiceDowntimes() {
        $default = 60;
        if (isset($this->config['age_service_downtimes'])) {
            return (int)$this->config['age_service_downtimes'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function getDisableHttpProxy() {
        $default = true;
        if (isset($this->config['disable_http_proxy'])) {
            return (bool)$this->config['disable_http_proxy'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isGearmanEnabled() {
        $default = true;
        if (isset($this->config['use_gearman'])) {
            return (bool)$this->config['use_gearman'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isRabbitMqEnabled() {
        $default = false;
        if (isset($this->config['use_rabbitmq'])) {
            return (bool)$this->config['use_rabbitmq'];
        }
        return $default;
    }

    /**
     * @return array
     */
    public function getRabbitMqConfig() {
        $config = [
            'host'             => '127.0.0.1',
            'port'             => 5672,
            'user'             => 'statusengine',
            'password'         => 'statusengine',
            'vhost'            => '/',
            'exchange'         => 'statusengine',
            'durable_exchange' => false,
            'durable_queues'   => false,
        ];

        if (isset($this->config['rabbitmq']['host'])) {
            $config['host'] = $this->config['rabbitmq']['host'];
        }

        if (isset($this->config['rabbitmq']['port'])) {
            $config['port'] = $this->config['rabbitmq']['port'];
        }

        if (isset($this->config['rabbitmq']['user'])) {
            $config['user'] = $this->config['rabbitmq']['user'];
        }

        if (isset($this->config['rabbitmq']['password'])) {
            $config['password'] = $this->config['rabbitmq']['password'];
        }

        if (isset($this->config['rabbitmq']['vhost'])) {
            $config['vhost'] = $this->config['rabbitmq']['vhost'];
        }

        if (isset($this->config['rabbitmq']['exchange'])) {
            $config['exchange'] = $this->config['rabbitmq']['exchange'];
        }

        if (isset($this->config['rabbitmq']['durable_exchange'])) {
            $config['durable_exchange'] = (bool)$this->config['rabbitmq']['durable_exchange'];
        }

        if (isset($this->config['rabbitmq']['durable_queues'])) {
            $config['durable_queues'] = (bool)$this->config['rabbitmq']['durable_queues'];
        }

        return $config;
    }

    /**
     * @return bool
     */
    public function useBinaryUuidInMySQL() {
        $default = false;
        if (isset($this->config['mysql_use_binary_uuid'])) {
            return (bool)$this->config['mysql_use_binary_uuid'];
        }

        return $default;
    }


}
