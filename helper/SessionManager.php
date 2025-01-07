<?php

class SessionManager {
    private $timeout;
    private $presenter;

    public function __construct($timeout, $presenter) {
        $this->timeout = $timeout;
        $this->presenter = $presenter;
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        } else {
            $this->checkSessionTimeout();
        }
    }

    private function checkSessionTimeout() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $this->timeout) {
            $this->destroy();
            $this->presenter->render('login');
            exit();
        }
        $_SESSION['last_activity'] = time();
    }

    public function setUser($userID, $rol) {
        $_SESSION['userID'] = $userID;
        $_SESSION['rol'] = $rol;
        $_SESSION['isAdmin'] = ($rol === 'admin');
        $_SESSION['isVendedor'] = ($rol === 'vendedor');
        $_SESSION['isEditor'] = ($rol === 'editor');
        $_SESSION['isUser'] = ($rol === 'user');
        $_SESSION['last_activity'] = time();
    }

    public function destroy() {
        session_unset();
        session_destroy();
    }
}



