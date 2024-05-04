<?php

namespace Entify;

use PDO;
use PDOException;

class Database extends Init
{
    private $statement;
    private $dbHandler;
    private $error;

    public function __construct() 
    {
        $conn = 'mysql:host=' . self::$dbhost . ';dbname=' . self::$dbname . ';dbport=' . self::$dbport;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );
        try {
            $this->dbHandler = new PDO($conn, self::$dbuser, self::$dbpass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    public function query($sql): void
    {
        $this->statement = $this->dbHandler->prepare($sql);
    }

    //Bind values
    public function bind($parameter, $value, $type = null): void
    {
        $type = match (true) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };

        $this->statement->bindValue($parameter, $value, $type);
    }

    public function execute(): bool
    {
        return $this->statement->execute();
    }

    public function resultSet(): array
    {
        $this->execute();
        return $this->statement->fetchAll(PDO::FETCH_OBJ);
    }

    public function single(): object|bool
    {
        $this->execute();
        return $this->statement->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    public function lastInsertId(): int
    {
        return $this->dbHandler->lastInsertId();
    }
}