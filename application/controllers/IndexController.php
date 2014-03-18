<?php

class IndexController extends My_Controller_Action
{

    public function init()
    {
        $this->_helper->json("Error 404! Ruta no encontrada");
    }   
}

