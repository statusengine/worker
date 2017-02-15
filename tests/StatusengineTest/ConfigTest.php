<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016  Daniel Ziegler
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

namespace Statusengine\Test\Config;

use Statusengine\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf() {
        $Config = new Config(__DIR__ . DS . 'resources' . DS . 'config.yml');
        $this->assertInstanceOf('Statusengine\Config', $Config);
    }

    public function testExceptionOnMissingFile() {
        $this->setExpectedException('Statusengine\Exception\FileNotFoundException');
        $Config = new Config('/tmp/this/file/does/not/exists/config.yml');
    }

    public function testIsRedisEnabled() {
        $Config = $this->getConfig();
        $this->assertTrue($Config->isRedisEnabled());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertTrue($emptyConfig->isRedisEnabled());
    }

    public function testIsMysqlEnabled() {
        $Config = $this->getConfig();
        $this->assertTrue($Config->isMysqlEnabled());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertFalse($emptyConfig->isMysqlEnabled());
    }

    public function testIsCrateEnabled() {
        $Config = $this->getConfig();
        $this->assertTrue($Config->isCrateEnabled());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertFalse($emptyConfig->isCrateEnabled());
    }

    public function testGetMysqlConfig() {
        $Config = $this->getConfig();
        $assert = [
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'root',
            'password' => 'password',
            'database' => 'statusengine_data'
        ];
        $this->assertEquals($assert, $Config->getMysqlConfig());

        $emptyConfig = $this->getEmptyConfig();
        $assert = [
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'statusengine',
            'password' => 'password',
            'database' => 'statusengine_data'
        ];
        $this->assertEquals($assert, $emptyConfig->getMysqlConfig());
    }

    public function testGetCrateConfig() {
        $Config = $this->getConfig();
        $assert = [
            '172.0.0.1:4200',
            '192.168.56.101:4200',
            '192.168.56.102:4200',
        ];
        $this->assertEquals($assert, $Config->getCrateConfig());

        $emptyConfig = $this->getEmptyConfig();
        $assert = ['127.0.0.1:4200'];
        $this->assertEquals($assert, $emptyConfig->getCrateConfig());
    }

    public function testGetGearmanConfig() {
        $Config = $this->getConfig();
        $assert = [
            'address' => '127.0.0.1',
            'port' => 4730,
            'timeout' => 1000
        ];
        $this->assertEquals($assert, $Config->getGearmanConfig());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals($assert, $emptyConfig->getGearmanConfig());
    }

    public function testGetRedisConfig() {
        $Config = $this->getConfig();
        $assert = [
            'address' => '127.0.0.1',
            'port' => 6379
        ];
        $this->assertEquals($assert, $Config->getRedisConfig());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals($assert, $emptyConfig->getRedisConfig());
    }

    public function testGetNumberOfServicestatusWorkers() {
        $Config = $this->getConfig();

        $this->assertEquals(3, $Config->getNumberOfServicestatusWorkers());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals(1, $emptyConfig->getNumberOfServicestatusWorkers());

        $configFails = $this->getConfigFails();
        $this->assertEquals(1, $configFails->getNumberOfServicestatusWorkers());
    }

    public function testGetNumberOfHoststatusWorkers() {
        $Config = $this->getConfig();

        $this->assertEquals(4, $Config->getNumberOfHoststatusWorkers());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals(1, $emptyConfig->getNumberOfHoststatusWorkers());

        $configFails = $this->getConfigFails();
        $this->assertEquals(1, $configFails->getNumberOfHoststatusWorkers());
    }

    public function testGetNumberOfLogentryWorkers() {
        $Config = $this->getConfig();

        $this->assertEquals(5, $Config->getNumberOfLogentryWorkers());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals(1, $emptyConfig->getNumberOfLogentryWorkers());

        $configFails = $this->getConfigFails();
        $this->assertEquals(1, $configFails->getNumberOfLogentryWorkers());
    }

    public function testGetNumberOfHostcheckWorkers() {
        $Config = $this->getConfig();

        $this->assertEquals(7, $Config->getNumberOfHostcheckWorkers());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals(1, $emptyConfig->getNumberOfHostcheckWorkers());

        $configFails = $this->getConfigFails();
        $this->assertEquals(1, $configFails->getNumberOfHostcheckWorkers());
    }

    public function testGetNumberOfServicecheckWorkers() {
        $Config = $this->getConfig();

        $this->assertEquals(8, $Config->getNumberOfServicecheckWorkers());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals(1, $emptyConfig->getNumberOfServicecheckWorkers());

        $configFails = $this->getConfigFails();
        $this->assertEquals(1, $configFails->getNumberOfServicecheckWorkers());
    }

    public function testGetNumberOfStatechangeWorkers() {
        $Config = $this->getConfig();

        $this->assertEquals(6, $Config->getNumberOfStatechangeWorkers());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals(1, $emptyConfig->getNumberOfStatechangeWorkers());

        $configFails = $this->getConfigFails();
        $this->assertEquals(1, $configFails->getNumberOfStatechangeWorkers());
    }

    public function testGetNumberOfPerfdataWorkers() {
        $Config = $this->getConfig();

        $this->assertEquals(2, $Config->getNumberOfPerfdataWorkers());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertEquals(1, $emptyConfig->getNumberOfPerfdataWorkers());

        $configFails = $this->getConfigFails();
        $this->assertEquals(1, $configFails->getNumberOfPerfdataWorkers());
    }

    public function testIsProcessPerfdataEnabled() {
        $Config = $this->getConfig();

        $this->assertTrue($Config->isProcessPerfdataEnabled());

        $emptyConfig = $this->getEmptyConfig();
        $this->assertFalse($emptyConfig->isProcessPerfdataEnabled());
    }

    public function testGetBulkConfig() {
        $Config = $this->getConfig();
        $EmptyConfig = $this->getEmptyConfig();

        $assert = [
            'number_of_bulk_records' => 1000,
            'max_bulk_delay' => 15
        ];

        $this->assertEquals($assert, $Config->getBulkSettings());

        $this->assertEquals($assert, $EmptyConfig->getBulkSettings());
    }


    public function testIsCratePerfdataBackend() {
        $Config = $this->getConfig();
        $this->assertTrue($Config->isCratePerfdataBackend());

        $EmptyConfig = $this->getEmptyConfig();
        $this->assertFalse($EmptyConfig->isCratePerfdataBackend());
    }

    public function testIsOnePerfdataBackendEnabled() {
        $Config = $this->getConfig();
        $this->assertTrue($Config->isOnePerfdataBackendEnabled());

        $EmptyConfig = $this->getEmptyConfig();
        $this->assertFalse($EmptyConfig->isOnePerfdataBackendEnabled());
    }

    public function testStoreLiveDateInArchive() {
        $Config = $this->getConfig();
        $this->assertTrue($Config->storeLiveDateInArchive());

        $EmptyConfig = $this->getEmptyConfig();
        $this->assertFalse($EmptyConfig->storeLiveDateInArchive());
    }

    public function testGetNodeName() {
        $Config = $this->getConfig();
        $this->assertEquals('Crowbar', $Config->getNodeName());

        $EmptyConfig = $this->getEmptyConfig();
        $this->assertEquals('node_name NOT SET', $EmptyConfig->getNodeName());
    }

    public function testCheckForCommands() {
        $Config = $this->getConfig();
        $this->assertTrue($Config->checkForCommands());

        $EmptyConfig = $this->getEmptyConfig();
        $this->assertFalse($EmptyConfig->checkForCommands());
    }

    public function testGetCommandCheckInterval() {
        $Config = $this->getConfig();
        $this->assertEquals(15, $Config->getCommandCheckInterval());

        $EmptyConfig = $this->getEmptyConfig();
        $this->assertEquals(10, $EmptyConfig->getCommandCheckInterval());

        $configFails = $this->getConfigFails();
        $this->assertEquals(10, $configFails->getCommandCheckInterval());
    }

    public function testGetQueryHanlder() {
        $Config = $this->getConfig();
        $this->assertEquals('/opt/naemon/var/naemon.qh', $Config->getQueryHandler());

        $EmptyConfig = $this->getEmptyConfig();
        $this->assertEquals('/opt/naemon/var/naemon.qh', $EmptyConfig->getQueryHandler());
    }

    /**
     *
     * @return Config
     */
    private function getConfig() {
        return new Config(__DIR__ . DS . 'resources' . DS . 'config.yml');
    }

    /**
     *
     * @return Config
     */
    private function getConfigFails() {
        return new Config(__DIR__ . DS . 'resources' . DS . 'config_fails.yml');
    }

    /**
     *
     * @return Config
     */
    private function getEmptyConfig() {
        return new Config(__DIR__ . DS . 'resources' . DS . 'empty_config.yml');
    }

}
