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

namespace Statusengine\Console;

use Statusengine\Backends\PerfdataBackends\PerfdataStorageBackends;
use Statusengine\BackendSelector;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Config;
use Statusengine\StorageBackend;
use Statusengine\Syslog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class Cleanup
 * @package Statusengine\Console
 */
class Cleanup extends Command {
    /**
     * @var Config
     */
    private $Config;

    /**
     * @var Syslog
     */
    private $Syslog;

    /**
     * @var StorageBackend
     */
    private $StorageBackend;

    /**
     * @var PerfdataStorageBackends
     */
    private $PerfdataStorageBackends;

    protected function configure() {

        $this
            // the name of the command (the part after "bin/console")
            ->setName('cleanup')
            // the short description shown while running "php bin/console list"
            ->setDescription('Will delete old records out of the database')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command will delete old records out of your database");
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $style = new OutputFormatterStyle('cyan');
        $output->getFormatter()->setStyle('cyan', $style);

        $startTime = time();
        $output->writeln(sprintf(
            'Startusengine Cleanup started at: <info>%s</info>',
            date('Y-m-d H:i:s', $startTime)
        ));


        $this->Config = new Config();
        $this->Syslog = new Syslog($this->Config);
        $BulkInsertObjectStore = new BulkInsertObjectStore(
            1,
            1
        );
        $BackendSelector = new BackendSelector($this->Config, $BulkInsertObjectStore, $this->Syslog);
        $this->StorageBackend = $BackendSelector->getStorageBackend();

        $this->PerfdataStorageBackends = new PerfdataStorageBackends(
            $this->Config,
            $BulkInsertObjectStore,
            $this->Syslog
        );

        //Connect to storage backend

        $this->StorageBackend->connect();
        $this->StorageBackend->setTimeout(3600);

        $output->writeln('<cyan>Delete old host records</cyan>');
        $this->cleanupHostchecks($input, $output);
        $this->cleanupHostAcknowledgements($input, $output);
        $this->cleanupHostNotifications($input, $output);
        $this->cleanupHostStatehistory($input, $output);
        $this->cleanupHostDowntimes($input, $output);

        $output->writeln('<cyan>Delete old service records</cyan>');
        $this->cleanupServicechecks($input, $output);
        $this->cleanupServiceAcknowledgements($input, $output);
        $this->cleanupServiceNotifications($input, $output);
        $this->cleanupServiceStatehistory($input, $output);
        $this->cleanupServiceDowntimes($input, $output);

        $output->writeln('<cyan>Delete old misc records</cyan>');
        $this->cleanupLogentries($input, $output);
        $this->cleanupTasks($input, $output);


        $this->cleanupPerfdata($input, $output);


        $output->writeln(sprintf('Cleanup took: <info>%s</info> seconds...', time() - $startTime));
        $output->writeln(sprintf('Startusengine Cleanup finished at: <info>%s</info>', date('Y-m-d H:i:s')));
    }

    private function cleanupHostchecks(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeHostchecks() === 0) {
            $output->writeln('<cyan>Skipping host check records</cyan>');
            return;
        }
        $output->write('Delete old <comment>host check</comment> records...');
        $this->StorageBackend->deleteHostchecksOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeHostchecks())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupHostAcknowledgements(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeHostAcknowledgements() === 0) {
            $output->writeln('<cyan>Skipping host acknowledgements records</cyan>');
            return;
        }
        $output->write('Delete old <comment>host acknowledgements</comment> records...');
        $this->StorageBackend->deleteHostAcknowledgementsOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeHostAcknowledgements())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupHostNotifications(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeHostNotifications() === 0) {
            $output->writeln('<cyan>Skipping host notification records</cyan>');
            return;
        }
        $output->write('Delete old <comment>host notification</comment> records...');
        $this->StorageBackend->deleteHostNotificationsOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeHostNotifications())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupHostStatehistory(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeHostStatehistory() === 0) {
            $output->writeln('<cyan>Skipping host state history records</cyan>');
            return;
        }
        $output->write('Delete old <comment>host state history</comment> records...');
        $this->StorageBackend->deleteHostStatehistoryOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeHostStatehistory())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupHostDowntimes(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeHostDowntimes() === 0) {
            $output->writeln('<cyan>Skipping host downtime history records</cyan>');
            return;
        }
        $output->write('Delete old <comment>host downtime history</comment> records...');
        $this->StorageBackend->deleteHostDowntimeHistoryOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeHostDowntimes())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupServicechecks(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeServicechecks() === 0) {
            $output->writeln('<cyan>Skipping service check records</cyan>');
            return;
        }
        $output->write('Delete old <comment>service check</comment> records...');
        $this->StorageBackend->deleteServicechecksOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeServicechecks())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupServiceAcknowledgements(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeServiceAcknowledgements() === 0) {
            $output->writeln('<cyan>Skipping service acknowledgements records</cyan>');
            return;
        }
        $output->write('Delete old <comment>service acknowledgements</comment> records...');
        $this->StorageBackend->deleteServiceAcknowledgementsOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeServiceAcknowledgements())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupServiceNotifications(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeServiceNotifications() === 0) {
            $output->writeln('<cyan>Skipping service notifications records</cyan>');
            return;
        }
        $output->write('Delete old <comment>service notification</comment> records...');
        $this->StorageBackend->deleteServiceNotificationsOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeServiceNotifications())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupServiceStatehistory(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeServiceStatehistory() === 0) {
            $output->writeln('<cyan>Skipping service state history records</cyan>');
            return;
        }
        $output->write('Delete old <comment>service state history</comment> records...');
        $this->StorageBackend->deleteServiceStatehistoryOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeServiceStatehistory())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupServiceDowntimes(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeServiceDowntimes() === 0) {
            $output->writeln('<cyan>Skipping service downtime history records</cyan>');
            return;
        }
        $output->write('Delete old <comment>service downtime history</comment> records...');
        $this->StorageBackend->deleteServiceDowntimeHistoryOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeServiceDowntimes())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupLogentries(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeLogentries() === 0) {
            $output->writeln('<cyan>Skipping log entry records</cyan>');
            return;
        }
        $output->write('Delete old <comment>log entry</comment> records...');
        $this->StorageBackend->deleteLogentriesOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeLogentries())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupTasks(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgeLogentries() === 0) {
            $output->writeln('<cyan>Skipping task records</cyan>');
            return;
        }
        $output->write('Delete old <comment>task</comment> records...');
        $this->StorageBackend->deleteTasksOlderThan(
            $this->getTimestampByInterval($this->Config->getAgeTasks())
        );
        $output->writeln('<info> done</info>');
    }

    private function cleanupPerfdata(InputInterface $input, OutputInterface $output) {
        if ($this->Config->getAgePerfdata() === 0 || $this->Config->isProcessPerfdataEnabled() === false) {
            $output->writeln('<cyan>Skipping perfdata records</cyan>');
            return;
        }
        foreach ($this->PerfdataStorageBackends->getBackends() as $backendName => $backend) {
            $output->write(sprintf(
                'Delete old <comment>perfdata</comment> records for backend <comment>%s</comment>',
                $backendName
            ));
            $backend->connect();
            $backend->deletePerfdataOlderThan($this->getTimestampByInterval(
                $this->Config->getAgePerfdata()
            ));
            $output->writeln('<info> done</info>');
        }
    }

    /**
     * @param $interval
     * @return int
     */
    private function getTimestampByInterval($interval) {
        if (!is_numeric($interval) || $interval < 0) {
            throw new \RuntimeException(sprintf('Value %s for archive age is not valid!', $interval));
        }
        return time() - (3600 * 24 * $interval);
    }

}
