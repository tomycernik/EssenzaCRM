<?php

class Presenter {

    public function __construct()
    {
    }

    public function render($view, $data = [], $showFooter = true)
    {
        include_once("view/template/header.mustache");
        include_once($view);
        if ($showFooter) {
            include_once("view/template/footer.mustache");
        }
    }
}