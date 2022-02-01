<?php

namespace App\Database\MySQL;

use PDO;

class SelectQuery extends AQuery
{
    public function __construct(PDO &$dbh, string $table, protected array $cols)
    {
        parent::__construct($dbh, $table);
    }

    protected function compile(): string
    {
        if (empty($this->cols)) {
            return '';
        }

        $comp = 'SELECT';

        $insertCommas = count($this->cols) > 1;
        foreach ($this->cols as $i => $col) {
            if ($col === '*') {
                $comp .= " $col";
                break;
            } else {
                $comp .= " `$col`";

                if ($insertCommas && $i < count($this->cols) - 1) {
                    $comp .= ',';
                }
            }
        }

        $comp .= " FROM $this->table";

        $comp .= $this->compileWhere();
        $comp .= $this->compileOrderBy();
        $comp .= $this->compileLimit();

        return $comp;
    }

    public function result()
    {
        if ($this->exec()) {
            $result = $this->query?->fetchAll(PDO::FETCH_ASSOC);

            return $result ?? [];
        }

        return [];
    }
}