<?php

namespace Kernel\Abstractions;

abstract class AbsDbConnection
{
    protected IEnvEngine $env_engine;
    protected IErrorHandler $error_handler;

    protected string $state;

    public function __construct(IEnvEngine $envEngine, IErrorHandler $errorHandler){
//        $this->on_demand = $on_demand;
        $this->env_engine = $envEngine;
        $this->error_handler = $errorHandler;
        $this->state = 'created';

        $this->connect();
    }

    abstract protected function connect();

/*
    public function disconnect(){
        if($this->state !== 'connected') return;
        $this->connection->close();
    }

    public function commit(){
        if($this->on_demand) return true;

        if (!$this->connection->commit()) {
//            $this->error = $this->connection->connect_error;
            $this->error = 'Transaction commit failed';
            $this->state = 'error';
            return false;
        }

        return true;
    }

    public function begin_transaction(){
        if($this->on_demand) return true;

        if (!$this->connection->begin_transaction()) {
//            $this->error = $this->connection->connect_error;
            $this->error = 'Begin Transaction failed';
            $this->state = 'error';
            return false;
        }

        return true;
    }

    public function rollback(){
        if($this->on_demand) return false;

        if (!$this->connection->rollback()) {
//            $this->error = $this->connection->connect_error;
            $this->error = 'Rollback Transaction failed';
            $this->state = 'error';
            return false;
        }

        return true;
    }

    public function Select(string $table, ?array $fields, $where_clause){
        $content = !empty($fields) ? '`'.implode('`,`', $fields).'`' : '*' ;

        if(is_array($where_clause)) $where_clause = $this->stringifyWhereClause($where_clause);

        $sql = "SELECT $content" .
            " FROM `$table`" .
            " WHERE $where_clause;" ;

        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        else {
            return null;
        }
    }

    public function gSelect(string $table, ?array $fields, $where_clause){
        $content = !empty($fields) ? '`'.implode('`,`', $fields).'`' : '*' ;

        if(is_array($where_clause)) $where_clause = $this->stringifyWhereClause($where_clause);

        $sql = "SELECT $content" .
            " FROM $table" .
            " WHERE $where_clause LIMIT 1;" ;

        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                yield $row;
            }
        }
        else {
            return null;
        }
    }

    private function stringifyWhereClause($where_clause){
        $w_str = [];
        foreach($where_clause as $field => $val){
            $w_str[] = "`$field` = '$val'";
        }
        $w_str = implode(" AND ", $w_str);
        return $w_str;
    }

    public function oneSelect(string $table, ?array $fields, $where_clause){
        $content = !empty($fields) ? '`'.implode('`,`', $fields).'`' : '*' ;

        if(is_array($where_clause)) $where_clause = $this->stringifyWhereClause($where_clause);

        $sql = "SELECT *" .
            " FROM $table" .
            " WHERE $where_clause LIMIT 1;" ;

        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        else {
            return null;
        }
    }

    public function raw(string $sql){
        return $this->connection->query($sql);
    }

    public function drop_table(string $table): ?bool
    {
        $sql = "DROP TABLE IF EXISTS $table;";
        $this->connection->query($sql);
        return true;
    }

    public function create_table(string $table, array $fields): ?bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS $table " .
            " (" . implode(',', $fields) . ");";
        $this->connection->query($sql);
        return true;
    }

    public function insert(string $table, array $fields): ?bool
    {
        $sql = "INSERT INTO `$table`" .
            " (`" . implode('`,`', array_keys($fields)) . "`)" .
            " VALUES ('" . implode("','", array_values($fields)) . "');";

        $this->connection->query($sql);
        return $this->connection->insert_id;
    }

    public function update(string $table, array $fields, $where_clause): ?bool
    {
        $content = [];
        foreach ($fields as $key => $val) {
            $content[] = "`$key` = '$val'";
        }
        $content = implode(',', $content);

        if(is_array($where_clause)) $where_clause = $this->stringifyWhereClause($where_clause);

        $sql = "UPDATE `$table`" .
            " SET $content" .
            " WHERE $where_clause;" ;
        $this->connection->query($sql);
        return true;
    }

    public function insertOrUpdate(string $table, array $fields, $where_clause): ?bool
    {
        $record = $this->oneSelect($table, $fields, $where_clause);
        if(is_null($record)) { //insert
            return $this->insert($table, $fields);
        }
        else{ //update
            return $this->update($table, $fields, $where_clause);
        }
    }

    public function increase(string $table, array $counter_fields, $where_clause): ?bool
    {
        $record = $this->oneSelect($table, $counter_fields, $where_clause);

        if(is_null($record)) { //insert
            return $this->insert($table, array_merge( array_combine($counter_fields, str_split(str_repeat('1',count($counter_fields)))),$where_clause ))  ;
        }
        else{ //update
            $values = [];
            foreach($counter_fields as $field){
                $values[$field] = ((int) $record[$field]) + 1;
            }
            return $this->update($table, $counter_fields, $where_clause);
        }
    }

    public function has_table(string $table): ?bool
    {
        $result = $this->raw("SHOW TABLES LIKE '$table';");
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        else {
            return null;
        }
    }

    public function show_tables_like(string $table): ?string
    {
        $query_string = $table;
        do{
            $result = $this->raw("SHOW TABLES LIKE '$query_string';");
            $query_string .= '_';
        }while($result->num_rows <= 0);

        if ($result->num_rows > 0) {
            $result = $result->fetch_assoc();
            $result = array_values($result)[0];
            return $result;
        }
        else {
            return null;
        }
    }

    public function get_table_columns(string $table): ?array
    {
        $columns = [];
        $result = $this->raw("SHOW COLUMNS FROM `$table`;");
        if ($result->num_rows > 0)
            while($res = $result->fetch_assoc()){
            $columns[$res['Field']] = array_filter($res,function($k) use ($res){
                return $k != $res['Field'];
            });
        }
        else {
            return null;
        }
        return $columns;
    }

*/
}