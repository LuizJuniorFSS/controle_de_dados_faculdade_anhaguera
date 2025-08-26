<?php
require_once __DIR__ . '/config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $conn;
    private $error;
    private $stmt;
    
    public function __construct() {
        // Configurar DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        
        // Configurar opções do PDO
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );
        
        // Criar instância do PDO
        try {
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            echo 'Erro de conexão: ' . $this->error;
        }
    }
    
    // Preparar statement com query
    public function query($sql, $params = []) {
        $this->stmt = $this->conn->prepare($sql);
        
        // Bind parameters if provided
        if (!empty($params)) {
            $i = 1;
            foreach ($params as $param) {
                $this->stmt->bindValue($i++, $param);
            }
        }
        
        $this->execute();
        return $this->stmt;
    }
    
    // Bind values
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    // Executar statement
    public function execute() {
        return $this->stmt->execute();
    }
    
    // Obter resultados como array de objetos
    public function resultSet() {
        // Execute já foi chamado no método query
        return $this->stmt->fetchAll();
    }
    
    // Obter um único registro
    public function single() {
        // Execute já foi chamado no método query
        return $this->stmt->fetch();
    }
    
    // Obter contagem de linhas
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    // Obter último ID inserido
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    // Iniciar transação
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    // Commit transação
    public function commit() {
        return $this->conn->commit();
    }
    
    // Rollback transação
    public function rollback() {
        return $this->conn->rollBack();
    }
}
?>