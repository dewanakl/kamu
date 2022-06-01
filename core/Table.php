<?php

namespace Core;

/**
 * Table builder
 * simple class table builder
 */
class Table
{
    private $query = array();
    private $type;
    private $table;

    function __construct()
    {
        $this->type = $_ENV['DB_DRIV'];
    }

    /**
     * Set table di database
     *
     * @param string $name
     *
     * @return void
     */
    public function table(string $name): void
    {
        $this->table = $name;
    }

    /**
     * Export hasilnya ke string sql
     * 
     * @return string
     */
    public function export(): string
    {
        $query = 'CREATE TABLE IF NOT EXISTS ' . $this->table . ' (';
        $query .= join(", ", $this->query);
        $query .= ');';
        $this->query = array();

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
     * Id atribute
     * 
     * @param string $name
     * 
     * @return void
     */
    public function id(string $name = 'id'): void
    {
        if ($this->type == 'pgsql') {
            $this->query[] = "$name SERIAL NOT NULL PRIMARY KEY";
        } else {
            $this->query[] = "$name INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT";
        }
    }

    public function unsignedInteger(string $name): void
    {
        $this->query[] = "$name INT NOT NULL";
    }

    public function string(string $name, int $len = 255): self
    {
        $this->query[] = "$name VARCHAR($len) NOT NULL";
        return $this;
    }

    public function integer(string $name): self
    {
        if ($this->type == 'pgsql') {
            $this->query[] = "$name bigint NOT NULL";
        } else {
            $this->query[] = "$name INTEGER(11) NOT NULL";
        }

        return $this;
    }

    public function text(string $name): self
    {
        $this->query[] = "$name TEXT NOT NULL";
        return $this;
    }

    public function dateTime(string $name): self
    {
        if ($this->type == 'pgsql') {
            $this->query[] = "$name timestamp without time zone NOT NULL";
        } else {
            $this->query[] = "$name datetime NOT NULL";
        }

        return $this;
    }

    public function timeStamp(): void
    {
        if ($this->type == 'pgsql') {
            $this->query[] = "create_at timestamp without time zone NOT NULL DEFAULT NOW()";
            $this->query[] = "update_at timestamp without time zone NOT NULL DEFAULT NOW()";
        } else {
            $this->query[] = "create_at datetime NOT NULL DEFAULT NOW()";
            $this->query[] = "update_at datetime NOT NULL DEFAULT NOW()";
        }
    }

    public function nullable(): self
    {
        $this->query[$this->getLastArray()] = str_replace('NOT NULL', 'NULL', end($this->query));
        return $this;
    }

    public function default(string|int $name): void
    {
        if (is_string($name)) {
            $constraint = " DEFAULT '$name'";
        } else {
            $constraint = " DEFAULT $name";
        }

        $this->query[$this->getLastArray()] = end($this->query) . $constraint;
    }

    public function unique(): void
    {
        $this->query[$this->getLastArray()] = end($this->query) . ' UNIQUE';
    }

    public function foreign(string $name): self
    {
        $this->query[] = "CONSTRAINT FK_$name FOREIGN KEY($name)";
        return $this;
    }

    public function references(string $name): self
    {
        $this->query[$this->getLastArray()] = end($this->query) . " REFERENCES TABLE-TARGET($name)";
        return $this;
    }

    public function on(string $name): self
    {
        $this->query[$this->getLastArray()] = str_replace('TABLE-TARGET', $name, end($this->query));
        return $this;
    }

    public function cascadeOnDelete(): void
    {
        $this->query[$this->getLastArray()] = end($this->query) . ' ON DELETE CASCADE';
    }
}
