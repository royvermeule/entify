<?php

namespace Entify;
use Symfony\Component\Yaml\Yaml;

class SchemaReader extends Init
{
    private array $schemaYaml;
    public array $tables;

    public function __construct()
    {
        parent::__construct();

        $this->schemaYaml = Yaml::parse(self::$schema);        

        $this->setTables();
    }

    private function setTables(): void
    {
        foreach ($this->schemaYaml as $tableName => $table) {
            $columns = [];
            foreach ($table['columns'] as $columnName => $column) {
                $columns[] = new Column(
                    $columnName,
                    $column['type'],
                    $column['length'] ?? null,
                    $column['default'] ?? null,
                    $column['primary'] ?? false,
                    $column['auto_increment'] ?? false
                );
            }

            $relations = null;
            if (isset($table['relations'])) {
                $relations = [];
                foreach ($table['relations'] as $relationName => $relation) {
                    $relations[] = new Relation(
                        $relationName,
                        $relation['type'],
                        $relation['local'],
                        $relation['foreign']
                    );
                }
            }

            $this->tables[] = new Table(
                $tableName,
                $columns,
                $relations
            );
        }
    }

    public function getTableByName(string $name): ?Table
    {
        foreach ($this->tables as $table) {
            if ($table->name === $name) {
                return $table;
            }
        }
    }
}