<?php

class Admin_LoginController extends Admin_Controller_Action
{

    public function init()
    {

    }

    public function indexAction()
    {

        $form = new Admin_Form_Login();

        $this->view->form = $form;


    }

} 