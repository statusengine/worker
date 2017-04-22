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

use Statusengine\Config;
use Statusengine\Syslog;
use Statusengine\TcpSocket;
use Statusengine\ValueObjects\Gauge;

class GraphitePerfdata {

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
    private $prefix;

    /**
     * @var string
     */
    private $illegalCharacters;


    /**
     * GraphitePerfdata constructor.
     * @param Config $Config
     * @param Syslog $Syslog
     */
    public function __construct(Config $Config, Syslog $Syslog) {
        $this->Config = $Config;
        $this->Syslog = $Syslog;

        $this->address = $this->Config->getGraphiteAddress();
        $this->port = $this->Config->getGraphitePort();
        $this->prefix = $this->Config->getGraphitePrefix();
        $this->illegalCharacters = $this->Config->getGraphiteIllegalCharacters();
    }

    /**
     * @param Gauge $Gauge
     * @return bool
     */
    public function savePerfdata(Gauge $Gauge){
        $data = $this->buildKey($Gauge);
        $TcpSocket = new TcpSocket($this->address, $this->port);

        try{
            $TcpSocket->connect();
        }catch (\Exception $e){
            $this->Syslog->error('Graphite error: '.$e->getMessage());
            return false;
        }

        try{
            $TcpSocket->send($data.PHP_EOL);
            $TcpSocket->disconnect();
        }catch (\Exception $e){
            $this->Syslog->error('Graphite error: '.$e->getMessage());
            return false;
        }
        return true;

    }

    /**
     * @param Gauge $Gauge
     * @return string
     */
    private function buildKey(Gauge $Gauge){
        return sprintf(
            '%s.%s.%s.%s %s %s',
            $this->replaceIllegalCharacters($this->prefix),
            $this->replaceIllegalCharacters($Gauge->getHostName()),
            $this->replaceIllegalCharacters($Gauge->getServiceDescription()),
            $this->replaceIllegalCharacters($Gauge->getLabel()),
            $Gauge->getValue(),
            $Gauge->getTimestamp()
        );
    }

    /**
     * @param string $str
     * @return mixed
     */
    public function replaceIllegalCharacters($str){
        return preg_replace($this->illegalCharacters, '_', $str);
    }


    /**
     * @return true
     */
    public function connect(){
        return true;
    }


    /**
     * @return true
     */
    public function dispatch(){
        return true;
    }

}
