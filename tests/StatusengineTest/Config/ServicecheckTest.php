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

use Statusengine\Config\Servicecheck;

class ServicecheckTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf() {
        $config = new Servicecheck();
        $this->assertInstanceOf('Statusengine\Config\Servicecheck', $config);
    }

    public function testGetQueueName() {
        $config = new Servicecheck();
        $this->assertEquals('statusngin_servicechecks', $config->getQueueName());
    }

}
