<?php

namespace Core\Database;

use Exception;
use PDO;
use PDOException;
use Throwable;

/**
 * Hubungkan ke database yang ada dengan pdo
 *
 * @class DataBase
 * @package Core\Database
 */
class DataBase
{
    /**
     * Object PDO
     * 
     * @var object $pdo
     */
    private $pdo;

    /**
     * Statement dari query 
     * 
     * @var PDOStatement|false $stmt
     */
    private $stmt;

    /**
     * Apakah transaksi
     * 
     * @var bool $transaction
     */
    private $transaction;

    /**
     * Buat objek database
     *
     * @return void
     * 
     * @throws PDOException
     */
    function __construct()
    {
        $dsn = sprintf(
            "%s:host=%s;dbname=%s;port=%s;",
            env('DB_DRIV'),
            env('DB_HOST'),
            env('DB_NAME'),
            env('DB_PORT')
        );

        $option = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            if (empty($this->pdo)) {
                $this->pdo = new PDO($dsn, env('DB_USER'), env('DB_PASS'), $option);
            }

            $this->transaction = false;
        } catch (PDOException $e) {
            try {
                if (DEBUG) {
                    $this->queryException($e);
                }
            } catch (Throwable) {
                $this->queryException($e);
            }

            return unavailable();
        }
    }

    /**
     * Tampilkan error
     *
     * @param mixed $e
     * @return void
     * 
     * @throws Exception
     */
    private function queryException(mixed $e): void
    {
        $sql = (@$this->stmt->queryString) ? PHP_EOL . PHP_EOL . 'SQL: "' . $this->stmt->queryString . '"' : null;
        throw new Exception($e->getMessage() . $sql);
    }

    /**
     * Mulai transaksinya
     *
     * @return bool
     */
    public function startTransaction(): bool
    {
        $this->transaction = true;
        return $this->pdo->beginTransaction();
    }

    /**
     * Akhiri transaksinya
     *
     * @return bool
     */
    public function endTransaction(): bool
    {
        if ($this->transaction) {
            return $this->pdo->commit();
        }

        return false;
    }

    /**
     * Eksekusi raw sql
     *
     * @param string $command
     * @return int|false
     * 
     * @throws PDOException
     */
    public function exec(string $command): int|false
    {
        try {
            return $this->pdo->exec($command);
        } catch (PDOException $e) {
            try {
                if (DEBUG) {
                    $this->queryException($e);
                }
            } catch (Throwable) {
                $this->queryException($e);
            }

            return false;
        }
    }

    /**
     * Siapkan querynya
     *
     * @param string $query
     * @return void
     */
    public function query(string $query): void
    {
        $this->stmt = $this->pdo->prepare($query);
    }

    /**
     * Siapkan juga valuenya
     *
     * @param int|string $param
     * @param mixed $value
     * @param ?int $type
     * @return void
     */
    public function bind(int|string $param, mixed $value, ?int $type = null): void
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

    /**
     * Eksekusi juga
     *
     * @return mixed
     * 
     * @throws Exception
     */
    public function execute(): mixed
    {
        try {
            return $this->stmt->execute();
        } catch (Exception $e) {
            if ($this->transaction) {
                $this->pdo->rollBack();
            }

            try {
                if (DEBUG) {
                    $this->queryException($e);
                }
            } catch (Throwable) {
                $this->queryException($e);
            }

            return unavailable();
        }
    }

    /**
     * Tampilkan semua
     *
     * @return mixed
     */
    public function all(): mixed
    {
        if (!$this->execute()) {
            return false;
        }

        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Tampilkan satu aja
     *
     * @return mixed
     */
    public function first(): mixed
    {
        if (!$this->execute()) {
            return false;
        }

        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Hitung jumlah rownya
     *
     * @return int
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Dapatkan idnya
     * 
     * @param ?string $name
     * @return string|false
     */
    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }
}
