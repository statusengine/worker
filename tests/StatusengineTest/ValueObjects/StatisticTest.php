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


use Statusengine\ValueObjects\Statistic;


class StatisticTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf(){
        $Statistic = new Statistic('name', 'description', 'value');
        $this->assertInstanceOf('\Statusengine\ValueObjects\Statistic', $Statistic);
    }

    public function testGetValue(){
        $Statistic = new Statistic(
            'total_processed_hoststatus_records',
            'How many host status records where processed by Statusengine',
            13333333337
        );

        $this->assertEquals('total_processed_hoststatus_records', $Statistic->getName());
        $this->assertEquals('How many host status records where processed by Statusengine', $Statistic->getDescription());
        $this->assertEquals(13333333337, $Statistic->getValue());
    }

}
