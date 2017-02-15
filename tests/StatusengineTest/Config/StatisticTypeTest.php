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


use Statusengine\Config\StatisticType;


class StatisticTypeTest extends \PHPUnit_Framework_TestCase {

    public function testIsInstanceOf() {
        $StatsType = new StatisticType();
        $this->assertInstanceOf('\Statusengine\Config\StatisticType', $StatsType);
    }

    public function testGetTypes() {
        $StatsType = new StatisticType();
        $this->assertEquals(1 << 0, $StatsType->isStatusengineStatistic());
        $this->assertEquals(1 << 0, $StatsType->getType());

        $this->assertEquals(1 << 1, $StatsType->isHoststatusStatistic());
        $this->assertEquals(1 << 1, $StatsType->getType());

        $this->assertEquals(1 << 2, $StatsType->isServicestatusStatistic());
        $this->assertEquals(1 << 2, $StatsType->getType());

        $this->assertEquals(1 << 3, $StatsType->isLogentryStatistic());
        $this->assertEquals(1 << 3, $StatsType->getType());

        $this->assertEquals(1 << 4, $StatsType->isStatechangeStatistic());
        $this->assertEquals(1 << 4, $StatsType->getType());

        $this->assertEquals(1 << 5, $StatsType->isHostcheckStatistic());
        $this->assertEquals(1 << 5, $StatsType->getType());

        $this->assertEquals(1 << 6, $StatsType->isServicecheckStatistic());
        $this->assertEquals(1 << 6, $StatsType->getType());

        $this->assertEquals(1 << 7, $StatsType->isPerfdataStatistic());
        $this->assertEquals(1 << 7, $StatsType->getType());
    }

}
