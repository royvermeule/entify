<?php

namespace Entify\Bin\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Entify extends Command
{
    protected static $defaultName = 'entify';
    protected static $buildEntityParam = 'entitys';
    protected static $buildSqlParam = 'db';
    protected static $buildAllParam = 'all';
    protected static $dropParam = 'drop';
    protected static $refreshParam = 'refresh';
    protected static $defaultData = 'default';

    protected function configure()
    {
        $this->setName(self::$defaultName);
        $this->setDescription('Entify command line tool');
        
        $this->addOption(self::$buildEntityParam);
        $this->addOption(self::$buildSqlParam);
        $this->addOption(self::$buildAllParam);
        $this->addOption(self::$dropParam);
        $this->addOption(self::$refreshParam); 
        $this->addOption(self::$defaultData);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = match (true) {
            $input->getOption(self::$buildEntityParam) => $this->buildEntity(),
            $input->getOption(self::$buildSqlParam) => $this->buildSql(),
            $input->getOption(self::$buildAllParam) => $this->buildAll(),
            $input->getOption(self::$dropParam) => $this->drop(),
            $input->getOption(self::$refreshParam) => $this->refresh(),
            $input->getOption(self::$defaultData) => $this->defaultData(),
            default => 'No valid option selected',
        };

        $output->writeln($message);

        return Command::SUCCESS;
    }

    private function buildEntity(): string
    {
        $entityBuilder = new \Entify\EntityBuilder();
        $entityBuilder->build();

        return 'Entitys are now active';
    }

    private function buildSql(): string
    {
        $sqlBuilder = new \Entify\SqlBuilder();
        $sqlBuilder->buildTables();
        $sqlBuilder->declareRelations();

        return 'Database is now active';
    }

    private function buildAll(): string
    {
        $this->buildSql();
        $this->buildEntity();

        return 'Entitys and database are now active';
    }

    private function drop(): string
    {
        $sqlBuilder = new \Entify\SqlBuilder();
        $sqlBuilder->clearDatabase();
        return 'All tables, relations and entitys are now dropped';
    }

    private function refresh(): string
    {
        $this->drop();
        $this->buildAll();

        return 'All tables, relations and entitys are now refreshed';
    }

    private function defaultData(): string
    {
        $sqlBuilder = new \Entify\SqlBuilder();
        $sqlBuilder->defaultData();

        return 'Default data is added to the database';
    }
}