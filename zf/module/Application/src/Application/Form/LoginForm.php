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

class LoginForm extends Form {

    protected $inputFilter;
    protected $csrf;

    public function __construct($name = null) {
        $name = $name == null ? "user" : $name;
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
                'id' => 'email',
                'class' => 'form-control',
                'required' => 'required',
                'autofocus' => 'autofocus'
            )
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'id' => 'password',
                'class' => 'form-control',
                'required' => 'required',
                'autocomplete' => 'off',
                'autocorrect' => 'off',
            )
        ));


        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Login',
                'id' => 'loginButton',
                'class' => "btnLogin"
            )
        ));
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                        'name' => 'password',
                        'filters' => array(
                            array('name' => 'StringTrim'),
                            array('name' => 'StripTags'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'NotEmpty',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'messages' => array(
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter password'
                                    )
                                ),
                            ),
                        )
            )));

            $inputFilter->add($factory->createInput(array(
                        'name' => 'email',
                        'filters' => array(
                            array('name' => 'StringTrim'),
                            array('name' => 'StripTags'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'NotEmpty',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'messages' => array(
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter email'
                                    )
                                ),
                            ),
                        )
            )));


            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

}
