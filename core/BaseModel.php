<?php

namespace Core;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use ReturnTypeWillChange;
use Traversable;

/**
 * Simple query builder
 *
 * @class BaseModel
 * @package Core
 */
class BaseModel implements IteratorAggregate, JsonSerializable
{
    /**
     * String query sql
     * 
     * @var string|null $query
     */
    private $query;

    /**
     * Nilai yang akan dimasukan
     * 
     * @var array $param
     */
    private $param;

    /**
     * Nama tabelnya
     * 
     * @var string $table
     */
    private $table;

    /**
     * Waktu bikin dan update
     * 
     * @var array $dates
     */
    private $dates;

    /**
     * Attributes hasil query
     * 
     * @var array $attributes
     */
    private $attributes;

    /**
     * Object database
     * 
     * @var DataBase $db
     */
    private $db;

    /**
     * Buat objek basemodel
     *
     * @return void
     */
    function __construct()
    {
        $this->connect();
    }

    /**
     * Koneksi ke DataBase
     *
     * @return void
     */
    private function connect(): void
    {
        if (!($this->db instanceof DataBase)) {
            $this->db = App::get()->singleton(DataBase::class);
        }
    }

    /**
     * Ambil attribute
     *
     * @return array
     */
    private function attribute(): array
    {
        if (is_bool($this->attributes)) {
            return [];
        }

        return $this->attributes ?? [];
    }

    /**
     * Bind antara query dengan param
     * 
     * @param string $query
     * @param array $data
     * @return void
     */
    private function bind(string $query, array $data = []): void
    {
        $this->db->query($query);

        foreach ($data as $key => $val) {
            $this->db->bind(":" . $key, $val);
        }

        $this->query = null;
        $this->param = [];
    }

    /**
     * Cek select syntax query
     * 
     * @return void
     */
    private function checkSelect(): void
    {
        if (!str_contains($this->query ?? '', 'SELECT')) {
            $this->query = 'SELECT * FROM ' . $this->table . $this->query;
        }
    }

    /**
     * Ubah objek agar bisa iterasi
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->attribute());
    }

    /**
     * Ubah objek ke json secara langsung
     *
     * @return array
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->attribute();
    }

    /**
     * Ubah objek ke array
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [$this->attribute(), $this->table, $this->dates];
    }

    /**
     * Kebalikan dari serialize
     *
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->connect();
        $this->query = null;
        $this->param = [];

        $this->attributes = $data[0];
        $this->table = $data[1];
        $this->dates = $data[2];
    }

    /**
     * Set nama tabelnya
     *
     * @return void
     */
    public function table(string $name): void
    {
        $this->table = $name;
    }

    /**
     * Set tanggal updatenya
     *
     * @return void
     */
    public function dates(array $date): void
    {
        $this->dates = $date;
    }

    /**
     * Debug querynya
     *
     * @return void
     */
    public function dd(): void
    {
        $this->checkSelect();
        dd($this->query, $this->param);
    }

    /**
     * Mulai transaksinya
     *
     * @return bool
     */
    public function startTransaction(): bool
    {
        return $this->db->startTransaction();
    }

    /**
     * Akhiri transaksinya
     *
     * @return bool
     */
    public function endTransaction(): bool
    {
        return $this->db->endTransaction();
    }

    /**
     * Refresh attributnya
     *
     * @return self
     */
    public function refresh(): self
    {
        return $this->find($this->attributes['id']);
    }

    /**
     * Where syntax sql
     *
     * @param string $colomn
     * @param mixed $value
     * @param string $statment
     * @param string $agr
     * @return self
     */
    public function where(string $column, mixed $value, string $statment = '=', string $agr = 'AND'): self
    {
        if (!$this->query && !$this->param) {
            $this->query = 'SELECT * FROM ' . $this->table;
        }

        if (!str_contains($this->query ?? '', 'WHERE')) {
            $agr = 'WHERE';
        }

        $replaceColumn = str_replace('.', '', $column);

        $this->query = $this->query . " $agr $column $statment :" .  $replaceColumn;
        $this->param[$replaceColumn] = $value;

        return $this;
    }

    /**
     * Join syntax sql
     *
     * @param string $table
     * @param string $column
     * @param string $refers
     * @param string $param
     * @param string $type
     * @return self
     */
    public function join(string $table, string $column, string $refers, string $param = '=', string $type = 'INNER'): self
    {
        $this->query = $this->query . " $type JOIN $table ON $column $param $refers";
        return $this;
    }

    /**
     * Left join syntax sql
     *
     * @param string $table
     * @param string $column
     * @param string $refers
     * @param string $param
     * @return self
     */
    public function leftJoin(string $table, string $column, string $refers, string $param = '='): self
    {
        return $this->join($table, $column, $refers, $param, 'LEFT');
    }

    /**
     * Right join syntax sql
     *
     * @param string $table
     * @param string $column
     * @param string $refers
     * @param string $param
     * @return self
     */
    public function rightJoin(string $table, string $column, string $refers, string $param = '='): self
    {
        return $this->join($table, $column, $refers, $param, 'RIGHT');
    }

