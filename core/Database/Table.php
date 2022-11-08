<?php

namespace Core\Database;

use Closure;

/**
 * Membuat tabel dengan mudah
 * 
 * @class Table
 * @package Core\Database
 */
class Table
{
    /**
     * Param query
     * 
     * @var array $query
     */
    private $query;

    /**
     * Tipe dbms
     * 
     * @var string $type
     */
    private $type;

    /**
     * Nama tabelnya
     * 
     * @var string $table
     */
    private $table;

    /**
     * Alter tabelnya
     * 
     * @var string $alter
     */
    private $alter;

    /**
     * Colums di tabel ini
     * 
     * @var array $columns
     */
    private $columns;

    /**
     * Init objek
     *
     * @return void
     */
    function __construct()
    {
        $this->type = env('DB_DRIV', 'mysql');
        $this->query = [];
        $this->alter = null;
        $this->columns = [];
    }

    /**
     * Set nama table di database
     *
     * @param string $name
     * @return void
     */
    public function table(string $name): void
    {
        $this->table = $name;
    }

    /**
     * Create table sql
     * 
     * @return string
     */
    public function create(): string
    {
        $query = 'CREATE TABLE IF NOT EXISTS ' . $this->table . ' (';
        $query .= join(', ', $this->query);
        $query .= ');';
        $this->query = [];
        $this->columns = [];

        return $query;
    }

    /**
     * Export hasilnya ke string sql
     * 
     * @return string|null
     */
    public function export(): string|null
    {
        if ($this->alter != null) {
            $db = app(DataBase::class);

            foreach ($this->columns as $value) {
                if ($this->type == 'pgsql') {
                    $query = "SELECT column_name FROM information_schema.columns WHERE table_name='{$this->table}' and column_name='{$value}';";
                } else {
                    $query = "SHOW COLUMNS FROM {$this->table} WHERE Field = '{$value}';";
                }

                $db->query($query);
                $db->execute();
                $jumlahColumn = $db->rowCount();

                if ($jumlahColumn != 0) {
                    $this->query = [];
                    $this->alter = null;
                    $this->columns = [];
                    return null;
                }
            }
        }

        $query = 'ALTER TABLE ' . $this->table . ' ';
        $query .= join(', ', array_map(fn ($data) => $this->alter . ' ' . $data, $this->query));
        $query .= ';';
        $this->query = [];
        $this->alter = null;
        $this->columns = [];

        return $query;
    }

    /**
     * Get index paling akhir
     * 
     * @return int
     */
    private function getLastArray(): int
    {
        return count($this->query) - 1;
    }

    /**
     * Id, unique, primary key
     * 
     * @param string $name
     * @return void
     */
    public function id(string $name = 'id'): void
    {
        if ($this->type == 'pgsql') {
            $this->query[] = "$name SERIAL NOT NULL PRIMARY KEY";
        } else {
            $this->query[] = "$name INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT";
        }
        $this->columns[] = $name;
    }

    /**
     * Tipe string atau varchar
     * 
     * @param string $name
     * @param int $len
     * @return self
     */
    public function string(string $name, int $len = 255): self
    {
        $this->query[] = "$name VARCHAR($len) NOT NULL";
        $this->columns[] = $name;
        return $this;
    }

    /**
     * Tipe integer
     * 
     * @param string $name
     * @return self
     */
    public function integer(string $name): self
    {
        if ($this->type == 'pgsql') {
            $this->query[] = "$name BIGINT NOT NULL";
        } else {
            $this->query[] = "$name INTEGER(11) NOT NULL";
        }
        $this->columns[] = $name;
        return $this;
    }

    /**
     * Tipe text
     * 
     * @param string $name
     * @return self
     */
    public function text(string $name): self
    {
        $this->query[] = "$name TEXT NOT NULL";
        $this->columns[] = $name;
        return $this;
    }

