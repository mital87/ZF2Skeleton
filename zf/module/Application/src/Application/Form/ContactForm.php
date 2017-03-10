<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter,
    Zend\InputFilter\Factory as InputFactory,
    Zend\InputFilter\InputFilterAwareInterface,
    Zend\InputFilter\InputFilterInterface;
use Zend\Form\Element\Csrf as CsrfElement,
    Zend\Validator\Csrf as CsrfValidator;

class ContactForm extends Form {
      
    protected $inputFilter;
    protected $csrf;

    public function __construct($name = null) {
        $name = $name == null ? "contact" : $name;
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type' => 'text',
                'id' => 'name',
                'class' => 'form-control',
            )
        ));

        $this->add(array(
            'name' => 'contactno',
            'attributes' => array(
                'type' => 'text',
                'id' => 'contactno',
                'class' => 'form-control',
            )
        ));
        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
                'id' => 'email',
                'class' => 'form-control',
            )
        ));
        
        $this->add(array(
            'name' => 'post',
            'type' => 'select',
            'attributes' => array(
                'id' => 'select',
                'class' => 'form-control',
            ),
            'options' => array(
                'value_options' => array(
                    '0' => '-- Select Post--',
                    '1' => 'Manager',
                    '2' => 'Sr.Develoepr',
                    '3' => 'Jr.Developer'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Submit',
                'id' => 'submitbtn',
                'class' => "btnLogin"
            )
        ));
    }
    
}

