<?php
class Admin_AuthController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        
        if (Local_Application::getInstance()->getAuth()->hasIdentity()) {
            $this->_redirect("admin/");
        }         
    }
    
    public function loginAction(){
        if ((!empty($_POST["form_login"])) && (!empty($_POST["form_pass"]))) {
            
            $auth_result = Local_Application::getInstance()->authenticate($_POST["form_login"], $_POST["form_pass"]);
            
            if ($auth_result->getCode() == Zend_Auth_Result::SUCCESS) {
                 $this->_redirect("admin/");
            }
            else{
                $this->view->wrong_pass = "false";
                $this->_forward("index");
            }            
        } else {
            $this->_forward("index");
        }
    }
}

?>