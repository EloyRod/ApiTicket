<?php

class Admin_Form_Login extends Zend_Form
{

    public function init()
    {

        $this->addElement('text', 'user');
        $this->getElement('user')
            ->addValidator('notEmpty', true, array('messages' => array(
                Zend_Validate_NotEmpty::IS_EMPTY => 'CAMPO_OBLIGATORIO'
            )))
            ->setLabel('USUARIO')
            ->setRequired(true);

        $this->addElement('password', 'password');
        $this->getElement('password')
            ->addValidator('notEmpty', true, array('messages' => array(
                Zend_Validate_NotEmpty::IS_EMPTY => 'CAMPO_OBLIGATORIO'
            )))
            ->setLabel('PASSWORD')
            ->setRequired(true);

        $this->addElement('submit', 'submit');
        $this->getElement('submit')
            ->setLabel('ENVIAR');

    }


}