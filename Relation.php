<?php

namespace Entify;

class Relation
{
    public function __construct(
        public string $table,
        public string $type,
        public string $local,
        public string $foreign
    ) {
    }
}