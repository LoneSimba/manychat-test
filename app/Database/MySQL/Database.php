<?php

namespace App\Database\MySQL;

use PDO;
use Dotenv\Dotenv;

use App\Database\{IDatabase, IQuery};

class Database implements IDatabase
{
    private PDO $dbh;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(getcwd());
        $dotenv->load();

        $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}";
        $this->dbh = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
    }

    public function select(string $table, string ...$fields): IQuery
    {
        return new SelectQuery($this->dbh, $table, $fields);
    }

    public function insert(string $table, array $data): IQuery
    {
        return new InsertQuery($this->dbh, $table, $data);
    }

    public function delete(string $table, $key = null): IQuery
    {
        return new DeleteQuery($this->dbh, $table, $key);
    }

    public function update(string $table, array $data): IQuery
    {
        return new UpdateQuery($this->dbh, $table, $data);
    }

    public function createTable(string $table): IQuery
    {
        return new CreateTable($this->dbh, $table);
    }

    public function transaction(callable $callback)
    {
        try {
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbh->beginTransaction();

            $result = $callback();
            $this->dbh->commit();
            return $result;
        } catch (\Exception $e) {
            $this->dbh->rollBack();
            return null;
        }
    }
}