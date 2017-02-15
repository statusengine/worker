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

namespace StatusengineTest\ValueObjects;


use Statusengine\ValueObjects\Logentry;


class LogentryTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf(){
        $Logentry = new Logentry($this->getData());
        $this->assertInstanceOf('\Statusengine\ValueObjects\Logentry', $Logentry);
    }

    public function testGetValues(){
        $Logentry = new Logentry($this->getData());
        $this->assertEquals(1483763419, $Logentry->getLogentryTime());
        $this->assertEquals(1483763400, $Logentry->getEntryTime());
        $this->assertEquals(1024, $Logentry->getLogentryType());
        $this>self::assertEquals('This is a test log entry record', $Logentry->getLogentryData());
    }

    public function testSerialize(){
        $Logentry = new Logentry($this->getData());

        $this->assertEquals([
            'logentry_time' => 1483763419,
            'entry_time' => 1483763400,
            'logentry_type' => 1024,
            'logentry_data' => 'This is a test log entry record'
        ], $Logentry->serialize());
    }

    public function getData(){
        $logentry = new \stdClass();
        $logentry->timestamp = '1483763419';
        $logentry->logentry = new \stdClass();
        $logentry->logentry->entry_time = '1483763400';
        $logentry->logentry->data_type = '1024';
        $logentry->logentry->data = 'This is a test log entry record';
        return $logentry;
    }

}
