<?php

namespace App\Database;

interface IDatabase
{
    public function select(string $table, string ...$fields): IQuery;

    public function insert(string $table, array $data): IQuery;

    public function delete(string $table, string $key = null): IQuery;

    public function update(string $table, array $data): IQuery;

    public function transaction(callable $callback);
}