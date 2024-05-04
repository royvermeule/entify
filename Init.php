<?php

namespace Entify;

class Init 
{
    public static string $entitiesLocation;
    public static string $entitiesNamespace;
    public static string $schema;
    public static string $defaultData;
    public static string $dbhost;
    public static string $dbuser;
    public static string $dbpass;
    public static string $dbname;
    public static int $dbport;

    public const ROOT_DIR = __DIR__ . '/../../../';

    public function __construct() 
    {
        $config = require self::ROOT_DIR . '/entify-config.php';

        $entities = $config['entities'];
        self::$entitiesNamespace = array_key_first($entities);
        self::$entitiesLocation = $entities[self::$entitiesNamespace];

        self::$schema = file_get_contents($config['schema']);
        self::$defaultData = $config['default-data'];

        self::$dbhost = $config['database-connection']['DB_HOST'];
        self::$dbuser = $config['database-connection']['DB_USER'];
        self::$dbpass = $config['database-connection']['DB_PASS'];
        self::$dbname = $config['database-connection']['DB_NAME'];
        self::$dbport = $config['database-connection']['DB_PORT'];
    }
}