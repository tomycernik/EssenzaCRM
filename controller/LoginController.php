<?php

class LoginController
{
    private $homeController;
    private $adminController;
    private $loginModel;
    private $presenter;


    public function __construct($homeController, $adminController, $loginModel, $sessionManager, $presenter)
    {
        $this->homeController = $homeController;
        $this->adminController = $adminController;
        $this->loginModel = $loginModel;
        $this->sessionManager = $sessionManager;
        $this->presenter = $presenter;
    }

    public function get() {
        $data = [];
        $this->presenter->render("login", $data);
    }

    public function autenticar() {
        if (isset($_POST["username"], $_POST["password"])) {
            $formData = $_POST;
            $result = $this->verificarUsuario($formData);

            if ($result !== false) {
                $this->sessionManager->setUser($result['id'], $result['rol']);
                $this->mostrarLoginCorrecto($_POST["username"]);
            } else {
                $this->mostrarLoginError("Usuario y/o contraseña inválidos. Intente nuevamente");
            }
        } else {
            $data["message"] = "Faltó completar uno o más campos. Por favor, intente nuevamente.";
            $data["showMessage"] = true;
            $this->presenter->render("login", $data);
        }
    }

    public function verificarUsuario($formData) {
        $username = $formData["username"];
        $password = $formData["password"];

        return $this->loginModel->validarLogin($username, $password);
    }

    private function mostrarLoginCorrecto($username) {
        $isAdmin = $_SESSION['isAdmin'];
        $isVendedor = $_SESSION['isVendedor'];
        $_SESSION['username'] = $username;

        if ($isAdmin) {
            $this->adminController->homeAdmin();
        }elseif ($isVendedor){
            $this->homeController->get();
        }
    }

    private function mostrarLoginError($message) {
        $data["message"] = $message;
        $data['showMessage'] = true;
        $this->presenter->render("login", $data);
    }
}

