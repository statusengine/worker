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
    }

    /**
     * @param Gauge $Gauge
     * @return bool
     */
    public function savePerfdata(Gauge $Gauge) {
        try {
            $Client = ClientBuilder::create()->setHosts($this->getHosts())->build();

            $data = [
                'index' => $this->index,
                'type' => 'metric',
                'body' => [
                    '@timestamp' => ($Gauge->getTimestamp() * 1000),
                    'value' => $Gauge->getValue(),
                    'hostname' => $Gauge->getHostName(),
                    'service_description' => $Gauge->getServiceDescription(),
                    'metric' => $Gauge->getLabel()
                ]
            ];

            $response = $Client->index($data);
        } catch (\Exception $e) {
            $this->Syslog->error('Elasticsearch error!');
            $this->Syslog->error($e->getMessage());
        }

        return true;
    }

    /**
     * @return array
     */
    private function getHosts() {
        return [sprintf('%s:%s', $this->address, $this->port)];
    }

    /**
     * @return true
     */
    public function connect() {
        return true;
    }


    /**
     * @return true
     */
    public function dispatch() {
        return true;
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    public function deletePerfdataOlderThan($timestamp) {
        return true;
    }

}
