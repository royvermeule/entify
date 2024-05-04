<?php

namespace Entify;
use Entify\Enums\ColumnType;

class EntityBuilder extends SchemaReader
{
    private array $entities;

    public function __construct()
    {
        parent::__construct();

        foreach ($this->tables as $table) {
            $this->entities[$table->name] = [
                'properties' => $this->setProperties($table),
                'mappers' => $this->setMappers($table),
                'setters' => $this->setSetters($table),
                'getters' => $this->setGetters($table),
                'relations' => $this->setRelations($table),
            ];
        }
    }

    private function setProperties(Table $table): array
    {
        $properties = [];
        foreach ($table->columns as $column) {
            $columnType = ColumnType::fromYamlType($column->type)->toPhpType();
            $property = "private $columnType $$column->name;\n";
            $properties[] = $property;
        }

        return $properties;
    }

    private function setMappers (Table $table): array
    {
        $mappers = [];
        foreach ($table->columns as $column) {
            if (!isset($mappers['getAll'])) {
                $getall = "public function getAll(): array";
                $getall .= "{";
                $getall .= "\$query = 'SELECT * FROM $table->name';";
                $getall .= "\$this->query(\$query);";
                $getall .= "\$result = \$this->resultSet();";
                $getall .= "\$entities = [];";
                $getall .= "foreach (\$result as \$row) {";
                $getall .= "\$entity = new " . ucfirst($table->name) . "();";
                foreach ($table->columns as $innerCol) {
                    $getall .= "\$entity->set" . ucfirst($innerCol->name) . "(\$row->$innerCol->name);";
                }
                $getall .= "\$entities[] = \$entity;";
                $getall .= "}";
                $getall .= "return \$entities;";
                $getall .= "}\n";

                $mappers['getAll'] = $getall;
            }

            if (!isset($mappers['create'])) {
                $create = "public function create(";
                $create .= "): void";
                $create .= "{";
                $create .= "\$query = 'INSERT INTO $table->name (";
                foreach ($table->columns as $innerCol) {
                    if (!$innerCol->auto_increment) {
                        $create .= "$innerCol->name,";
                    }
                }
                $create = rtrim($create, ',');
                $create .= ") VALUES (";
                foreach ($table->columns as $innerCol) {
                    if (!$innerCol->auto_increment) {
                        $create .= ":$innerCol->name,";
                    }
                }
                $create = rtrim($create, ',');
                $create .= ")';";
                $create .= "\$this->query(\$query);";
                foreach ($table->columns as $innerCol) {
                    if (!$innerCol->auto_increment) {
                        $create .= "\$this->bind(':$innerCol->name', \$this->$innerCol->name);";
                    }
                }
                $create .= "\$this->execute();";
                foreach ($table->columns as $innerCol) {
                    if ($innerCol->auto_increment) {
                        $create .= "\$this->{$innerCol->name} = \$this->lastInsertId();";
                    }
                }
                $create .= "}\n";
                $mappers['create'] = $create;

                
            }

            if (!isset($mappers['update'])) {
                $update = "public function update(): void";
                $update .= "{";
                $update .= "\$query = 'UPDATE $table->name SET ";
                foreach ($table->columns as $innerCol) {
                    if (!$innerCol->auto_increment) {
                        $update .= "$innerCol->name = :$innerCol->name,";
                    }
                }
                $update = rtrim($update, ',');
                $update .= " WHERE ";
                foreach ($table->columns as $innerCol) {
                    if ($innerCol->primary) {
                        $update .= "$innerCol->name = :$innerCol->name";
                    }
                }
                $update .= "';";
                $update .= "\$this->query(\$query);";
                foreach ($table->columns as $innerCol) {
                    $update .= "\$this->bind(':$innerCol->name', \$this->$innerCol->name);";
                }
                $update .= "\$this->execute();";
                $update .= "}\n";
                $mappers['update'] = $update;
            }

            if (!isset($mappers['delete'])) {
                $delete = "public function delete(";
                foreach ($table->columns as $innerCol) {
                    if ($innerCol->primary) {
                        $columnType = ColumnType::fromYamlType($innerCol->type)->toPhpType();
                        $delete .= "$columnType $$innerCol->name): void";
                    }
                }
                $delete .= "{";
                $delete .= "\$query = 'DELETE FROM $table->name WHERE ";
                foreach ($table->columns as $innerCol) {
                    if ($innerCol->primary) {
                        $delete .= "$innerCol->name = :$innerCol->name";
                    }
                }
                $delete .= "';";
                $delete .= "\$this->query(\$query);";
                foreach ($table->columns as $innerCol) {
                    if ($innerCol->primary) {
                        $delete .= "\$this->bind(':$innerCol->name', $$innerCol->name);";
                    }
                }
                $delete .= "\$this->execute();";
                $delete .= "}\n";
                $mappers['delete'] = $delete;
            }

            $getByName = "public function getById(int $$table->name): array";

            $columnType = ColumnType::fromYamlType($column->type)->toPhpType();
            $getBy = "public function getBy" . ucfirst($column->name) . "($columnType $$column->name): array";
            $getBy .= "{";
            $getBy .= "\$query = 'SELECT * FROM $table->name WHERE $column->name = :$column->name';";
            $getBy .= "\$this->query(\$query);";
            $getBy .= "\$result = \$this->resultSet();";
            $getBy .= "\$entities = [];";
            $getBy .= "foreach (\$result as \$row) {";
            $getBy .= "\$entity = new " . ucfirst($table->name) . "();";
            foreach ($table->columns as $innerCol) {
                $getBy .= "\$entity->set" . ucfirst($innerCol->name) . "(\$row->$innerCol->name);";
            }
            $getBy .= "\$entities[] = \$entity;";
            $getBy .= "}";
            $getBy .= "return \$entities;";
            $getBy .= "}";
            $mappers[$getBy] = $getBy;
        }

        return $mappers;
    }

