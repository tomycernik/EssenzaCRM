<?php
include_once("controller/RegistroController.php");
include_once ("controller/UserController.php");
include_once ("controller/AdminController.php");
include_once ("controller/LoginController.php");
include_once ("controller/HomeController.php");
include_once ("controller/PedidoController.php");
include_once ("controller/ClienteController.php");


include_once("model/RegistroModel.php");
include_once ("model/UserModel.php");
include_once ("model/AdminModel.php");
include_once ("model/LoginModel.php");
include_once ("model/PedidoModel.php");
include_once ("model/ClienteModel.php");

include_once ("helper/Database.php");
include_once ("helper/Router.php");

include_once ("helper/Presenter.php");
include_once ("helper/MustachePresenter.php");
include_once ("helper/SessionManager.php");
include_once ("helper/Redirect.php");



include_once('vendor/mustache/src/Mustache/Autoloader.php');

class Configuration
{
    const SESSION_TIMEOUT = 10 ;

    // CONTROLLERS
    public static function getLoginController()
    {
        return new LoginController(self::getHomeController(), self::getAdminController(), self::getLoginModel(), self::getSessionManager(), self::getPresenter());
    }

    public static function getHomeController()
    {
        return new HomeController(self::getAdminController(), self::getPresenter());
    }


    public static function getRegistroController()
    {
        return new RegistroController(self::getRegistroModel(), self::getPedidoModel(), self::getPresenter());
    }

    public static function getUserController(){
        return new UserController(self::getUserModel(), self::getPresenter(), self::getSessionManager());
    }


    public static function getAdminController(){
        return new AdminController(self::getAdminModel(), self::getPresenter());
    }

    public static function getPedidoController(){
        return new PedidoController(self::getPedidoModel(), self::getPresenter(), self::getSessionManager());
    }

    public static function getClienteController(){
        return new ClienteController(self::getClienteModel(), self::getPedidoModel(), self::getPresenter());
    }


    // MODELS
    private static function getLoginModel()
    {
        return new LoginModel(self::getDatabase());
    }

    private static function getRegistroModel()
    {
        return new RegistroModel(self::getDatabase());
    }
    private static function getUserModel(){
        return new UserModel(self::getDatabase());
    }


    private static function getAdminModel(){
        return new AdminModel(self::getDatabase());
    }

    private static function getPedidoModel(){
        return new PedidoModel(self::getDatabase());
    }

    private static function getClienteModel(){
        return new ClienteModel(self::getDatabase());
    }


    // HELPERS
    public static function getDatabase()
    {
        $config = self::getConfig();
        return new Database($config["servername"], $config["username"], $config["password"], $config["dbname"], $config["port"]);
    }

    private static function getConfig()
    {
        $configFile = file_exists("config/macConfig.ini") ? "config/macConfig.ini" : "config/config.ini";
        return parse_ini_file($configFile);
    }

    public static function getRouter()
    {
        return new Router("getLoginController", "get");
    }

    public static function getPresenter()
    {
        return new MustachePresenter("view/template");
    }

    public static function getSessionManager()
    {
        return new SessionManager(self::SESSION_TIMEOUT * 60, self::getPresenter()); // Convertir a segundos
    }
}