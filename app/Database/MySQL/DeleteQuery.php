<?php

namespace App\Database\MySQL;

use PDO;

class DeleteQuery extends AQuery
{
    protected $key;

    public function __construct(PDO &$dbh, string $table, $key = null)
    {
        parent::__construct($dbh, $table);

        if (!empty($key)) {
            $primary = $this->primary();

            if (!$primary || !$primary['type']) {
                return;
            }

            if (gettype($key) === $primary['type']) {
                $_key = ":w_{$primary['name']}";
                $this->where[$primary['name']] = [
                    'key' => $_key,
                    'sign' => '=',
                    'operator' => 'AND',
                ];
                $this->data[$_key] = $key;
            }
        }
    }

    protected function compile(): string
    {
        $comp = "DELETE FROM $this->table";
        $comp .= $this->compileWhere();
        $comp .= $this->compileOrderBy();
        $comp .= $this->compileLimit();

        return $comp;
    }

    public function result()
    {
        if ($this->exec()) {
            return $this->query->rowCount();
        }

        return 0;
    }
}