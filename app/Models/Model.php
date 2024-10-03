<?php

namespace App\Models;

use Dotenv\Dotenv;
use Exception;
use mysqli;

Dotenv::createImmutable('../')->load();

abstract class Model
{
    protected mysqli $conn;
    public $table;
    public $query;

    public function __construct()
    {
        $this->conn = $this->connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
    }

    /**
     * Connect to database
     * @param string $db_host
     * @param string $db_user
     * @param string $db_pass
     * @param string $db_name
     * @return mysqli
     * @throws Exception
     */
    public function connect(string $db_host, string $db_user, string $db_pass, string $db_name): mysqli
    {
        try {
            $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
            if ($conn->connect_error) {
                throw new Exception('Connection failed: ' . $conn->connect_error);
            }
            return $conn;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * run query
     * @param string $sql
     * @return object
     */
    public function query(string $sql): object
    {
        $this->query = $this->conn->query($sql);
        return $this;
    }

    public function all(): array
    {
        $sql = "SELECT * FROM $this->table";
        return $this->query($sql)->get();
    }

    /**
     * Get first row
     *
     *
     * @return array
     */
    public function first(): array|null
    {
        return $this->query->fetch_assoc();
    }

    /**
     * Get all rows
     *
     * @return array | object
     */
    public function get(): array|object
    {
        return $this->query->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Prepare query
     * @param string $sql
     * @param array $params
     * @return object
     */
    public function prepare($sql, $params = []): object
    {
        $stmt = $this->conn->prepare($sql);

        if ($params) {
            // Inicializamos la cadena para los tipos de parámetros
            $types = '';
            $values = [];

            // Iteramos sobre el array de parámetros
            foreach ($params as $type => $value) {
                $types .= $type;  // Construimos la cadena de tipos
                $values[] = $value;  // Guardamos los valores en un arreglo
            }

            // Llama a bind_param con los tipos y los valores separados
            $stmt->bind_param($types, ...$values);
        }

        $stmt->execute();
        $this->query = $stmt->get_result();
        return $this;
    }

    /**
     * Get single row
     * @param string $id
     * @return object|array
     */
    public function find(string $id): object|array
    {
        $sql = "SELECT * FROM $this->table WHERE id = ?";
        $params = ['i' => $id];
        return $this->prepare($sql, $params)->first();
    }

    /**
     * Close connection
     * @return void
     */
    public function __destruct()
    {
        $this->conn->close();
    }
}
