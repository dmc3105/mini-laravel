<?php

namespace Minilaravel\ORM;

class QueryBuilder
{
    private string $table;
    private string $modelClass;
    private Database $database;

    private array $wheres = [];
    private array $bindings = [];
    private ?string $orderBy = null;
    private ?int $limit = null;

    public function __construct(
        string $modelClass,
        string $table,
        Database $database,
    )
    {
        $this->modelClass = $modelClass;
        $this->table = $table;
        $this->database = $database;
    }

    public function where(
        string $attribute,
        string $operator,
        mixed $value
    ) : self {
        $parameter_number = count($this->bindings) + 1;
        $parameter = 'param_' . $parameter_number;

        $this->wheres[] = "$attribute $operator :$parameter";

        $this->bindings[$parameter] = $value;

        return $this;
    }

    public function get() : array {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($this->wheres) {
            $sql .= " WHERE " . implode(" AND ", $this->wheres);
        }
        
        if ($this->orderBy) {
            $sql .= " ORDER BY $this->orderBy";
        }

        if ($this->limit) {
            $sql .= " LIMIT $this->limit";
        }
        
        $statement = $this->database
            ->getPdo()
            ->prepare($sql);

        $statement->execute($this->bindings);

        $rows = $statement->fetchAll();

        $models = [];
        foreach ($rows as $row) {
            $model = new $this->modelClass();

            $model->fill($row);

            $models[] = $model;
        }

        return $models;
    }

    public function limit(int $lim) : self {
        $this->limit = $lim;
        return $this;
    }

    public function orderBy(string $orderBy) : self {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function first() : ?object {
        $this->limit(1);

        $models = $this->get();

        return $models[0] ?? null;
    }
}
    