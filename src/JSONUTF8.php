<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2019  Daniel Ziegler
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

namespace Statusengine;


class JSONUTF8 {

    /**
     * @param string $json
     * @param Syslog $Syslog
     * @return bool|mixed
     */
    public static function decodeJson($json, Syslog $Syslog) {
        $payload = json_decode($json);
        $errno = json_last_error();
        if ($errno === JSON_ERROR_UTF8) {
            //Try to detect charset and retry to json_decode

            $enclist = [
                'UTF-8',
                'ISO-8859-15',
                'ISO-8859-1', 'ISO-8859-16',
                'Windows-1251', 'Windows-1252', 'Windows-1254',
                'ASCII'
            ];

            $detectEncoding = mb_detect_encoding($json, $enclist);

            $json = iconv($detectEncoding . "//TRANSLIT//IGNORE", "UTF-8", $json);
            $payload = json_decode($json);
            $errno = json_last_error();
        }
        // parsing error
        if ($errno != JSON_ERROR_NONE) {
            $Syslog->warning('Error while parsing JSON - ' . self::getJsonLastErrorMsg($errno));
            $Syslog->debug('Couldn\'t parse: ' . $json);
            return false;
            // parsed object is not an object
        } else if (!is_object($payload)) {
            $Syslog->warning('Error while parsing JSON string - Response isn\'t an object');
            $Syslog->debug('Invalid JSON: ' . $json);
            return false;
        } else {
            return $payload;
        }
    }

    /**
     * @param int $errno
     * @return string
     */
    private static function getJsonLastErrorMsg($errno) {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        $errors = [
            JSON_ERROR_NONE           => 'No error',
            JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
            JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX         => 'Syntax error',
            JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        ];

        return (isset($errors[$errno]) ? $errors[$errno] : 'Unknown error');
    }

}