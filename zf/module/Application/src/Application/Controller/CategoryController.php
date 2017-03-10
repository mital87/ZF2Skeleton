<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;

class CategoryController extends AbstractActionController {

    private $_entity;
    private $_form;
    public $em;

    public function __construct() {
        $this->_entity = new \Application\Entity\Contact();
        $this->_form = new \Application\Form\ContactForm();
    }

    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    public function indexAction() {
        
        $form = new \Application\Form\ContactForm ();
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $data = $form->getData();
                $em = $this->getEntityManager();
                $this->_entity->exchangeArray($data);
                $em->persist($this->_entity);
                $em->flush();
                $this->flashmessenger()->addSuccessMessage("contact added succesfully.");
                return $this->redirect()->toRoute('category');
            }
        }

        $view = new ViewModel(array(
            'contactForm' => $form,
        ));
        return $view;
    }
    
    public function editAction(){
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $request = $this->getRequest();
        $id = $this->params ('id');
        $contactEditObj = $em->getRepository('Application\Entity\Contact')->findOneBy(array("id" => $id));

        $contactForm = new \Application\Form\ContactForm();
        $contactForm->get('name')->setValue($contactEditObj->getName());
        $contactForm->get('contactno')->setValue($contactEditObj->getContactno());
        $contactForm->get('email')->setValue($contactEditObj->getEmail());
        $contactForm->get('post')->setValue($contactEditObj->getPost());
        
        
        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();
            $contactForm->setData($postData);
            if ($contactForm->isValid()) {
                $categoryObj = $this->getEntityManager()->getRepository('Application\Entity\Contact')
                            ->findOneBy(array("id" => $id));
                $categoryObj->setName($postData['name']);
                $categoryObj->setContactno($postData['contactno']);
                $categoryObj->setEmail($postData['email']);
                $categoryObj->setPost($postData['post']);
                $em = $this->getEntityManager();
                $em->persist($categoryObj);
                $em->flush();
                $this->flashmessenger()->addSuccessMessage("contact Edited succesfully.");
                return $this->redirect()->toRoute('category');
            }
            
        }
        
        
        //ajax code
//        echo json_encode(array(
//                    "success" => "Data successfully saved.",
//                    "path" => BASE_URI . $fileName
//                ));
//                exit();
        $view = new ViewModel(array(
            'data' => $contactForm,
        ));
        return $view;
        
    }
    
    
    public function viewAction(){
        $em = $this->getEntityManager();
        $contactEditObj = $em->getRepository('Application\Entity\Contact')->findAll();
         $view = new ViewModel(array(
            'view' => $contactEditObj,
        ));
        return $view;
        
//        $em = $this->getEntityManager ();
//        $string = 'select c.name,c.contactno, u.first_name from contact c join user u on c.id=u.id';
//        $qb = $em->createQueryBuilder ();
//        $connection = $em->getConnection ();
//        $data = $connection->executeQuery ( $string);
//        $data1 = $data->fetchAll();
//        print_r($data1); exit;
        
    }
    
    public function deleteAction(){
        
        $id= $this->params('id');
        $em = $this->getEntityManager();
        $strSql = 'DELETE FROM `contact` WHERE id in ('.$id.')' ;
        $qb = $em->createQueryBuilder ();
        $connection = $em->getConnection ();
        $connection->executeQuery ( $strSql);
        $this->flashmessenger()->addSuccessMessage("Deleted succesfully.");
        return $this->redirect()->toRoute('category');
        
        
//        $strSql = 'UPDATE `application_settings` SET field_value = "'. $nextTime .'" WHERE field_name = "next_bug_cron" ' ;
//        $qb = $em->createQueryBuilder ();
//        $connection = $em->getConnection ();
//        $connection->executeQuery ( $strSql);
        
//        $strSql = 'INSERT INTO `application_settings` (field_name, field_value) VALUES("next_bug_cron","'. time() .'" ) ' ;
//        $qb = $em->createQueryBuilder ();
//        $connection = $em->getConnection ();
//        $connection->executeQuery ( $strSql);
        
        
//        $em = $this->getEntityManager ();
//        $email_queue_query = new QueryBuilder ( $em );
//        $email_queue_query->from ( "email_queue", "eq" );
//        $email_queue_query->select ( '*' )->where ( 'sent = 0 AND cron_queue = 0 ORDER BY entity_id ASC LIMIT 5' );
//        $connection = $em->getConnection ();
//        $collection = $connection->executeQuery ( $email_queue_query->getDQL () )->fetchAll ();
        
        
    }
//        public function indexAction() {
//        $authService = new AuthenticationService();
////        if ($authService->hasIdentity()) {
////            return $this->redirect()->toRoute('home', array("action" => "admin"));
////        }
//
//        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
//        $this->_form->setDbAdapter($dbAdapter);
//        $request = $this->getRequest();
//
//        try {
//            $sessionobj = new Container('Zend_Auth');
//
//            if ($request->isPost()) {
//
//                $data = array_map("strip_tags", $request->getPost()->toArray());
//                $data['image'] = (isset($sessionobj->img) && file_exists(ROOT_DIR . '/asset/image/' . $sessionobj->img)) ? $sessionobj->img : "";
//
//                $file = $this->params()->fromFiles('image');
//                if ($file['name'] != "") {
//                    $data['image'] = $file['name'];
//                }
//                $this->_form->setData($data);
//                if ($this->_form->isValid()) {
//                    $data = $this->_form->getData();
//                    $upload = true;
////                    if ($file['name'] != "") {
////                        $upload = $this->uploadFile($file, $data);
////                    }
//
//                    if ($upload == true) {
//                        $em = $this->getEntityManager();
//                        $this->_entity->exchangeArray($data);
//                        $em->persist($this->_entity);
//                        $em->flush();
//                        $this->flashmessenger()->addSuccessMessage("You rigistered Successfully.");
//                        return $this->redirect()->toRoute('home');
//                    }
//                }
//            }
//
//            $sessionobj->img = "";
//        } catch (\Exception $e) {
//            die($e->getMessage());
//        }
//        $this->_form->get("confirm_password")->setValue("");
//        $this->_form->get("password")->setValue("");
//        $view = new ViewModel(array(
//            'registerForm' => $this->_form,
//        ));
//        return $view;
//    }
//    
    
 

}
