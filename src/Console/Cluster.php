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


use Statusengine\BackendSelector;
use Statusengine\BulkInsertObjectStore;
use Statusengine\Config;
use Statusengine\StorageBackend;
use Statusengine\Syslog;
use Statusengine\ValueObjects\NodeName;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Cluster extends Command {

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


    protected function configure() {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('cluster')
            // the short description shown while running "php bin/console list"
            ->setDescription('Interface to manage your Statusengine Worker nodes in the Cluster')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("With this CLI tools, you can list, add and delete a node from the Statusengine Cluster");

        $this->addOption('nodename', null, InputOption::VALUE_OPTIONAL, 'A nodename you want to manipulate');
        $this->addArgument('action', InputArgument::OPTIONAL, 'add, delete or list', 'list');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $this->Config = new Config();
        $this->Syslog = new Syslog($this->Config);
        $BulkInsertObjectStore = new BulkInsertObjectStore(
            1,
            1
        );
        $BackendSelector = new BackendSelector($this->Config, $BulkInsertObjectStore, $this->Syslog);
        $this->StorageBackend = $BackendSelector->getStorageBackend();

        switch ($input->getArgument('action')) {
            case 'add':
                $helper = $this->getHelper('question');
                $nodename = $input->getOption('nodename');

                if (mb_strlen($nodename) == 0) {
                    $question = new Question('Please enter a nodename. This name needs to be unique in the cluster!' . PHP_EOL);
                    $question->setValidator(function ($answer) {
                        if (!is_string($answer) || mb_strlen($answer) == 0) {
                            throw new \RuntimeException(
                                'Please type in a unique nodename'
                            );
                        }
                        return $answer;
                    });

                    $nodename = $helper->ask($input, $output, $question);
                }

                $this->StorageBackend->saveNodeName($nodename, 0);

                $output->writeln(sprintf(
                    '<info>Node %s was added successfully to the cluster</info>',
                    $nodename
                ));

                break;

            case 'delete':
                $helper = $this->getHelper('question');
                $nodename = $input->getOption('nodename');
                if (mb_strlen($nodename) == 0) {
                    $question = new Question('Please enter a nodename' . PHP_EOL);
                    $question->setAutocompleterValues($this->getNodeNamesForAutocompletion());
                    $question->setValidator(function ($answer) {
                        if (!is_string($answer) || mb_strlen($answer) == 0) {
                            throw new \RuntimeException(
                                'Please type in a nodename'
                            );
                        }
                        return $answer;
                    });

                    $nodename = $helper->ask($input, $output, $question);
                }

                $this->StorageBackend->deleteNodeByName($nodename);

                $output->writeln(sprintf(
                    '<info>Node %s was deleted successfully</info>',
                    $nodename
                ));
                break;
        }
        usleep(500000);
        $this->showClusterOverview($output);
    }

    /**
     * @return array
     */
    private function getNodeNamesForAutocompletion() {
        $nodenames = [];
        foreach ($this->StorageBackend->getNodes() as $node) {
            /**
             * @var NodeName $node
             */
            $nodenames[] = $node->getNodeName();
        }
        return $nodenames;
    }

    private function showClusterOverview(Output $output) {
        $nodes = [];
        foreach ($this->StorageBackend->getNodes() as $node) {
            /**
             * @var NodeName $node
             */
            $nodes[] = [
                $node->getNodeName(),
                $node->getNodeVersion(),
                $node->getNodeStartTimeHuman()
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Node Name', 'Node Version', 'Last Start Time'])
            ->setRows($nodes);
        $table->render();
    }

}