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

use Statusengine\ValueObjects\Task;


class TaskTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf() {
        $Task = new Task(1, '', '', '', '');
        $this->assertInstanceOf('\Statusengine\ValueObjects\Task', $Task);
    }

    public function testSetAndGetValues() {
        $Task = new Task(1486250416, 'Crowbar', 'payload', 'type', 'ee925529-639e-456d-9619-a6a3922f0fc4');
        $this->assertEquals(1486250416, $Task->getEntryTime());
        $this->assertEquals('Crowbar', $Task->getNodeName());
        $this->assertEquals('payload', $Task->getPayload());
        $this->assertEquals('type', $Task->getType());
        $this->assertEquals('ee925529-639e-456d-9619-a6a3922f0fc4', $Task->getUuid());
    }


}
