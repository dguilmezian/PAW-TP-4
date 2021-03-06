<?php

namespace App\Core\Database;

use PDO;
use Exception;

class QueryBuilder
{
    /**
     * The PDO instance.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Create a new QueryBuilder instance.
     *
     * @param PDO $pdo
     */
    public function __construct($pdo, $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = ($logger) ? $logger : null;
    }

    /**
     * Select all records from a database table.
     *
     * @param string $table
     */
    public function selectAll($table)
    {
        $statement = $this->pdo->prepare("select * from {$table}");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_CLASS);
    }

    /**
     * Insert a record into a table.
     *
     * @param  string $table
     * @param  array $parameters
     */
    public function insert($table, $parameters)
    {
        $parameters = $this->cleanParameterName($parameters);
        $sql = sprintf(
            'insert into %s (%s) values (%s)',
            $table,
            implode(', ', array_keys($parameters)),
            ':' . implode(', :', array_keys($parameters))
        );
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
        } catch (Exception $e) {
            $this->sendToLog($e);
        }
    }

    public function update($table, $parameters, $parametro, $valor)
    {
        //uso parametro y valor para el where
        $parameters = $this->cleanParameterName($parameters);
        foreach ($parameters as $column => $value) {
            $sql = sprintf(
                'update %s set %s = "%s" where (%s)  = (%s)',
            $table,
            $column,
            $value,
            $parametro,
            $valor);
            try{
                $statement=$this->pdo->prepare($sql);
                $statement->execute();
            }catch (Exception $e){
                echo $sql;
                echo $e;
            }
        }
        /*$sql = sprintf(
            'update %s  set %s = %s where (%s)=(%s)',
            $table,
            implode(', ', array_keys($parameters)),
            '=' . implode(', :', array_keys($parameters)),
            $parametro,
            $valor
        );
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
        } catch (Exception $e) {
            echo $sql;
            echo $e;
        }*/
    }


    public function delete($table, $parametro, $valor)
    {

        $sql = sprintf('delete from %s where (%s)=(%s)',
            $table,
            $parametro,
            $valor);

        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute();
        } catch (Exception $e) {
            echo $e;
        }
    }

    private function sendToLog(Exception $e)
    {
        if ($this->logger) {
            $this->logger->error('Error', ["Error" => $e]);
        }
    }

    /**
     * Limpia guiones - que puedan venir en los nombre de los parametros
     * ya que esto no funciona con PDO
     *
     * Ver: http://php.net/manual/en/pdo.prepared-statements.php#97162
     */
    private function cleanParameterName($parameters)
    {
        $cleaned_params = [];
        foreach ($parameters as $name => $value) {
            $cleaned_params[str_replace('-', '', $name)] = $value;
        }
        return $cleaned_params;
    }
}
