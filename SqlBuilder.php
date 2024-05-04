<?php

namespace Entify;
use Symfony\Component\Yaml\Yaml;

class SqlBuilder extends SchemaReader
{
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = new Database;
    }
    
    public function buildTables(): void
    {
        $query = '';
        foreach ($this->tables as $table) {
            $query .= "CREATE TABLE IF NOT EXISTS {$table->name} (";
            foreach ($table->columns as $column) {
                $query .= "{$column->name} {$column->type}";
                if ($column->length) {
                    $query .= "({$column->length})";
                }
                if ($column->primary && $column->primary === true) {
                    $query .= " PRIMARY KEY";
                }
                if ($column->auto_increment && $column->auto_increment === true) {
                    $query .= " AUTO_INCREMENT";
                }
                if ($column->default) {
                    $query .= " DEFAULT {$column->default}";
                }
                $query .= ',';
            }
            $query = rtrim($query, ',');
            $query .= ");";
        }


        $this->db->query($query);
        $this->db->execute();
    }

    public function declareRelations(): void
    {
        $query = '';
        foreach ($this->tables as $table) {
            if (isset($table->relations)) {
                foreach ($table->relations as $relationName => $relation) {
                    $type = $relation->type;
                    if (
                        ($type === 'manyToOne' && $table->name === $relation->table) ||
                        ($type === 'oneToMany' && $table->name !== $relation->table) ||
                        $type === 'oneToOne'
                    ) {
                        foreach ($this->tables as $innerTable) {
                            if ($innerTable->name === $relation->table) {
                                foreach ($innerTable->relations as $innerRelationName => $innerTableRelation) {
                                    if (
                                        $innerTableRelation->table === $table->name &&
                                        $innerTableRelation->local === $relation->foreign &&
                                        $innerTableRelation->foreign === $relation->local
                                    ) {
                                        $query .= "ALTER TABLE {$relation->table} ADD FOREIGN KEY ({$relation->foreign}) REFERENCES {$table->name}({$relation->local});";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    
        
        $this->db->query($query);
        $this->db->execute();
    }

    public function clearDatabase(): void
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->db->execute();
    
        foreach ($this->tables as $table) {
            $query = "DROP TABLE IF EXISTS {$table->name};";
            $this->db->query($query);
            $this->db->execute();
        }
    
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
        $this->db->execute();
    }

    public function defaultData(): void
    {
        require_once self::$defaultData;
    }
}