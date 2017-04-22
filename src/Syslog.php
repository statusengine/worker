<?php
/**
 * Statusengine UI
 * Copyright (C) 2017  Daniel Ziegler
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


class Syslog {

    /**
     * @var Config
     */
    private $Config;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var bool
     */
    private $isLogOpened = false;

    public function __construct(Config $Config) {
        $this->Config = $Config;
        $this->tag = $this->Config->getSyslogTag();
        $this->enabled = $this->Config->isSyslogEnabled();
    }

    /**
     * informational message
     * @param string $message
     */
    public function info($message){
        $this->log(LOG_INFO, $message);
    }

    /**
     * normal, but significant, condition
     * @param string $message
     */
    public function notice($message){
        $this->log(LOG_NOTICE, $message);
    }

    /**
     * error conditions
     * @param string $message
     */
    public function error($message){
        $this->log(LOG_ERR, $message);
    }

    /**
     * warning conditions
     * @param string $message
     */
    public function warning($message){
        $this->log(LOG_WARNING, $message);
    }

    /**
     * critical conditions
     * @param string $message
     */
    public function critical($message){
        $this->log(LOG_CRIT, $message);
    }

    /**
     * action must be taken immediately
     * @param string $message
     */
    public function alert($message){
        $this->log(LOG_ALERT, $message);
    }

    /**
     * system is unusable
     * @param string $message
     */
    public function emergency($message){
        $this->log(LOG_EMERG, $message);
    }

    /**
     * debug-level message
     * @param string $message
     */
    public function debug($message){
        $this->log(LOG_DEBUG, $message);
    }

    /**
     * @param $priority
     * @param $message
     * @return bool
     */
    private function log($priority, $message){
        if($this->enabled === false){
            return false;
        }

        if(!$this->isLogOpened){
            $this->open();
        }
        return syslog($priority, $message);
    }

    /**
     * @return bool
     */
    private function open(){
        return openlog($this->tag, LOG_PID | LOG_PERROR | LOG_CONS, LOG_USER);
    }

    /**
     * @return bool
     */
    private function close(){
        return closelog();
    }

}
