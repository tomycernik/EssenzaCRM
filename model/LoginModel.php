<?php

class LoginModel
{
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function validarLogin($username, $password)
    {
        $query = "SELECT id, rol, password FROM usuario WHERE username = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $id = null;
        $rol = null;
        $hashedPassword = null;
        $stmt->bind_result($id, $rol, $hashedPassword);
        $stmt->fetch();
        $stmt->close();

        if ($id && password_verify($password, $hashedPassword)) {
            return ['id' => $id, 'rol' => $rol];
        } else {
            return false;
        }
    }

}