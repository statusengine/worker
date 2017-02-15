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

namespace Statusengine\Test;

use Statusengine\BackendSelector;


class BackendSelectorTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf() {
        $BackendSelector = new BackendSelector($this->getMockConfig(), $this->getMockBulkInsertObjectStore());
        $this->assertInstanceOf('Statusengine\BackendSelector', $BackendSelector);
    }

    public function testIsCrateBackend() {
        $BackendSelector = new BackendSelector($this->getMockConfig('isCrateEnabled'), $this->getMockBulkInsertObjectStore());
        $this->assertInstanceOf('Statusengine\Crate\Crate', $BackendSelector->getStorageBackend());
    }

    public function testIsMysqlBackend() {
        $BackendSelector = new BackendSelector($this->getMockConfig('isMysqlEnabled'), $this->getMockBulkInsertObjectStore());
        $this->assertInstanceOf('Statusengine\Mysql\MySQL', $BackendSelector->getStorageBackend());
    }

    public function testExceptionForUnknownBackends() {
        $BackendSelector = new BackendSelector($this->getMockConfig(), $this->getMockBulkInsertObjectStore());
        $this->setExpectedException('Statusengine\Exception\NoBackendFoundException');
        $StorageBackend = $BackendSelector->getStorageBackend();
    }

    /**
     * @param null $methodName
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockConfig($methodName = null) {
        $config = $this->getMockBuilder('Statusengine\Config')
            ->disableOriginalConstructor()
            ->getMock();
        if ($methodName != null) {
            $config->expects($this->any())->method($methodName)->will($this->returnValue(true));
        }
        return $config;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockBulkInsertObjectStore(){
        $BulkInsertObjectStore = $this->getMockBuilder('Statusengine\BulkInsertObjectStore')
            ->disableOriginalConstructor()
            ->getMock();
        return $BulkInsertObjectStore;
    }

}
