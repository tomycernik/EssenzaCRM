<?php


class UserController
{
    private $userModel;
    private $presenter;
    private $sessionManager;

    public function __construct($userModel, $presenter, $sessionManager) {
        $this->userModel = $userModel;
        $this->presenter = $presenter;
        $this->sessionManager = $sessionManager;
    }

    public function get() {
        $data = [];
        $this->presenter->render("home", $data);
    }

    public function getUserProfile() {
        if (!isset($_GET['id'])) {

            $this->renderProfileError("El ID del usuario no está presente");
            return;
        }

        $userId = $_GET['id'];
        $user = $this->userModel->getUserById($userId);
        $stats = $this->userModel->getUserQuestionStats($userId);
        $username = $user['username'];

        if (!$user) {

            $this->renderProfileError("El usuario con ID $userId no existe");
            return;
        }

        $userPosition = $this->getUserPosition();

        $user['position'] = $userPosition;

        $qrImagePath = $this->qrCreator->createQr($userId, $username);

        // Pasar los datos del usuario a la vista
        $data = ['user' => $user, 'qr' => $qrImagePath,
            'correct' => $stats['correct'],
            'incorrect' => $stats['incorrect']];
        $this->presenter->render("profileOtherUser", $data);
    }

    public function renderProfileError($message){
        $data["message"] = $message;
        $data['showMessage'] = true;
        $this->presenter->render("ranking", $data);
    }

    public function exit() {
        $this->sessionManager->destroy();
        $this->presenter->render("login");
        exit();
    }

}