    /**
     * Tipe boolean
     * 
     * @param string $name
     * @return self
     */
    public function boolean(string $name): self
    {
        $this->query[] = "$name BOOLEAN NOT NULL";
        $this->columns[] = $name;
        return $this;
    }

    /**
     * Tipe timestamp / datetime
     * 
     * @param string $name
     * @return self
     */
    public function dateTime(string $name): self
    {
        if ($this->type == 'pgsql') {
            $this->query[] = "$name TIMESTAMP WITHOUT TIME ZONE NOT NULL";
        } else {
            $this->query[] = "$name DATETIME NOT NULL";
        }
        $this->columns[] = $name;
        return $this;
    }

    /**
     * created_at and updated_at
     * 
     * @return void
     */
    public function timeStamp(): void
    {
        if ($this->type == 'pgsql') {
            $this->query[] = "created_at TIMESTAMP WITHOUT TIME ZONE NULL";
            $this->query[] = "updated_at TIMESTAMP WITHOUT TIME ZONE NULL";
        } else {
            $this->query[] = "created_at DATETIME NULL";
            $this->query[] = "updated_at DATETIME NULL";
        }
        $this->columns[] = 'created_at';
        $this->columns[] = 'updated_at';
    }

    /**
     * Boleh kosong
     * 
     * @return self
     */
    public function nullable(): self
    {
        $this->query[$this->getLastArray()] = str_replace('NOT NULL', 'NULL', end($this->query));
        return $this;
    }

    /**
     * Default value pada dbms
     * 
     * @param string|int|bool $name
     * @return void
     */
    public function default(string|int|bool $name): void
    {
        $constraint = is_string($name) ? " DEFAULT '$name'" : " DEFAULT " . (is_bool($name) ? ($name ? 'true' : 'false') : $name);

        $this->query[$this->getLastArray()] = end($this->query) . $constraint;
    }

    /**
     * Harus berbeda
     * 
     * @return void
     */
    public function unique(): void
    {
        $this->query[$this->getLastArray()] = end($this->query) . ' UNIQUE';
    }

    /**
     * Bikin relasi antara nama attribute
     * 
     * @param string $name
     * @return self
     */
    public function foreign(string $name): self
    {
        $this->query[] = 'CONSTRAINT FK_' . $this->table . "_$name FOREIGN KEY($name)";
        return $this;
    }

    /**
     * Dengan nama attribute tabel targetnya
     * 
     * @param string $name
     * @return self
     */
    public function references(string $name): self
    {
        $this->query[$this->getLastArray()] = end($this->query) . " REFERENCES TABLE-TARGET($name)";
        return $this;
    }

    /**
     * Nama tabel targetnya
     * 
     * @param string $name
     * @return self
     */
    public function on(string $name): self
    {
        $this->query[$this->getLastArray()] = str_replace('TABLE-TARGET', $name, end($this->query));
        return $this;
    }

    /**
     * Hapus nilai pada foreign key juga jika menghapus
     * 
     * @return void
     */
    public function cascadeOnDelete(): void
    {
        $this->query[$this->getLastArray()] = end($this->query) . ' ON DELETE CASCADE';
    }

    /**
     * Tambahkan kolom baru
     * 
     * @param Closure $fn
     * @return void
     */
    public function addColumn(Closure $fn): void
    {
        $this->alter = 'ADD';
        $fn($this);
    }

    /**
     * Hapus kolom
     * 
     * @param string $name
     * @return void
     */
    public function dropColumn(string $name): void
    {
        $this->alter = 'DROP';
        $this->query[$this->getLastArray()] = 'COLUMN ' . $name;
    }

    /**
     * Rename kolom
     * 
     * @param string $from
     * @param string $to
     * @return void
     */
    public function renameColumn(string $from, string $to): void
    {
        $this->alter = 'RENAME';
        $this->query[$this->getLastArray()] = $from . ' TO ' . $to;
        $this->columns[] = $from;
    }
}
