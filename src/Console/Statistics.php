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

namespace Statusengine\Console;

use Output\NaemonPerfdata;
use Statusengine\Redis\Redis;
use Statusengine\Config;
use Statusengine\StatisticsMatcher;
use Statusengine\Syslog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class Statistics
 * @package Statusengine\Console
 * @link http://symfony.com/doc/current/console/input.html
 */
class Statistics extends Command {
    /**
     * @var Config
     */
    private $Config;

    protected function configure(){

        $this
            // the name of the command (the part after "bin/console")
            ->setName('statistics')
            // the short description shown while running "php bin/console list"
            ->setDescription('Print statistics about Statusengine workers')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command print useful statistics of Statusengine performance ");

        $this->addOption('naemon', null, InputOption::VALUE_NONE, 'Print a Naemon/Nagios compatible output');

        $this->addOption('watch', null, InputOption::VALUE_OPTIONAL, 'Refresh every X seconds', 0);

    }

    public function execute(InputInterface $input, OutputInterface $output){
        $this->Config = new Config();
        $Syslog = new Syslog($this->Config);
        $Redis = new Redis($this->Config, $Syslog);
        $Redis->connect();
        $stats = $Redis->getHash('statusengine_statistics');

        $printNaemonOutput = $input->getOption('naemon');
        $watch = (int)$input->getOption('watch');

        if ($printNaemonOutput === true) {
            $NaemonPerfdata = new NaemonPerfdata($stats);
            $output->writeln(sprintf('Statusengine statistics%s', $NaemonPerfdata->getPerfdataOutput()));
            return true;
        }

        if ($watch) {
            while (true) {
                $this->clear($output);
                $stats = $Redis->getHash('statusengine_statistics');
                $this->printHumanOutput($stats, $output);
                sleep($watch);
                $this->clear($output);
            }
        }

        $this->printHumanOutput($stats, $output);


    }

    /**
     * @param $int
     * @return string
     */
    private function format($int){
        return number_format($int, 0);
    }

    /**
     * @param StatisticsMatcher $StatisticsMatcher
     * @return int
     */
    public function getTotalOverall(StatisticsMatcher $StatisticsMatcher){
        return
            $StatisticsMatcher->getProcessedHoststatusRecords()->getValue() +
            $StatisticsMatcher->getProcessedServicestatusRecords()->getValue() +
            $StatisticsMatcher->getProcessedPerfdataRecords()->getValue() +
            $StatisticsMatcher->getProcessedHostcheckRecords()->getValue() +
            $StatisticsMatcher->getProcessedServicecheckRecords()->getValue() +
            $StatisticsMatcher->getProcessedLogentryRecords()->getValue() +
            $StatisticsMatcher->getProcessedMiscRecords()->getValue();
    }

    /**
     * @param StatisticsMatcher $StatisticsMatcher
     * @return int
     */
    public function getTotalOverallLastMinute(StatisticsMatcher $StatisticsMatcher){
        return
            $StatisticsMatcher->getProcessedHoststatusRecordsLastMinute()->getValue() +
            $StatisticsMatcher->getProcessedServicestatusRecordsLastMinute()->getValue() +
            $StatisticsMatcher->getProcessedPerfdataRecordsLastMinute()->getValue() +
            $StatisticsMatcher->getProcessedHostcheckRecordsLastMinute()->getValue() +
            $StatisticsMatcher->getProcessedServicecheckRecordsLastMinute()->getValue() +
            $StatisticsMatcher->getProcessedLogentryRecordsLastMinute()->getValue() +
            $StatisticsMatcher->getProcessedMiscRecordsLastMinute()->getValue();
    }

