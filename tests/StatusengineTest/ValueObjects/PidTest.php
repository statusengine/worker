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

namespace Statusengine\Test\ValueObjects;

use Statusengine\ValueObjects\Pid;


class PidTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf() {
        $Pid = new Pid(1);
        $this->assertInstanceOf('\Statusengine\ValueObjects\Pid', $Pid);
    }

    public function testString() {
        $this->setExpectedException('Statusengine\Exception\NotNumericValueException');
        $Pid = new Pid('foobar');
    }

    public function testSaveAndGetPid() {
        $Pid = new Pid(1149);
        $this->assertEquals(1149, $Pid->getPid());
    }

}
