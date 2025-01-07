<?php

include_once("Configuration.php");
include_once("helper/SessionManager.php");

$router = Configuration::getRouter();
$presenter = Configuration::getPresenter();
$timeout = Configuration::SESSION_TIMEOUT * 60;

$sessionManager = new SessionManager($timeout, $presenter);

$controller = $_GET["controller"] ?? "";
$action = $_GET["action"] ?? "";

$authConfig = parse_ini_file("config/auth.ini", true);
$role = 'guest'; // Default role
if (isset($_SESSION['rol'])) {
    $role = $_SESSION['rol'];
}

// Get allowed controllers and actions for the role
$allowedControllers = $authConfig[$role]['controllers'] ?? [];
$allowedActions = $authConfig[$role]['actions'] ?? [];

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {

    if (!in_array($controller, $allowedControllers) || !in_array($action, $allowedActions)) {

        $controller = "login";
        $action = "login";
    }
} else {
    // If the user is logged in, allow access based on their role
    if (!in_array($controller, $allowedControllers) || !in_array($action, $allowedActions)) {
        // If the requested controller or action is not allowed, redirect to a default page
        $controller = "home";
        $action = "";
    }
}

$router->route($controller, $action);

