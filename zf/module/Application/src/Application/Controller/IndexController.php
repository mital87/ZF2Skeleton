<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;

class IndexController extends AbstractActionController {

    private $_entity;
    private $_form;
    public $em;

    public function __construct() {
        $this->_entity = new \Application\Entity\User();
        $this->_form = new \Application\Form\UserRegisterForm ();
    }

    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    public function indexAction() {
        $authService = new AuthenticationService();
        if ($authService->hasIdentity()) {
            return $this->redirect()->toRoute('home', array("action" => "admin"));
        }
        $form = new \Application\Form\LoginForm ();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $data = $form->getData();

                $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
                $adapter = $authService->getAdapter();
                $adapter->setIdentityValue($data ['email']);
                $adapter->setCredentialValue($data ['password']);
                $authResult = $authService->authenticate();
                print_r($authResult);
                if ($authResult->isValid()) {
                    $sessionobj = new Container('Zend_Auth');
                    $sessionobj->setExpirationSeconds(60 * 60);
                    $identity = $authResult->getIdentity();
                    $authService->getStorage()->write($identity);
                    return $this->redirect()->toRoute('home', array("action" => "admin"));
                }
                $this->flashMessenger()->addErrorMessage("Invalid credentials");
                 return $this->redirect()->toRoute('home');
            }
        }

        $view = new ViewModel(array(
            'loginForm' => $form,
        ));
        return $view;
    }

    /**
     * Register New User
     * 
     * @return ViewModel
     */
    public function registerAction() {
        $authService = new AuthenticationService();
        if ($authService->hasIdentity()) {
            return $this->redirect()->toRoute('home', array("action" => "admin"));
        }

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->_form->setDbAdapter($dbAdapter);
        $request = $this->getRequest();

        try {
            $sessionobj = new Container('Zend_Auth');

            if ($request->isPost()) {

                $data = array_map("strip_tags", $request->getPost()->toArray());
                $data['image'] = (isset($sessionobj->img) && file_exists(ROOT_DIR . '/asset/image/' . $sessionobj->img)) ? $sessionobj->img : "";

                $file = $this->params()->fromFiles('image');
                if ($file['name'] != "") {
                    $data['image'] = $file['name'];
                }
                $this->_form->setData($data);
                if ($this->_form->isValid()) {
                    $data = $this->_form->getData();
                    $upload = true;
                    if ($file['name'] != "") {
                        $upload = $this->uploadFile($file, $data);
                    }

                    if ($upload == true) {
                        $em = $this->getEntityManager();
                        $this->_entity->exchangeArray($data);
                        $em->persist($this->_entity);
                        $em->flush();
                        $this->flashmessenger()->addSuccessMessage("You rigistered Successfully.");
                        return $this->redirect()->toRoute('home');
                    }
                }
            }

            $sessionobj->img = "";
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->_form->get("confirm_password")->setValue("");
        $this->_form->get("password")->setValue("");
        $view = new ViewModel(array(
            'registerForm' => $this->_form,
        ));
        return $view;
    }

    /**
     * Admin
     * 
     * @return ViewModel
     */
    public function adminAction() {
        $authService = new AuthenticationService();
        if (!$authService->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }
        $authobj = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService")->getIdentity();
        $view = new ViewModel(array(
            'user' => $authobj,
        ));
        return $view;
    }

    /**
     * LogOut Action
     * 
     * @return type
     */
    public function logoutAction() {
        $sessionAuth = new Container('Zend_Auth');
        $sessionAuth->getManager()->getStorage()->clear();
        $authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        $authService->clearIdentity();
        return $this->redirect()->toRoute('home');
    }

    private function uploadFile($file, &$data) {

        $size = new \Zend\Validator\File\Size(array('max' => '2MB'));
        $ext = new \Zend\Validator\File\Extension(array('png', 'jpg', 'jpeg'));

        $fileValidator = new \Application\Validators\FileValidator();
        try {
            $fileValidator->setValidators(array($size, $ext), $file['name']);
            if (!$fileValidator->isValid()) {
                $dataError = $fileValidator->getMessages();
                $error = array();
                foreach ($dataError as $key => $row) {
                    $error[] = $row;
                }
                $this->_form->setMessages(array('image' => $error));
                return false;
            }
            $fileValidator->setDestination(ROOT_DIR . '/asset/image');
            if ($fileValidator->receive()) {
                $path = $fileValidator->getDestination();
                $fileName = $fileValidator->getFileName();
                $name = $fileValidator->getFileName(null, false);
                $data['image'] = $name;
                return true;
            } else {
                throw new \Exception("Please try after some time.");
            }
        } catch (\Exception $ex) {
            $this->_form->setMessages(array('image' => array($ex->getMessage())));
            return false;
        }
    }

    /**
     * upload file using camera
     */
    public function uploadAction() {
        $request = $this->getRequest();

        try {
            if ($request->isPost()) {
                $file = $this->params()->fromFiles('webcam');
                $data = array();
                if ($this->uploadFile($file, $data) == true) {
                    $sessionobj = new Container('Zend_Auth');
                    $sessionobj->img = $data['image'];
                    $data['success'] = "true";
                    echo json_encode($data);
                } else {
                    echo json_encode(array("error" => "try after some time"));
                }
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        exit;
    }
    
    
    public function addAction(){
        
        
    }

}
