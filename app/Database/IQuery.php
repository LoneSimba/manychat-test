<?php

namespace App\Database;

interface IQuery
{
    public function primary(): array|null;

    public function where(array $data, string $sign = '=', bool $disjunctive = false): self;

    public function limit(int $limit): self;

    public function orderBy(string $col, EDirection $direction): self;

    public function exec(): bool;

    public function result();
}