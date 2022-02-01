<?php

namespace App\Database\MySQL;

use PDO, PDOStatement;

use App\Database\IQuery;
use App\Database\EDirection;

abstract class AQuery implements IQuery
{
    protected int $limit = 0;

    protected array $data = [];

    protected array $where = [];

    protected string $orderBy = '';

    protected string $orderDirection = '';

    protected PDOStatement|null $query;

    public function __construct(protected PDO &$dbh, protected string $table)
    { }

    public function primary(): array|null
    {
        $_s = $this->dbh->prepare("SHOW COLUMNS FROM $this->table WHERE `Key` = 'PRI'");

        if ($_s->execute()) {
            $_i = $_s->fetch(PDO::FETCH_ASSOC);
            $_n = $_i['Field'];
            $_t = $_i['Type'];

            if (str_contains($_t, 'int')) {
                $_type = 'integer';
            } elseif (str_contains($_t, 'varchar')) {
                $_type = 'string';
            } else {
                $_type = null;
            }

            return [
                'name' => $_n,
                'type' => $_type
            ];
        }

        return null;
    }

    public function where(array $data, string $sign = '=', bool $disjunctive = false): self
    {
        foreach ($data as $col => $val) {
            $key = ":w_$col";
            $this->where[$col] = [
                'key' => $key,
                'sign' => $sign,
                'operator' => $disjunctive ? 'OR' : 'AND',
            ];
            $this->data[$key] = $val;
        }

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function orderBy(string $col, EDirection $direction): self
    {
        $this->orderBy = $col;
        $this->orderDirection = $direction->value;

        return $this;
    }

    public function exec(): bool
    {
        $compiled = $this->compile();
        if (empty($compiled)) {
            return false;
        }

        $this->query = $this->dbh->prepare($compiled) ?? null;

        if (!$this->query || !$this->query->execute($this->data)) {
            return false;
        }

        return true;
    }

    public function result()
    {
        return false;
    }

    protected function compile(): string
    {
        return '';
    }

    protected function compileWhere(): string
    {
        if (empty($this->where)) {
            return '';
        }

        $comp = ' WHERE';
        $concat = count($this->where) > 1;

        $i = 0;
        foreach ($this->where as $col => $item) {
            $comp .= " `$col` {$item['sign']} {$item['key']}";

            if ($concat && $i < count($this->where) - 1) {
                $comp .= " {$item['operator']}";
            }

            $i++;
        }

        return $comp;
    }

    protected function compileLimit(): string
    {
        if (!isset($this->limit) || $this->limit == 0) {
            return '';
        }

        return " LIMIT $this->limit";
    }

    protected function compileOrderBy(): string
    {
        if (empty($this->orderBy)) {
            return '';
        }

        $dir = $this->orderDirection ?? 'ASC';

        return " ORDER BY `$this->orderBy` $dir";
    }
}