<?php

class HomeController
{
    private $adminController;
    private $presenter;


    public function __construct($adminController, $presenter)
    {
        $this->adminController = $adminController;
        $this->presenter = $presenter;
    }
    public function get()
    {
        $usuarioId = $_SESSION['userID'];
        $isAdmin = $_SESSION['isAdmin'];


        if ($isAdmin) {
            $this->adminController->homeAdmin();

        } else {
            $data = [
                'isAdmin' => false,
                'userID' => $usuarioId
            ];
            $this->presenter->render('home', $data);
        }
    }
}