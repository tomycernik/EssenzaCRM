<?php

class MustachePresenter{
    private $mustache;
    private $partialsPathLoader;

    public function __construct($partialsPathLoader){
        Mustache_Autoloader::register();
        $this->mustache = new Mustache_Engine(
            array(
            'partials_loader' => new Mustache_Loader_FilesystemLoader( $partialsPathLoader )
        ));
        $this->partialsPathLoader = $partialsPathLoader;
    }

    public function render($contentFile , $data = array() ){
        $data['rol']= isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
        $data['userID']= isset($_SESSION['userID']) ? $_SESSION['userID'] : null;
        $data['isAdmin'] = isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : false;
        $data['isVendedor'] = isset($_SESSION['isVendedor']) ? $_SESSION['isVendedor'] : false;
        $data['isEditor'] = isset($_SESSION['isEditor']) ? $_SESSION['isEditor'] : false;
        $data['isUser'] = isset($_SESSION['isUser']) ? $_SESSION['isUser'] : false;
        $data['username'] = isset($_SESSION['username']) ? $_SESSION['username'] : null;
        echo  $this->generateHtml($contentFile, $data);
    }

    public function generateHtml($contentFile, $data = array()) {
        $contentAsString = file_get_contents(  $this->partialsPathLoader .'/header.mustache');
        $contentAsString .= file_get_contents('view/' . $contentFile . 'View.mustache');
        $contentAsString .= file_get_contents($this->partialsPathLoader . '/footer.mustache');
        return $this->mustache->render($contentAsString, $data);
    }
}