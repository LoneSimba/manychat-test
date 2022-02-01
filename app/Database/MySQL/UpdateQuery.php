<?php

namespace App\Database\MySQL;

use PDO;

class UpdateQuery extends AQuery
{
    protected array $update = [];

    public function __construct(PDO &$dbh, string $table, array $data)
    {
        parent::__construct($dbh, $table);

        foreach ($data as $col => $val) {
            $key = ":u_$col";
            $this->update[$col] = $key;
            $this->data[$key] = $val;
        }
    }

    protected function compile(): string
    {
        if (empty($this->update)) {
            return '';
        }

        $comp = "UPDATE $this->table SET ";

        $shouldConcat = count($this->update) > 1;

        $i = 0;
        foreach ($this->update as $col => $key) {
            $comp .= "`$col` = $key";
            if ($shouldConcat && $i < count($this->update) - 1) {
                $comp .= ', ';
            }
        }

        $comp .= $this->compileWhere();
        $comp .= $this->compileOrderBy();
        $comp .= $this->compileLimit();

        return $comp;
    }

    public function result()
    {
        if ($this->exec()) {
            return $this->query?->rowCount();
        }

        return 0;
    }
}