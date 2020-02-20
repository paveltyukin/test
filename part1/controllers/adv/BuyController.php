<?php

require_once("Local/Controller/Adv/Action.class.php");
require_once ("Local/Model/Adv/Buy.class.php");

class Adv_BuyController extends Local_Controller_Adv_Action
{
    public function indexAction() {
        $this->view->top_menu = "buy";
    }

    public function successAction() {
        $this->view->top_menu = "buy";
    }

    public function failAction() {
        $this->view->fail = true;
        $this->_forward('index');
    }

    public function sendAction(){
        $form = Local_Model_Adv_Buy::getForm();
        Local_Application::getInstance()->initFormTranslate();

        if ($form->isValid($_POST)) {
            if (Local_Model_Adv_Buy::sendMail($_POST)){
                $this->_redirect('/adv/buy/success/');
            } else {
                $this->_redirect('/adv/buy/fail/');
            }
        } else {
            $errors = $form->getMessages();

            $this->view->error_text = array();

            if (is_array($errors)){
                foreach($errors as $id => $value){
                    $i = array_keys($value);

                    $this->view->error_text[$id] = $value[$i[0]];
                }
            }

            $this->_forward('index');
        }
    }
}


?>