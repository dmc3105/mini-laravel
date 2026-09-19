<?php

namespace Minilaravel\ORM;

use Minilaravel\ORM\Attributes\Table;
use Minilaravel\ORM\Database;
use Minilaravel\ORM\QueryBuilder;
use JsonSerializable;
use Override;

abstract class Model implements JsonSerializable
{
    protected array $attributes = [];

    protected static Database $database;

    public static function setDatabase(Database $database): void
    {
        self::$database = $database;
    }

    public static function find(int $id): ?static
    {
        $model = new static();

        $sql = "SELECT * FROM {$model->table} WHERE id = :id";

        $statement = self::$database
            ->getPdo()
            ->prepare($sql);

        $statement->execute([
            'id' => $id
        ]);

        $data = $statement->fetch();

        if ($data == null) {
            return null;
        }

        $model->fill($data);

        return $model;
    }

    public static function query(): QueryBuilder
    {
        $model = new static();
        return new QueryBuilder(
            static::class,
            $model->getTableName(),
            self::$database
        );
    }

    #[Override()]
    public function jsonSerialize(): mixed
    {
        return $this->attributes;
    }

    protected function getTableName(): string
    {
        $reflection = new \ReflectionClass($this);

        $tableAttr = $reflection->getAttributes(Table::class)[0]->newInstance();
        return $tableAttr->name != null ?
            $tableAttr->name :
            strtolower($reflection->getShortName()) . "s";
    }

    public function fill(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function save(): void
    {
        if ($this->id === null) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    protected function update(): void
    {
        $id = $this->id;
        $tableName = $this->getTableName();
        unset($this->attributes['id']);

        $sets = [];
        foreach (array_keys($this->attributes) as $key) {
            $sets[] = "$key = :$key";
        }

        $sql = "UPDATE $tableName SET ";
        $sql .= implode(", ", $sets);
        $sql .= " WHERE id = :id";

        $statement = self::$database
            ->getPdo()
            ->prepare($sql);

        $this->attributes['id'] = $id;

        $statement->execute($this->attributes);
    }

    protected function insert(): void
    {
        $tableName = $this->getTableName();
        $sql = "INSERT INTO $tableName ";

        $keys = array_keys($this->attributes);
        $sql .= "(" . implode(", ", $keys) . ")";

        $sql .= " VALUES ";
        $params = array_map(fn($key) => ":$key", $keys);
        $sql .= "(" . implode(", ", $params) . ")";

        echo $sql . "<br>";

        $statement = self::$database
            ->getPdo()
            ->prepare($sql);

        $statement->execute($this->attributes);

        $this->attributes['id'] =
            (int) self::$database->getPdo()->lastInsertId();
    }
}
