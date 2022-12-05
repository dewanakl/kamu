<?php

namespace Core\Database;

use ArrayIterator;
use Closure;
use Core\Facades\App;
use Countable;
use Exception;
use IteratorAggregate;
use JsonSerializable;
use ReturnTypeWillChange;
use Traversable;

/**
 * Simple query builder
 *
 * @class BaseModel
 * @package Core\Database
 */
class BaseModel implements Countable, IteratorAggregate, JsonSerializable
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
     * Primary key tabelnya
     * 
     * @var string $primaryKey
     */
    private $primaryKey;

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
            $this->db->bind(':' . $key, $val);
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
        return [$this->attribute(), $this->table, $this->dates, $this->primaryKey];
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

        list(
            $this->attributes,
            $this->table,
            $this->dates,
            $this->primaryKey
        ) = $data;
    }

    /**
     * Eksport to json
     *
     * @return string|false
     */
    public function toJson(): string|false
    {
        return json_encode($this->attribute());
    }

    /**
     * Eksport to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return json_decode($this->toJson(), true);
    }

    /**
     * Set nama tabelnya
     *
     * @param string $name
     * @return void
     */
    public function table(string $name): void
    {
        $this->table = $name;
    }

    /**
     * Set tanggal updatenya
     *
     * @param array $date
     * @return void
     */
    public function dates(array $date): void
    {
        $this->dates = $date;
    }

    /**
     * Set primaryKey
     *
     * @param string $primaryKey
     * @return void
     */
    public function primaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * Get primaryKey
     *
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
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
     * Hitung jumlah data attribute
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->attribute());
    }

    /**
     * Refresh attributnya
     *
     * @return BaseModel
     */
    public function refresh(): BaseModel
    {
        return $this->find($this->__get($this->primaryKey));
    }

    /**
     * Where syntax sql
     *
     * @param string $colomn
     * @param mixed $value
     * @param string $statment
     * @param string $agr
     * @return BaseModel
     */
    public function where(string $column, mixed $value, string $statment = '=', string $agr = 'AND'): BaseModel
    {
        if (!$this->query && !$this->param) {
            $this->query = 'SELECT * FROM ' . $this->table;
        }

        if (!str_contains($this->query ?? '', 'WHERE')) {
            $agr = 'WHERE';
        }

        $replaceColumn = str_replace('.', '_', $column);

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
     * @return BaseModel
     */
    public function join(string $table, string $column, string $refers, string $param = '=', string $type = 'INNER'): BaseModel
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
     * @return BaseModel
     */
    public function leftJoin(string $table, string $column, string $refers, string $param = '='): BaseModel
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
     * @return BaseModel
     */
    public function rightJoin(string $table, string $column, string $refers, string $param = '='): BaseModel
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
     * @return BaseModel
     */
    public function fullJoin(string $table, string $column, string $refers, string $param = '='): BaseModel
    {
        return $this->join($table, $column, $refers, $param, 'FULL OUTER');
    }

    /**
     * Order By syntax sql
     *
     * @param string $name
     * @param string $order
     * @return BaseModel
     */
    public function orderBy(string $name, string $order = 'ASC'): BaseModel
    {
        $agr = str_contains($this->query, 'ORDER BY') ? ', ' : ' ORDER BY ';
        $this->query = $this->query . $agr . $name . ' ' . strtoupper($order);

        return $this;
    }

    /**
     * Group By syntax sql
     *
     * @param string $param
     * @return BaseModel
     */
    public function groupBy(string ...$param): BaseModel
    {
        $this->query = $this->query . ' GROUP BY ' . implode(', ', $param);
        return $this;
    }

    /**
     * Having syntax sql
     *
     * @param string $param
     * @return BaseModel
     */
    public function having(string $param): BaseModel
    {
        $this->query = $this->query . ' HAVING ' . $param;
        return $this;
    }

    /**
     * Limit syntax sql
     *
     * @param int $param
     * @return BaseModel
     */
    public function limit(int $param): BaseModel
    {
        $this->query = $this->query . ' LIMIT ' . $param;
        return $this;
    }

    /**
     * Offset syntax sql
     *
     * @param int $param
     * @return BaseModel
     */
    public function offset(int $param): BaseModel
    {
        $this->query = $this->query . ' OFFSET ' . $param;
        return $this;
    }

    /**
     * Select raw syntax sql
     *
     * @param string|array $param
     * @return BaseModel
     */
    public function select(string|array ...$param): BaseModel
    {
        if (is_array($param[0])) {
            $param = $param[0];
        }

        $this->checkSelect();
        $data = explode(' FROM', $this->query);

        $this->query = $data[0] . (str_contains($this->query, 'SELECT *') ? ' ' : ', ') . implode(', ', $param) . ' FROM' . $data[1];
        $this->query = str_replace('SELECT *', 'SELECT', $this->query);

        return $this;
    }

    /**
     * Count sql aggregate
     *
     * @param string $name
     * @return BaseModel
     */
    public function counts(string $name = '*'): BaseModel
    {
        return $this->select('COUNT(' . $name . ')' . ($name == '*' ? null : ' AS ' . $name));
    }

    /**
     * Max sql aggregate
     *
     * @param string $name
     * @return BaseModel
     */
    public function max(string $name): BaseModel
    {
        return $this->select('MAX(' . $name . ') AS ' . $name);
    }

    /**
     * Min sql aggregate
     *
     * @param string $name
     * @return BaseModel
     */
    public function min(string $name): BaseModel
    {
        return $this->select('MIN(' . $name . ') AS ' . $name);
    }

    /**
     * Avg sql aggregate
     *
     * @param string $name
     * @return BaseModel
     */
    public function avg(string $name): BaseModel
    {
        return $this->select('AVG(' . $name . ') AS ' . $name);
    }

    /**
     * Sum sql aggregate
     *
     * @param string $name
     * @return BaseModel
     */
    public function sum(string $name): BaseModel
    {
        return $this->select('SUM(' . $name . ') AS ' . $name);
    }

    /**
     * Hitung jumlah rownya
     *
     * @return int
     */
    public function rowCount(): int
    {
        return $this->db->rowCount();
    }

    /**
     * Ambil semua data
     *
     * @return BaseModel
     */
    public function get(): BaseModel
    {
        $this->checkSelect();

        $this->bind($this->query, $this->param ?? []);
        $this->attributes = $this->db->all();

        return $this;
    }

    /**
     * Ambil satu data aja paling atas
     *
     * @return BaseModel
     */
    public function first(): BaseModel
    {
        $this->checkSelect();

        $this->bind($this->query, $this->param ?? []);
        $this->attributes = $this->db->first();

        return $this;
    }

    /**
     * Ambil semua datanya dari tabel ini
     *
     * @return BaseModel
     */
    public function all(): BaseModel
    {
        return $this->get();
    }

    /**
     * Ambil atau error "tidak ada"
     *
     * @return mixed
     */
    public function firstOrFail(): mixed
    {
        return $this->first()->fail(fn () => notFound());
    }

    /**
     * Error dengan fungsi
     *
     * @param Closure $fn
     * @return mixed
     */
    public function fail(Closure $fn): mixed
    {
        if (!$this->attributes) {
            return $fn();
        }

        return $this;
    }

    /**
     * Cari model id
     *
     * @param mixed $id
     * @param mixed $where
     * @return BaseModel
     * 
     * @throws Exception
     */
    public function id(mixed $id, mixed $where = null): BaseModel
    {
        if (empty($this->primaryKey)) {
            throw new Exception('Primary key tidak terdefinisi !');
        }

        return $this->where(is_null($where) ? $this->primaryKey : $where, $id);
    }

    /**
     * Cari berdasarkan id
     *
     * @param mixed $id
     * @param mixed $where
     * @return BaseModel
     */
    public function find(mixed $id, mixed $where = null): BaseModel
    {
        return $this->id($id, $where)->limit(1)->first();
    }

    /**
     * Cari berdasarkan id atau error "tidak ada"
     *
     * @param mixed $id
     * @param mixed $where
     * @return mixed
     */
    public function findOrFail(mixed $id, mixed $where = null): mixed
    {
        return $this->id($id, $where)->limit(1)->firstOrFail();
    }

    /**
     * Save perubahan pada attribute dengan primarykey
     *
     * @return bool
     * 
     * @throws Exception
     */
    public function save(): bool
    {
        if (empty($this->primaryKey) || empty($this->__get($this->primaryKey))) {
            throw new Exception('Nilai primary key tidak ada !');
        }

        return $this->id($this->__get($this->primaryKey))->update($this->except([$this->primaryKey])->attribute());
    }

    /**
     * Delete by id primary key
     *
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        return $this->id($id)->delete();
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
            implode(', ',  array_map(fn ($data) => ':' . $data, $keys))
        );

        $this->bind($query, $data);
        $result = $this->db->execute();

        if ($result === false) {
            return false;
        }

        $this->attributes = $data;

        $id = $this->db->lastInsertId();
        if ($id) {
            $this->attributes[$this->primaryKey] = intval($id);
        }

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

        return boolval($result);
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

        return boolval($result);
    }

    /**
     * Ambil sebagian dari attribute
     * 
     * @param array $only
     * @return BaseModel
     */
    public function only(array $only): BaseModel
    {
        $temp = [];
        foreach ($only as $ol) {
            $temp[$ol] = $this->__get($ol);
        }

        $this->attributes = $temp;

        return $this;
    }

    /**
     * Ambil kecuali dari attribute
     * 
     * @param array $except
     * @return BaseModel
     */
    public function except(array $except): BaseModel
    {
        $temp = [];
        foreach ($this->attribute() as $key => $value) {
            if (!in_array($key, $except)) {
                $temp[$key] = $value;
            }
        }

        $this->attributes = $temp;

        return $this;
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
     * Isi nilai ke model ini
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * 
     * @throws Exception
     */
    public function __set(string $name, mixed $value): void
    {
        if ($this->primaryKey == $name) {
            throw new Exception('Nilai primary key tidak bisa di ubah !');
        }

        $this->attributes[$name] = $value;
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
