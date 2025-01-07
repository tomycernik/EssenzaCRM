<?php

class RegistroModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function registrarUsuario($data)
    {
        $query = "INSERT INTO usuario (nombre, username, password) 
                  VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($query);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->bind_param("sss",
            $data['nombre'],
            $data['username'],
            $hashed_password
        );
        $success = $stmt->execute();

        $stmt->close();
        if ($success) {
            return $success;
        } else {
            return false;
        }
    }

    public function registrarCliente($data)
    {
        $query = "INSERT INTO usuario (nombre, dni, celular, provincia, localidad, rol, codigo_postal, direccion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($query);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        $nombre = $data['nombre'];
        $dni = $data['dni'];
        $celular = $data['celular'];
        $provincia = $data['provincia'];
        $localidad = $data['localidad'];
        $rol = "cliente";
        $codigo_postal = $data['codigo_postal'];
        $direccion = $data['direccion'];

        $stmt->bind_param("ssssssis", $nombre, $dni, $celular, $provincia, $localidad, $rol, $codigo_postal, $direccion);
        $success = $stmt->execute();

        if ($success) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }




    public function obtenerClientePorDni($dni)
    {
        $query = "SELECT * FROM usuario WHERE dni = ?";
        $stmt = $this->database->prepare($query);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        $stmt->bind_param("s", $dni);

        $stmt->execute();

        $result = $stmt->get_result();

        $cliente = $result->fetch_assoc();

        $stmt->close();

        return $cliente ? $cliente : false;
    }


}