    /**
     * @param $stats
     * @param $output
     */
    public function printHumanOutput($stats, $output){
        $StatisticsMatcher = new StatisticsMatcher($stats);


        $output->writeln([
            '<comment>Statusengine statistics @'.date('H:i:s').'</comment>',
            'Cluster node: <info>'.$this->Config->getNodeName().'</info>',
            '',
            '<info>Live data hold in Redis:</info>',
            '==================================='
        ]);
        $output->write($StatisticsMatcher->getProcessedHoststatusRecords()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedHoststatusRecords()->getValue())));
        $output->write($StatisticsMatcher->getProcessedHoststatusRecordsLastMinute()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedHoststatusRecordsLastMinute()->getValue())));

        $output->write($StatisticsMatcher->getProcessedServicestatusRecords()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedServicestatusRecords()->getValue())));
        $output->write($StatisticsMatcher->getProcessedServicestatusRecordsLastMinute()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedServicestatusRecordsLastMinute()->getValue())));


        $output->writeln([
            '',
            '<info>Processed Historical data:</info>',
            '==================================='
        ]);
        $output->write($StatisticsMatcher->getProcessedHostcheckRecords()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedHostcheckRecords()->getValue())));
        $output->write($StatisticsMatcher->getProcessedHostcheckRecordsLastMinute()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedHostcheckRecordsLastMinute()->getValue())));

        $output->write($StatisticsMatcher->getProcessedServicecheckRecords()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedServicecheckRecords()->getValue())));
        $output->write($StatisticsMatcher->getProcessedServicecheckRecordsLastMinute()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedServicecheckRecordsLastMinute()->getValue())));

        $output->write($StatisticsMatcher->getProcessedLogentryRecords()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedLogentryRecords()->getValue())));
        $output->write($StatisticsMatcher->getProcessedLogentryRecordsLastMinute()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedLogentryRecordsLastMinute()->getValue())));

        $output->write($StatisticsMatcher->getProcessedStatechangeRecords()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedStatechangeRecords()->getValue())));
        $output->write($StatisticsMatcher->getProcessedStatechangeRecordsLastMinute()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedStatechangeRecordsLastMinute()->getValue())));

        $output->write($StatisticsMatcher->getProcessedPerfdataRecords()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedPerfdataRecords()->getValue())));
        $output->write($StatisticsMatcher->getProcessedPerfdataRecordsLastMinute()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedPerfdataRecordsLastMinute()->getValue())));

        $output->writeln([
            '',
            '<info>Processed Misc data:</info>',
            '==================================='
        ]);
        $output->write($StatisticsMatcher->getProcessedMiscRecords()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedMiscRecords()->getValue())));
        $output->write($StatisticsMatcher->getProcessedMiscRecordsLastMinute()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($StatisticsMatcher->getProcessedMiscRecordsLastMinute()->getValue())));


        $output->writeln([
            '',
            '<info>Overall:</info>',
            '==================================='
        ]);
        $output->write('Total processed records');
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($this->getTotalOverall($StatisticsMatcher))));
        $output->write('Total processed records last minute');
        $output->writeln(sprintf(': <comment>%s</comment>', $this->format($this->getTotalOverallLastMinute($StatisticsMatcher))));


        $output->writeln([
            '',
            '<info>Statusengine process intormation:</info>',
            '==================================='
        ]);
        $output->write($StatisticsMatcher->getLastUpdate()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', date('H:i:s - d.m.Y', $StatisticsMatcher->getLastUpdate()->getValue())));

        $output->write($StatisticsMatcher->getNumberOfWorkers()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $StatisticsMatcher->getNumberOfWorkers()->getValue()));

        $output->write($StatisticsMatcher->getNumberOfTotalProcesses()->getDescription());
        $output->writeln(sprintf(': <comment>%s</comment>', $StatisticsMatcher->getNumberOfTotalProcesses()->getValue()));

        $output->write('Statusengine is running since: ');
        $output->writeln(sprintf(': <comment>%s</comment>', $this->uptimeAsHuman(
            $StatisticsMatcher->getProgrammRuntime()->getValue()
        )));

        $output->writeln(['', '']);
    }

    /**
     * @param $output
     */
    public function clear($output){
        $output->write(sprintf("\033\143"));
    }


    /**
     * @param int $uptime
     * @return string
     */
    public function uptimeAsHuman($uptime = 0){
        $zeroDate = new \DateTime('@0');
        $updateDate = new \DateTime(sprintf('@%s', $uptime));

        $format = '%s seconds';

        if ($uptime >= (60 * 60 * 24 * 30)) {
            $format = '%y years, %m months, %d days, %h hours, %i minutes and %s seconds';
        }elseif  ($uptime >= (60 * 60 * 24 * 30)) {
            $format = '%m months, %d days, %h hours, %i minutes and %s seconds';
        } elseif ($uptime >= (60 * 60 * 24)) {
            $format = '%d days, %h hours, %i minutes and %s seconds';
        } elseif ($uptime >= (60 * 60)) {
            $format = '%h hours, %i minutes and %s seconds';
        } elseif ($uptime >= 60) {
            $format = '%i minutes and %s seconds';
        }

        return $zeroDate->diff($updateDate)->format($format);

    }

}