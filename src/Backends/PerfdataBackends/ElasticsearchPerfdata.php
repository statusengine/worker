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

namespace Statusengine\Backends\PerfdataBackends;

use Elasticsearch\ClientBuilder;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Config;
use Statusengine\Syslog;
use Statusengine\ValueObjects\Gauge;

class ElasticsearchPerfdata {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var string
     */
    private $address;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var BulkInsertObjectStore
     */
    private $BulkInsertObjectStore;


    /**
     * GraphitePerfdata constructor.
     * @param Config $Config
     * @param Syslog $Syslog
     */
    public function __construct(Config $Config, Syslog $Syslog) {
        $this->Config = $Config;
        $this->Syslog = $Syslog;

        $this->address = $this->Config->getElasticsearchAddress();
        $this->port = $this->Config->getElasticsearchPort();
        $this->index = $this->Config->getElasticsearchIndex();
        $this->pattern = $this->Config->getElasticsearchPattern();

        $BulkConfig = $this->Config->getBulkSettings();
        $this->BulkInsertObjectStore = new BulkInsertObjectStore(
            $BulkConfig['max_bulk_delay'],
            $BulkConfig['number_of_bulk_records']
        );
    }

    /**
     * @param Gauge $Gauge
     * @return bool
     */
    public function savePerfdata(Gauge $Gauge) {
        $this->BulkInsertObjectStore->addObject([
            'index' => [
                '_index' => $this->getIndex(),
                '_type' => 'metric',
            ]
        ]);

        $this->BulkInsertObjectStore->addObject([
            '@timestamp' => ($Gauge->getTimestamp() * 1000),
            'value' => $Gauge->getValue(),
            'hostname' => $Gauge->getHostName(),
            'service_description' => $Gauge->getServiceDescription(),
            'metric' => $Gauge->getLabel()
        ]);

        return true;
    }

    /**
     * @return array
     */
    private function getHosts() {
        return [sprintf('%s:%s', $this->address, $this->port)];
    }

    /**
     * @param null|int $timestamp
     * @return string
     */
    private function getIndex($timestamp = null) {
        return sprintf('%s%s', $this->index, $this->parsePattern($timestamp));
    }

    /**
     * @param null|int $timestamp
     * @return string
     */
    private function parsePattern($timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }

        if ($this->pattern === 'none') {
            return '';
        }

        switch ($this->pattern) {
            case 'daily':
                return date('Y.m.d', $timestamp);
            case 'weekly':
                return date('o.W', $timestamp);
            case 'monthly':
                return date('Y.m', $timestamp);
            default:
                return '';
        }
    }

    /**
     * @return true
     */
    public function connect() {
        try {

            //Fix forks
            usleep(rand(100000, 250000));

            $tempalteConfig = $this->Config->getElasticsearchTemplate();
            $Client = ClientBuilder::create()->setHosts($this->getHosts())->build();

            $templateExists = $Client->indices()->existsTemplate([
                'name' => $tempalteConfig['name']
            ]);

            if ($templateExists === false) {
                $this->Syslog->info('Elasticsearch index template missing - I will create it');

                $Client->indices()->putTemplate([
                    'name' => $tempalteConfig['name'],
                    'create' => false,
                    'body' => [
                        'template' => sprintf('%s*', $this->index),
                        'settings' => [
                            'number_of_shards' => $tempalteConfig['number_of_shards'],
                            'number_of_replicas' => $tempalteConfig['number_of_replicas'],
                            'refresh_interval' => $tempalteConfig['refresh_interval'],
                            'codec' => $tempalteConfig['codec'],
                            'mapper.dynamic' => false

                        ],
                        'mappings' => [
                            '_default_' => [
                                '_all' => [
                                    'enabled' => $tempalteConfig['enable_all']
                                ],
                                '_source' => [
                                    'enabled' => $tempalteConfig['enable_source']
                                ]
                            ],
                            'metric' => [
                                'properties' => [
                                    '@timestamp' => [
                                        'type' => 'date'
                                    ],
                                    'value' => [
                                        'type' => 'double',
                                        'index' => 'no'
                                    ],
                                    'hostname' => [
                                        'type' => 'string',
                                        'index' => 'not_analyzed'
                                    ],
                                    'service_description' => [
                                        'type' => 'string',
                                        'index' => 'not_analyzed'
                                    ],
                                    'metric' => [
                                        'type' => 'string',
                                        'index' => 'not_analyzed'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);
            }
        } catch (\Exception $e) {
            $this->Syslog->error('Elasticsearch error!');
            $this->Syslog->error($e->getMessage());
        }

        return true;
    }


    /**
     * @return true
     */
    public function dispatch() {
        if ($this->BulkInsertObjectStore->hasRaisedTimeout()) {
            $bulkData = $this->BulkInsertObjectStore->getObjects();
            $this->BulkInsertObjectStore->reset();
            try {
                $Client = ClientBuilder::create()->setHosts($this->getHosts())->build();
                $response = $Client->bulk(['body' => $bulkData]);
            } catch (\Exception $e) {
                $this->Syslog->error('Elasticsearch error!');
                $this->Syslog->error($e->getMessage());
            }


        }
        return true;
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deletePerfdataOlderThan($timestamp) {
        if ($this->pattern === 'none') {
            return true;
        }

        $Client = ClientBuilder::create()->setHosts($this->getHosts())->build();
        try {
            $allIndices = $Client->indices()->get([
                'index' => sprintf('%s*', $this->index)
            ]);

            if (empty($allIndices)) {
                return true;
            }

            foreach ($allIndices as $indexName => $index) {
                //Is this index older than allowed by age_perfdata?
                if ($index['settings']['index']['creation_date'] < $timestamp) {
                    $Client->indices()->delete([
                        'index' => $indexName
                    ]);
                }

            }

        } catch (\Exception $e) {
            $this->Syslog->error($e->getMessage());
        }
        return true;
    }

}
