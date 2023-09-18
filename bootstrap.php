<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2018  Daniel Ziegler
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


define('STATUSENGINE_WORKER_VERSION', '3.6.0');
define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__ . DS . 'vendor' . DS . 'autoload.php';

/**
 * @param mixed $param
 */
function debug($param) {
    if ($param === true || $param === false || $param === null || $param === '') {
        var_dump($param);
    }
    print_r($param);
}

/**
 * Same as echo but it will add a timestamp and new line
 *
 * @param string $str
 * @return void
 */
function echot(string $str) {
    printf(
        '[%s] %s%s',
        date('d.m.Y H:i:s'),
        $str,
        PHP_EOL
    );
}

