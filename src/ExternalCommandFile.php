<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016-2018  Daniel Ziegler
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


class ExternalCommandFile {


    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var string
     */
    private $externalCommandFile;

    /**
     * @var resource
     */
    private $file;

    /**
     * ExternalCommandFile constructor.
     * @param Config $Config
     * @param Syslog $Syslog
     */
    public function __construct(Config $Config, Syslog $Syslog) {
        $this->Config = $Config;
        $this->Syslog = $Syslog;

        $this->externalCommandFile = $Config->getExternalCommandFile();
    }

    /**
     * @return bool
     */
    public function connect() {
        if(!file_exists($this->externalCommandFile)){
            $this->Syslog->error(sprintf('Execute external file "%s" does not exists', $this->externalCommandFile));
            return false;
        }

        $this->file = fopen($this->externalCommandFile, 'a+');
    }

    public function disconnect() {
        if (is_resource($this->file)) {
            fclose($this->file);
        }
    }

    /**
     * @param $commandstring
     * @return bool
     */
    public function runCommand($commandstring) {
        if(!is_resource($this->file)){
            return false;
        }
        $this->Syslog->info(sprintf('Execute external command (via External Command File): %s', $commandstring));
        $command = $commandstring;
        fwrite($this->file, $commandstring.PHP_EOL);
    }

}
