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


use Statusengine\ValueObjects\Gauge;


class GaugeTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf() {
        $Gauge = new Gauge('host_name', 'service_desc', 'label', 0, time());
        $this->assertInstanceOf('\Statusengine\ValueObjects\Gauge', $Gauge);
    }

    public function testConstructAndGetAll() {
        $timestamp = time();
        $Gauge = new Gauge('host_name', 'service_desc', 'RTA', 0.25, $timestamp, 'ms',
            200, //warnign
            500, //critical
            0, //min
            1000); //max
        $this->assertEquals('host_name', $Gauge->getHostName());
        $this->assertEquals('service_desc', $Gauge->getServiceDescription());
        $this->assertEquals('RTA', $Gauge->getLabel());
        $this->assertEquals(0.25, $Gauge->getValue());
        $this->assertEquals($timestamp, $Gauge->getTimestamp());
        $this->assertEquals('ms', $Gauge->getUnit());
        $this->assertEquals(200, $Gauge->getWarning());
        $this->assertEquals(500, $Gauge->getCritical());
        $this->assertEquals(0, $Gauge->getMin());
        $this->assertEquals(1000, $Gauge->getMax());
    }


    public function testConstructAndGetMinimal() {

        $timestamp = time();
        $Gauge = new Gauge('host_name', 'service_desc', 'RTA', 0.25, $timestamp);
        $this->assertEquals('host_name', $Gauge->getHostName());
        $this->assertEquals('service_desc', $Gauge->getServiceDescription());
        $this->assertEquals('RTA', $Gauge->getLabel());
        $this->assertEquals(0.25, $Gauge->getValue());
        $this->assertEquals($timestamp, $Gauge->getTimestamp());
        $this->assertEquals('', $Gauge->getUnit());
        $this->assertEquals(null, $Gauge->getWarning());
        $this->assertEquals(null, $Gauge->getCritical());
        $this->assertEquals(null, $Gauge->getMin());
        $this->assertEquals(null, $Gauge->getMax());
    }

}
