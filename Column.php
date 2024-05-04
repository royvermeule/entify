<?php

namespace Entify;

class Column
{
    public function __construct(
        public string $name,
        public string $type,
        public ?string $length = null,
        public ?string $default,
        public bool $primary = false,
        public bool $auto_increment = false,
    ) {
    }
}