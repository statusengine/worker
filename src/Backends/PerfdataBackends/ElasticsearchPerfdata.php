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

namespace Statusengine\Backends\PerfdataBackends;

use Elasticsearch\ClientBuilder;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Config;
use Statusengine\Elasticsearch\Template;
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
     * ElasticsearchPerfdata constructor.
     * @param Config $Config
     * @param Syslog $Syslog
     * @throws \Statusengine\Exception\InvalidArgumentException
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
                '_index' => $this->getIndex()
            ]
        ]);

        $this->BulkInsertObjectStore->addObject([
            '@timestamp'          => ($Gauge->getTimestamp() * 1000),
            'value'               => $Gauge->getValue(),
            'hostname'            => $Gauge->getHostName(),
            'service_description' => $Gauge->getServiceDescription(),
            'metric'              => $Gauge->getLabel()
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
                $Template = new Template($this->Config, $this->index);
                $Client->indices()->putTemplate($Template->getTemplate());
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
