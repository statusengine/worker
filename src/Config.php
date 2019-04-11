<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2019  Daniel Ziegler
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

use Statusengine\Config\Env;
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
     */
    public function __construct($path = null) {
        //default path
        $this->path = __DIR__ . DS . '..' . DS . 'etc' . DS . 'config.yml';

        if ($path !== null) {
            $this->path = $path;
        }

        $this->parse();
    }

    /**
     * @return void
     */
    public function parse() {

        if (!file_exists($this->path)) {
            printf('Config file %s not found or not readable%s', $this->path, PHP_EOL);
            printf('Fallback to environment variables or default values%s.', PHP_EOL);
            $this->config = [];
        } else {
            $yaml = new Parser();
            $config = $yaml->parse(file_get_contents($this->path));

            $this->config = $config;
        }
    }

    /**
     * @return string
     */
    public function getNodeName() {
        $default = 'node_name NOT SET';
        $default = Env::get('SE_NODE_NAME', $default);
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
        $default = Env::get('SE_USE_REDIS', $default, Env::VALUE_BOOL);
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
        $default = Env::get('SE_USE_CRATE', $default, Env::VALUE_BOOL);
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
        $default = Env::get('SE_USE_MYSQL', $default);
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
        $default = Env::get('SE_PROCESS_PERFDATA', $default, Env::VALUE_BOOL);
        if (isset($this->config['process_perfdata'])) {
            return (bool)$this->config['process_perfdata'];
        }
        return $default;
    }

    /**
     * @return bool
     */
    public function isCratePerfdataBackend() {
        if (isset($this->config['perfdata_backend']) && is_array($this->config['perfdata_backend'])) {
            return in_array('crate', $this->config['perfdata_backend'], true);
        }

        $config = Env::get('SE_PERFDATA_BACKEND', [], Env::VALUE_ARRAY);
        return in_array('crate', $config, true);
    }

    /**
     * @return bool
     */
    public function isGraphitePerfdataBackend() {
        if (isset($this->config['perfdata_backend']) && is_array($this->config['perfdata_backend'])) {
            return in_array('graphite', $this->config['perfdata_backend'], true);
        }

        $config = Env::get('SE_PERFDATA_BACKEND', [], Env::VALUE_ARRAY);
        return in_array('graphite', $config, true);
    }

    /**
     * @return bool
     */
    public function isMysqlPerfdataBackend() {
        if (isset($this->config['perfdata_backend']) && is_array($this->config['perfdata_backend'])) {
            return in_array('mysql', $this->config['perfdata_backend'], true);
        }

        $config = Env::get('SE_PERFDATA_BACKEND', [], Env::VALUE_ARRAY);
        return in_array('mysql', $config, true);
    }

    /**
     * @return bool
     */
    public function isElasticsearchPerfdataBackend() {
        if (isset($this->config['perfdata_backend']) && is_array($this->config['perfdata_backend'])) {
            return in_array('elasticsearch', $this->config['perfdata_backend'], true);
        }

        $config = Env::get('SE_PERFDATA_BACKEND', [], Env::VALUE_ARRAY);
        return in_array('elasticsearch', $config, true);
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
            'host'     => Env::get('SE_MYSQL_HOST', '127.0.0.1'),
            'port'     => Env::get('SE_MYSQL_PORT', 3306, Env::VALUE_INT),
            'username' => Env::get('SE_MYSQL_USER', 'statusengine'),
            'password' => Env::get('SE_MYSQL_PASSWORD', 'password'),
            'database' => Env::get('SE_MYSQL_DATABASE', 'statusengine_data')
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
        $default = Env::get('SE_CRATE_NODES', ['127.0.0.1:4200'], Env::VALUE_ARRAY);

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
            'address' => Env::get('SE_GEARMAN_ADDRESS', '127.0.0.1'),
            'port'    => Env::get('SE_GEARMAN_PORT', 4730, Env::VALUE_INT),
            'timeout' => Env::get('SE_GEARMAN_TIMEOUT', 1000, Env::VALUE_INT)
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
            'address' => Env::get('SE_REDIS_ADDRESS', '127.0.0.1'),
            'port'    => Env::get('SE_REDIS_PORT', 6379, Env::VALUE_INT),
            'db'      => Env::get('SE_REDIS_DB', 0, Env::VALUE_INT)
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
        $default = Env::get('SE_NUMBER_SERVICESTATUS_WORKER', 1, Env::VALUE_INT);
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
        $default = Env::get('SE_NUMBER_HOSTSTATUS_WORKER', 1, Env::VALUE_INT);
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
        $default = Env::get('SE_NUMBER_LOGENTRY_WORKER', 1, Env::VALUE_INT);
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
        $default = Env::get('SE_NUMBER_STATECHANGE_WORKER', 1, Env::VALUE_INT);
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
        $default = Env::get('SE_NUMBER_HOSTCHECK_WORKER', 1, Env::VALUE_INT);
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
        $default = Env::get('SE_NUMBER_HOSTCHECK_WORKER', 1, Env::VALUE_INT);
        if (isset($this->config['SE_NUMBER_SERVICECHECK_WORKER'])) {
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
        $default = Env::get('SE_NUMBER_PERFDATA_WORKER', 1, Env::VALUE_INT);
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
        $default = Env::get('SE_NUMBER_MISC_WORKER', 1, Env::VALUE_INT);
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
            'number_of_bulk_records' => Env::get('SE_NUMBER_OF_BULK_RECORDS', 1000, Env::VALUE_INT),
            'max_bulk_delay'         => Env::get('SE_MAX_BULK_DELAY', 15, Env::VALUE_INT)
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
        $default = Env::get('SE_STORE_LIVE_DATA_IN_ARCHIVE_BACKEND', $default, Env::VALUE_BOOL);
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
        $default = Env::get('SE_CHECK_FOR_COMMANDS', $default, Env::VALUE_BOOL);
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
        $default = Env::get('SE_COMMAND_CHECK_INTERVAL', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_QUERY_HANDLER', $default);
        if (isset($this->config['query_hander'])) {
            return $this->config['query_hander'];
        }
        if (isset($this->config['query_handler'])) {
            return $this->config['query_handler'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getExternalCommandFile() {
        $default = '/opt/naemon/var/naemon.cmd';
        $default = Env::get('SE_EXTERNAL_COMMAND_FILE', $default);
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
        $default = Env::get('SE_SUBMIT_METHOD', $default);
        if (isset($this->config['submit_method'])) {
            return $this->config['submit_method'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getGraphiteAddress() {
        $default = 'localhost';
        $default = Env::get('SE_GRAPHITE_ADDRESS', $default);
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
        $default = Env::get('SE_GRAPHITE_PORT', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_GRAPHITE_ILLEGAL_CHARACTERS', $default);
        if (isset($this->config['graphite_illegal_characters'])) {
            return (string)$this->config['graphite_illegal_characters'];
        }
        return $default;
    }


    /**
     * @return string
     */
    public function getGraphitePrefix() {
        $default = 'statusengine';
        $default = Env::get('SE_GRAPHITE_PREFIX', $default);
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
        $default = Env::get('SE_ELASTICSEARCH_INDEX', $default);
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
        $default = Env::get('SE_ELASTICSEARCH_PATTERN', $default);
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
            'name'               => Env::get('SE_ELASTICSEARCH_TEMPLATE_NAME', 'statusengine-metric'),
            'number_of_shards'   => Env::get('SE_ELASTICSEARCH_TEMPLATE_NUMBER_OF_SHARDS', 1, Env::VALUE_INT),
            'number_of_replicas' => Env::get('SE_ELASTICSEARCH_TEMPLATE_NUMBER_OF_REPLICAS', 0, Env::VALUE_INT),
            'refresh_interval'   => Env::get('SE_ELASTICSEARCH_TEMPLATE_REFRESH_INTERVAL', '15s'),
            'codec'              => Env::get('SE_ELASTICSEARCH_TEMPLATE_CODEC', 'best_compression'),
            'enable_all'         => Env::get('SE_ELASTICSEARCH_TEMPLATE_ENABLE_ALL', 0, Env::VALUE_BOOL),
            'enable_source'      => Env::get('SE_ELASTICSEARCH_TEMPLATE_ENABLE_SOURCE', 1, Env::VALUE_BOOL)
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
        $default = Env::get('SE_ELASTICSEARCH_ADDRESS', $default);
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
        $default = Env::get('SE_ELASTICSEARCH_PORT', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_SYSLOG_ENABLED', $default, Env::VALUE_BOOL);
        if (isset($this->config['syslog_enabled'])) {
            return (bool)$this->config['syslog_enabled'];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getSyslogTag() {
        $default = 'statusengine-worker';
        $default = Env::get('SE_SYSLOG_TAG', $default);
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
        $default = Env::get('SE_AGE_HOSTCHECKS', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_HOST_ACKNOWLEDGEMENTS', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_HOST_NOTIFICATIONS', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_HOST_STATEHISTORY', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_SERVICECHECKS', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_SERVICE_ACKNOWLEDGEMENTS', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_SERVICE_NOTIFICATIONS', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_SERVICE_STATEHISTORY', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_LOGENTRIES', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_TASKS', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_PERFDATA', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_HOST_DOWNTIMES', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_AGE_SERVICE_DOWNTIMES', $default, Env::VALUE_INT);
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
        $default = Env::get('SE_DISABLE_HTTP_PROXY', $default, Env::VALUE_BOOL);
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
        $default = Env::get('SE_USE_GEARMAN', $default, Env::VALUE_BOOL);
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
        $default = Env::get('SE_USE_RABBITMQ', $default, Env::VALUE_BOOL);
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
            'host'             => Env::get('SE_RABBITMQ_HOST', '127.0.0.1'),
            'port'             => Env::get('SE_RABBITMQ_PORT', 5672, Env::VALUE_INT),
            'user'             => Env::get('SE_RABBITMQ_USER', 'statusengine'),
            'password'         => Env::get('SE_RABBITMQ_PASSWORD', 'statusengine'),
            'vhost'            => Env::get('SE_RABBITMQ_VHOST', '/'),
            'exchange'         => Env::get('SE_RABBITMQ_EXCHANGE', 'statusengine'),
            'durable_exchange' => Env::get('SE_RABBITMQ_DURABLE_EXCHANGE', false, Env::VALUE_BOOL),
            'durable_queues'   => Env::get('SE_RABBITMQ_DURABLE_QUEUES', false, Env::VALUE_BOOL),
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


}
