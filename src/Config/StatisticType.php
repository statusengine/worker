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

namespace Statusengine\Config;

/**
 * Description of StatisticType
 *
 * @author nook
 */
class StatisticType {

    /**
     *
     * @var int
     */
    private $type;

    /**
     * @return int
     */
    public function isStatusengineStatistic() {
        $this->type = 1 << 0;
        return $this->type;
    }

    /**
     * @return int
     */
    public function isHoststatusStatistic() {
        $this->type = 1 << 1;
        return $this->type;
    }

    /**
     * @return int
     */
    public function isServicestatusStatistic() {
        $this->type = 1 << 2;
        return $this->type;
    }

    /**
     * @return int
     */
    public function isLogentryStatistic() {
        $this->type = 1 << 3;
        return $this->type;
    }

    /**
     * @return int
     */
    public function isStatechangeStatistic() {
        $this->type = 1 << 4;
        return $this->type;
    }

    /**
     * @return int
     */
    public function isHostcheckStatistic() {
        $this->type = 1 << 5;
        return $this->type;
    }

    /**
     * @return int
     */
    public function isServicecheckStatistic() {
        $this->type = 1 << 6;
        return $this->type;
    }

    /**
     * @return int
     */
    public function isPerfdataStatistic() {
        $this->type = 1 << 7;
        return $this->type;
    }

    /**
     * @return int
     */
    public function getType() {
        return $this->type;
    }

}
