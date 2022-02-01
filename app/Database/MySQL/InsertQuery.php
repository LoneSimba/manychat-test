<?php

namespace App\Database\MySQL;

use App\Database\EDirection;
use PDO;

class InsertQuery extends AQuery
{
    protected array $insert = [];

    public function __construct(PDO &$dbh, string $table, array $data)
    {
        parent::__construct($dbh, $table);

        foreach ($data as $col => $val) {
            $key = ":i_$col";
            $this->insert[$col] = $key;
            $this->data[$key] = $val;
        }
    }

    protected function compile(): string
    {
        if (empty($this->insert)) {
            return '';
        }

        $compCols = $compKeys = '(';

        $shouldConcat = count($this->insert) > 1;

        $i = 0;
        foreach ($this->insert as $col => $key) {
            $compCols .= "`$col`";
            $compKeys .= "$key";
            if ($shouldConcat && $i < count($this->insert) - 1) {
                $compCols .= ', ';
                $compKeys .= ', ';
            }
        }
        $compKeys .= ')';
        $compCols .= ')';

        return "INSERT INTO $this->table $compCols VALUES$compKeys";
    }

    public function where(array $data, string $sign = '=', bool $disjunctive = false): AQuery
    {
        return $this;
    }

    public function orderBy(string $col, EDirection $direction): AQuery
    {
        return $this;
    }

    public function limit(int $limit): AQuery
    {
        return $this;
    }

    public function result()
    {
        if ($this->exec()) {
            return $this->query?->rowCount();
        }

        return 0;
    }
}