    private function setSetters(Table $table): array
    {
        $setters = [];
        foreach ($table->columns as $column) {
            $columnType = ColumnType::fromYamlType($column->type)->toPhpType();

            $method = "public function set" . ucfirst($column->name) . "($columnType $$column->name): void";
            $method .= "{";
            $method .= "\$this->$column->name = $$column->name;";
            $method .= "}";

            $setters[] = $method;
        }

        return $setters;
    }

    private function setGetters(Table $table): array
    {
        $getters = [];
        foreach ($table->columns as $column) {
            $columnType = ColumnType::fromYamlType($column->type)->toPhpType();

            $method = "public function get" . ucfirst($column->name) . "(): $columnType";
            $method .= "{";
            $method .= "return \$this->$column->name;";
            $method .= "}";

            $getters[] = $method;
        }

        return $getters;
    }

    private function setRelations(Table $table): array
    {
        $setters = [];
        if ($table->relations) {
            foreach ($table->relations as $relation) {
                $localColumn = $table->getColumnByName($relation->local);
                $localColumnType = ColumnType::fromYamlType($localColumn->type)->toPhpType();

                $methodName = "public function get" . ucfirst($relation->table) . "($localColumnType $$relation->local)";

                if (!isset($this->getters[$methodName])) {
                    $method = "public function get" . ucfirst($relation->table) . "($localColumnType $$relation->local)";
                    $method .= "{";
                    $method .= "$$relation->table = new " . ucfirst($relation->table) . ';';
                    $method .= "return $$relation->table" . "->getBy" . ucfirst($relation->foreign) . "($$relation->local);";
                    $method .= "}";
    
                    $setters[$methodName] = $method;
                }
            }
        }

        return $setters;
    }

    public function build(): void
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
    
        foreach ($this->entities as $name => $entity) {
            $properties = $entity['properties'];
            $mappers = $entity['mappers'];
            $setters = $entity['setters'];
            $getters = $entity['getters'];
            $relations = $entity['relations'];
    
            $entity = "<?php\n\n";
            $entity .= "namespace ". self::$entitiesNamespace .";\n\n";
            $entity .= "use Entify\Database;\n\n";
            $entity .= "class " . ucfirst($name) . " extends Database\n";
            $entity .= "{\n";
            $entity .= implode("\n\n", $properties);
            $entity .= "\n\n";
            $entity .= implode("\n\n", $mappers);
            $entity .= "\n\n";
            $entity .= implode("\n\n", $setters);
            $entity .= "\n\n";
            $entity .= implode("\n\n", $getters);
            $entity .= "\n\n";
            $entity .= implode("\n\n", $relations);
            $entity .= "\n";
            $entity .= "}\n";
    
            // Parse the PHP code to an AST
            $parser = (new \PhpParser\ParserFactory)->create(\PhpParser\ParserFactory::PREFER_PHP7);
            $stmts = $parser->parse($entity);
    
            // Pretty print the AST
            $formattedEntity = $prettyPrinter->prettyPrintFile($stmts);
    
            file_put_contents(self::ROOT_DIR . self::$entitiesLocation . '/' . ucfirst($name) . '.php', $formattedEntity);
        }
    }
}