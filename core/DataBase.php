<?php

namespace Core;

use Exception;
use PDO;
use PDOException;

class DataBase
{
    private $dbh;
    private $stmt;
    private $transaction;

    private $query;

    function __construct()
    {
        $dsn = sprintf(
            "%s:host=%s;dbname=%s;port=%s;",
            env('DB_DRIV'),
            env('DB_HOST'),
            env('DB_NAME'),
            env('DB_PORT'),
        );

        $option = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->dbh = new PDO($dsn, env('DB_USER'), env('DB_PASS'), $option);
        } catch (PDOException $e) {
            if (DEBUG) {
                throw new Exception($e->getMessage() . ' (SQL:' . $this->query . ')');
            }
            return unavailable();
        }
    }

    public function exec(string $command)
    {
        try {
            //$this->dbh->beginTransaction();
            $result = $this->dbh->exec($command);
            //$this->dbh->commit();

            return $result;
        } catch (PDOException $e) {
            //$this->dbh->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function query(string $query): void
    {
        $this->query = $query;
        $this->stmt = $this->dbh->prepare($this->query);
    }

    public function bind($param, $value, $type = null): void
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    break;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function setTransaction()
    {
        $this->transaction = true;
        $this->dbh->beginTransaction();
    }

    public function execute()
    {
        try {
            $result = $this->stmt->execute();
            if ($this->transaction) {
                $this->dbh->commit();
            }

            return $result;
        } catch (PDOException $e) {
            if ($this->transaction) {
                $this->dbh->rollBack();
            }

            if (DEBUG) {
                throw new Exception($e->getMessage() . ' (SQL: ' . $this->stmt->queryString . ')');
            }
            return false;
        }
    }

    public function getAll()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_CLASS);
    }

    public function get()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId(string $name = '')
    {
        return $this->dbh->lastInsertId($name);
    }
}
