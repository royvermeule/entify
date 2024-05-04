<?php

namespace Entify;

class Table
{
    public function __construct(
        public string $name,
        public array $columns,
        public ?array $relations = null
    ) {
    }

    public function getColumnByName(string $name): ?Column
    {
        foreach ($this->columns as $column) {
            if ($column->name === $name) {
                return $column;
            }
        }
    }
}