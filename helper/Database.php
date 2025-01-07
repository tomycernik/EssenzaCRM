<?php

class Database {
    private $conn;

    public function __construct($servername, $username, $password, $dbname, $port)
    {
        $this->conn = new mysqli($servername, $username, $password, $dbname, $port);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function query($sql)
    {
        $result = $this->conn->query($sql);

        if ($result === false) {
            die("Query failed: " . $this->conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function execute($sql)
    {
        if ($this->conn->query($sql) === false) {
            die("Execute failed: " . $this->conn->error);
        }

        return true;
    }

    public function prepare($sql)
    {
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            die("Prepare failed: " . $this->conn->error);
        }

        return $stmt;
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}
