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

namespace Statusengine\Console;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Schema;
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

/**
 * Class Database
 * @package Statusengine\Console
 *
 * Dump current database schema to php file
 * bin/Console.php database --dump
 *
 * Patch current database schema to schema defined in the php file
 * bin/Console.php database --update --dry-run
 */
class Database extends Command {

    const DB_VERSION = '3.7.0';

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
            ->setName('database')
            // the short description shown while running "php bin/console list"
            ->setDescription('Create and update database schema')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("CLI command to create and update the statusengine database schema");

        $this->addOption('dump', null, InputOption::VALUE_OPTIONAL, 'Will dump the current SQL schema fromm the database to a PHP file.', false);
        $this->addOption('update', null, InputOption::VALUE_OPTIONAL, 'Will the database schema.', false);
        $this->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, 'Only print all queries but dont execute. (--update only)', false);
        $this->addOption('drop', null, InputOption::VALUE_OPTIONAL, 'Also execute DROP TABLE statements Disabled by default!. (--update only)', false);
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

        if ($this->Config->isMysqlEnabled()) {
            $mysqlConfig = $this->Config->getMysqlConfig();

            $connectionParams = [
                'dbname'   => $mysqlConfig['database'],
                'user'     => $mysqlConfig['username'],
                'password' => $mysqlConfig['password'],
                'host'     => $mysqlConfig['host'],
                'port'     => $mysqlConfig['port'],
                //'charset'  => 'UTF-8',
                'driver'   => 'pdo_mysql',
            ];
        }


        if ($this->Config->isCrateEnabled()) {
            $crateConfig = $this->Config->getCrateConfig();
            $firstHost = $crateConfig[0];

            $config = explode(':', $firstHost);

            $connectionParams = [
                'user'        => null,
                'password'    => null,
                'host'        => $config[0],
                'port'        => $config[1],
                'driverClass' => 'Crate\DBAL\Driver\PDOCrate\Driver',
            ];
        }

        $connection = DriverManager::getConnection($connectionParams);
        $platform = $connection->getDatabasePlatform();

        $SchemaManager = $connection->getSchemaManager();

        $schema = $SchemaManager->createSchema();


        //https://stackoverflow.com/a/50518342
        if ($input->getOption('dump') === null) {
            $output->writeln('Dump database schema to PHP code...');
            if ($this->Config->isMysqlEnabled()) {
                $filename = $this->dumpMySQLSchema($schema);
            }

            if ($this->Config->isCrateEnabled()) {
                $filename = $this->dumpCrateDBSchema($schema);
            }

            $output->writeln('Schema written to: ' . $filename);
        }

        if ($input->getOption('update') === null) {
            $output->writeln('Start updating your database...');
            $this->updateDatabase(
                $schema,
                $connection,
                $output,
                $input->getOption('dry-run') === null,
                $input->getOption('drop') === null
            );
        }

    }

    /**
     * @param Schema $schema
     * @return string
     */
    public function dumpMySQLSchema(Schema $schema) {
        $file = fopen($this->getFullFileName(), 'w+');

        $data = '<?php' . PHP_EOL . PHP_EOL;
        $data .= 'use Doctrine\DBAL\Schema\Schema;' . PHP_EOL;

        $data .= PHP_EOL . PHP_EOL;


        $data .= 'require_once __DIR__ . DIRECTORY_SEPARATOR . \'..\' . DIRECTORY_SEPARATOR . \'bootstrap.php\';' . PHP_EOL;
        $data .= '$schema = new Schema();' . PHP_EOL . PHP_EOL;


        foreach ($schema->getTables() as $table) {
            /** @var $table \Doctrine\DBAL\Schema\Table */

            $data .= sprintf('/****************************************%s', PHP_EOL);
            $data .= sprintf(' * Define: %s%s', $table->getName(), PHP_EOL);
            $data .= sprintf(' ***************************************/%s', PHP_EOL);

            $data .= sprintf('$table = $schema->createTable("%s");%s', $table->getName(), PHP_EOL);

            $tableOptions = $table->getOptions();

            $data .= sprintf('$table->addOption("engine" , "%s");%s', $tableOptions['engine'], PHP_EOL);
            $data .= sprintf('$table->addOption("collation" , "%s");%s', $tableOptions['collation'], PHP_EOL);
            $data .= sprintf('$table->addOption("comment" , "%s");%s', $tableOptions['comment'], PHP_EOL);


            foreach ($table->getColumns() as $column) {
                $default = null;
                if (is_numeric($column->getDefault())) {
                    $default = $column->getDefault();
                } else if ($column->getDefault() !== null) {
                    $default = $column->getDefault();
                }

                $options = [
                    'unsigned'      => $column->getUnsigned(),
                    'autoincrement' => $column->getAutoincrement(),
                    'notnull'       => $column->getNotnull(),
                    'default'       => $default
                ];

                if ($column->getLength()) {
                    $options['length'] = $column->getLength();
                }

                $data .= sprintf('$table->addColumn("%s", "%s", %s);%s',
                    $column->getName(),
                    $column->getType()->getName(),
                    var_export($options, true),
                    PHP_EOL
                );

            }

            $primaryKey = $table->getPrimaryKey();
            if ($primaryKey) {
                $columns = $this->makeArrayIfString($primaryKey->getColumns());
                $data .= sprintf('$table->setPrimaryKey(%s);%s',
                    '[' . PHP_EOL . '    "' . implode('", ' . PHP_EOL . '    "', $columns) . '"' . PHP_EOL . ']',
                    PHP_EOL
                );
            }


            foreach ($table->getIndexes() as $index) {
                /** @var $index Index */
                if ($index->isPrimary()) {
                    continue;
                }

                $columns = $this->makeArrayIfString($index->getColumns());

                if ($index->isUnique()) {
                    $data .= sprintf('$table->addUniqueIndex(%s, "%s");%s',
                        '[' . PHP_EOL . '    "' . implode('", ' . PHP_EOL . '    "', $columns) . '"' . PHP_EOL . ']',
                        $index->getName(),
                        PHP_EOL
                    );
                } else {
                    $data .= sprintf('$table->addIndex(%s, "%s");%s',
                        '[' . PHP_EOL . '    "' . implode('", ' . PHP_EOL . '    "', $columns) . '"' . PHP_EOL . ']',
                        $index->getName(),
                        PHP_EOL
                    );
                }
            }

            $data .= PHP_EOL . PHP_EOL . PHP_EOL;
        }

        $data .= 'return $schema;' . PHP_EOL;

        fwrite($file, $data);
        fclose($file);

        return $this->getFullFileName();
    }

    /**
     * @param Schema $schema
     * @return string
     */
    public function dumpCrateDBSchema(Schema $schema) {
        //https://github.com/crate/crate-dbal/issues/75
        throw new \RuntimeException("CrateDB driver don't have sharts and primary key implemented");
    }

    /**
     * @param Schema $fromSchema
     * @param Connection $connection
     * @param OutputInterface $output
     * @param bool $dryrun
     * @param bool $drop
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateDatabase(Schema $fromSchema, Connection $connection, OutputInterface $output, $dryrun = false, $drop = false) {
        if (!file_exists($this->getFullFileName())) {
            throw new \RuntimeException(
                sprintf('File not found: %s', $this->getFullFileName())
            );
        }

        $toSchema = require_once $this->getFullFileName();

        $sql = $fromSchema->getMigrateToSql($toSchema, $connection->getDatabasePlatform());

        if (empty($sql)) {
            $output->writeln('<info>Database schema is up to date</info>');
            return;
        }

        foreach ($sql as $query) {
            if($drop === false){
                if(preg_match('/drop table/', strtolower($query))){
                    //Skip drop table queries
                    $output->write('<question>Skipping:</question> ');
                    $output->writeln($query);
                    continue;
                }
            }

            $output->writeln('<comment>' . $query . '</comment>');
            if ($dryrun === false) {
                try {
                    $stmt = $connection->query($query);
                } catch (\Exception $e) {
                    $output->writeln('<error>' . $e->getMessage() . '</error>');
                }
                unset($stmt);
            }
        }

        $query = 'INSERT INTO statusengine_dbversion (id, dbversion)VALUES(1, \'' . self::DB_VERSION . '\') ON DUPLICATE KEY UPDATE dbversion=\'' . self::DB_VERSION . '\'';
        $output->writeln('<comment>' . $query . '</comment>');
        if ($dryrun === false) {
            try {
                $stmt = $connection->query($query);
            } catch (\Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
        }

        if ($dryrun === false) {
            $output->writeln('<info>Database schema updated successfully</info>');
        } else {
            $output->writeln('');
            $output->writeln('<info>****************************************</info>');
            $output->writeln('<info>No modifications where done to database!!!</info>');
            $output->writeln('<info>****************************************</info>');
        }
    }

    /**
     * @return string
     */
    private function getFileName() {
        $filename = 'mysql.php';
        if ($this->Config->isCrateEnabled()) {
            $filename = 'cratedb.php';
        }
        return $filename;
    }

    /**
     * @return string
     */
    private function getFullFileName() {
        return __DIR__ . DS . '..' . DS . '..' . DS . 'lib' . DS . $this->getFileName();
    }

    /**
     * @param string|array $strOrArray
     * @return array
     */
    private function makeArrayIfString($strOrArray) {
        if (is_array($strOrArray)) {
            return $strOrArray;
        }

        return [$strOrArray];
    }

}