    /**
     * Full join syntax sql
     *
     * @param string $table
     * @param string $column
     * @param string $refers
     * @param string $param
     * @return self
     */
    public function fullJoin(string $table, string $column, string $refers, string $param = '='): self
    {
        return $this->join($table, $column, $refers, $param, 'FULL OUTER');
    }

    /**
     * Order By syntax sql
     *
     * @param string $name
     * @param string $order
     * @return self
     */
    public function orderBy(string $name, string $order = 'ASC'): self
    {
        if (str_contains($this->query, 'ORDER BY')) {
            $agr = ', ';
        } else {
            $agr = ' ORDER BY ';
        }

        $this->query = $this->query . $agr . $name . ' ' . $order;

        return $this;
    }

    /**
     * Group By syntax sql
     *
     * @param string $param
     * @return self
     */
    public function groupBy(string ...$param): self
    {
        $this->query = $this->query . ' GROUP BY ' . implode(', ', $param);
        return $this;
    }

    /**
     * Having syntax sql
     *
     * @param string $param
     * @return self
     */
    public function having(string $param): self
    {
        $this->query = $this->query . ' HAVING ' . $param;
        return $this;
    }

    /**
     * Limit syntax sql
     *
     * @param int $param
     * @return self
     */
    public function limit(int $param): self
    {
        $this->query = $this->query . ' LIMIT ' . $param;
        return $this;
    }

    /**
     * Select raw syntax sql
     *
     * @param string $param
     * @return self
     */
    public function select(string ...$param): self
    {
        $this->checkSelect();
        $param = implode(', ', $param);

        $this->query = str_replace('SELECT * FROM', "SELECT $param FROM", $this->query);
        return $this;
    }

    /**
     * Ambil semua data
     *
     * @return self
     */
    public function get(): self
    {
        $this->checkSelect();

        $this->bind($this->query, $this->param ?? []);
        $this->attributes = $this->db->all();

        return $this;
    }

    /**
     * Ambil satu data aja paling atas
     *
     * @return self
     */
    public function first(): self
    {
        $this->checkSelect();

        $this->bind($this->query, $this->param ?? []);
        $this->attributes = $this->db->first();

        return $this;
    }

    /**
     * Ambil atau error "tidak ada"
     *
     * @return self
     */
    public function firstOrFail(): self
    {
        $result = $this->first();
        if (!$result->attributes) {
            notFound();
        }

        return $result;
    }

    /**
     * Cari berdasarkan id
     *
     * @param mixed $id
     * @param string $where
     * @return self
     */
    public function find(mixed $id, string $where = 'id'): self
    {
        return $this->where($where, $id)->limit(1)->first();
    }

    /**
     * Ambil semua datanya dari tabel ini
     *
     * @return self
     */
    public function all(): self
    {
        return $this->get();
    }

    /**
     * Isi datanya
     * 
     * @param array $data
     * @return mixed
     */
    public function create(array $data): mixed
    {
        if ($this->dates) {
            $now = now('Y-m-d H:i:s.u');
            $data = array_merge($data, array_combine($this->dates, array($now, $now)));
        }

        $keys = array_keys($data);

        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ',  $keys),
            implode(', ',  array_map(fn ($data) => ":" . $data, $keys))
        );

        $this->bind($query, $data);
        $result = $this->db->execute();

        if ($result === false) {
            return false;
        }

        $id = $this->db->lastInsertId();
        $this->attributes = array_merge($data, ['id' => $id]);

        return $this;
    }

    /**
     * Update datanya
     * 
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        if ($this->dates) {
            $data = array_merge($data, [$this->dates[1] => now('Y-m-d H:i:s.u')]);
        }

        $query = ($this->query) ? str_replace('SELECT * FROM', 'UPDATE', $this->query) : 'UPDATE ' . $this->table . ' WHERE';
        $setQuery = 'SET ' . implode(', ',  array_map(fn ($data) => $data . ' = :' . $data, array_keys($data))) . (($this->query) ? ' WHERE' : '');

        $this->bind(str_replace('WHERE', $setQuery, $query), array_merge($data, $this->param ?? []));
        $result = $this->db->execute();

        if ($result === false) {
            return false;
        }

        return $result;
    }

    /**
     * Hapus datanya
     * 
     * @return bool
     */
    public function delete(): bool
    {
        $query = ($this->query) ? str_replace('SELECT *', 'DELETE', $this->query) : 'DELETE FROM ' . $this->table;

        $this->bind($query, $this->param ?? []);
        $result = $this->db->execute();

        if ($result === false) {
            return false;
        }

        return $result;
    }

    /**
     * Ambil nilai dari attribute
     * 
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($this->__isset($name)) {
            return $this->attributes[$name];
        }

        return null;
    }

    /**
     * Cek nilai dari attribute
     * 
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
}
