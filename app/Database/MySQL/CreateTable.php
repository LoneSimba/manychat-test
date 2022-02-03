<?php

namespace App\Database\MySQL;

use PDO;

class CreateTable extends AQuery
{
    protected array $cols = [];

    protected array $primary = [];

    protected array $timestamps = [];

    protected array $foreign = [];

    public function __construct(PDO &$dbh, string $table)
    {
        parent::__construct($dbh, $table);
    }

    public function id(): self
    {
        $this->primary = [
            'name' => 'id',
            'key' => 'primary',
            'length' => 0,
            'type' => 'int unsigned',
            'ext' => 'auto_increment'
        ];

        return $this;
    }

    public function timestamps(): self
    {
        $this->timestamps = [[
            'name' => 'created_at',
            'type' => 'timestamp',
            'default' => 'current_timestamp',
            'null' => false
        ], [
            'name' => 'updated_at',
            'type' => 'timestamp',
            'default' => 'current_timestamp',
            'null' => false,
            'ext' => 'on update current_timestamp'
        ]];

        return $this;
    }

    public function string(string $name, int $length = 0, bool $nullable = false): self
    {
        $this->cols[] = [
            'name' => $name,
            'type' => 'varchar',
            'length' => $length,
            'null' => $nullable
        ];

        return $this;
    }

    public function int(string $name, int $length = 0, bool $nullable = false): self
    {
        $this->cols[] = [
            'name' => $name,
            'type' => 'int',
            'length' => $length,
            'null' => $nullable
        ];

        return $this;
    }

    public function foreign(string $col_name, string $table, string $ext_name): self
    {
        $this->foreign[] = [
            'col' => $col_name,
            'table' => $table,
            'ext_name' => $ext_name
        ];

        return $this;
    }

    protected function compile(): string
    {
        array_unshift($this->cols, $this->primary);
        $this->cols = array_merge($this->cols, $this->timestamps);

        $comp = "CREATE TABLE IF NOT EXISTS $this->table (";
        foreach ($this->cols as $i => $col) {
            $comp .= " `{$col['name']}` {$col['type']}";

            if (isset($col['length']) && $col['length'] > 0) {
                $comp .= "({$col['length']})";
            }

            if (isset($col['default'])) {
                $comp .= " default {$col['default']}";
            }

            if (isset($col['null']) && !$col['null']) {
                $comp .= " not null";
            }

            if (isset($col['ext'])) {
                $comp .= " {$col['ext']}";
            }

            if (isset($col['key']) && $col['key'] == 'primary') {
                $comp .= " primary key";
            }

            if ($i < count($this->cols) - 1) {
                $comp .= ",";
            }
        }

        foreach ($this->foreign as $i => $key) {
            $comp .= ", foreign key (`{$key['col']}`) references {$key['table']} (`{$key['ext_name']}`)";
        }

        $comp .= " );";
        return $comp;
    }

    public function result()
    {
        try {
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->exec();
        } catch (\Exception $e) {
            echo $e;
            return false;
        }
    }
}