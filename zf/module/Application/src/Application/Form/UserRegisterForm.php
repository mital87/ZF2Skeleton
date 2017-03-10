<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\InputFilter\InputFilter,
    Zend\InputFilter\Factory as InputFactory,
    Zend\InputFilter\InputFilterAwareInterface,
    Zend\InputFilter\InputFilterInterface;
use Zend\Form\Element\Csrf as CsrfElement,
    Zend\Validator\Csrf as CsrfValidator;

class UserRegisterForm extends Form implements InputFilterAwareInterface {

    protected $inputFilter;
    protected $dbAdapter;
    protected $csrf;

    public function __construct($name = null) {
        $name = $name == null ? "user_register" : $name;
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');


        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'class' => 'form-control',
                'required' => 'required',
                'id' => 'password',
                'autocomplete' => 'off',
                'autocorrect' => 'off'
            ),
        ));

        $this->add(array(
            'name' => 'confirm_password',
            'attributes' => array(
                'type' => 'password',
                'class' => 'form-control',
                'required' => 'required',
                'id' => 'confirm_password',
                'autocomplete' => 'off',
                'autocorrect' => 'off'
            )
        ));

        $this->add(array(
            'name' => 'first_name',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control',
                'id' => 'first_name',
                'required' => 'required',
            )
        ));


        $this->add(array(
            'name' => 'last_name',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control',
                'id' => 'last_name',
                'required' => 'required',
            )
        ));

        $this->add(array(
            'name' => 'birth_date',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control',
                'id' => 'birth_date',
                'required' => 'required',
            )
        ));

        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
                'class' => 'form-control',
                'id' => 'email',
                'required' => 'required',
            )
        ));


        $this->add(array(
            'name' => 'gender',
            'type' => "select",
            'attributes' => array(
                'id' => 'gender',
                'required' => 'required',
            ),
            'options' => array(
                'label' => false,
                'value_options' => array('Male', 'Female')
            )
        ));

        $this->add(array(
            'name' => 'image',
            'attributes' => array(
                'type' => 'file',
                'id' => 'image',
                'accept'=>"image/*",
                'capture'=>'camera'
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Register',
                'id' => 'register',
                'class' => "button"
            )
        ));
    }

    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function setDbAdapter($dbAdapter) {
        $this->dbAdapter = $dbAdapter;
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                        'name' => 'image',
                        'required' => true,
                        'validators' => array(
                            array(
                                'name' => 'NotEmpty',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'messages' => array(
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select image'
                                    )
                                ),
                            ),
                        )
                    ))
            );

            $inputFilter->add($factory->createInput(array(
                        'name' => 'birth_date',
                        'required' => true,
                        'validators' => array(
                            array(
                                'name' => 'NotEmpty',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'messages' => array(
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please Birth Date'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'Callback',
                                'options' => array(
                                    'messages' => array(
                                        \Zend\Validator\Callback::INVALID_VALUE => 'Birth date should not be gratter than today',
                                    ),
                                    'callback' => function($value, $context = array()) {
                                        $value = date('Y-m-d', strtotime($value));
                                        $curDate = date("Y-m-d");
                                        return $value <= $curDate;
                                    },
                                ),
                            ),
                        )
                    ))
            );

            $inputFilter->add($factory->createInput(array(
                        'name' => 'gender',
                        'required' => true,
                        'validators' => array(
                            array(
                                'name' => 'InArray',
                                'options' => array(
                                    'haystack' => array(0, 1),
                                    'messages' => array(
                                        'notInArray' => 'Please select valid encryption'
                                    ),
                                ),
                            )
                        )
            )));

            $inputFilter->add($factory->createInput(array(
                        'name' => 'first_name',
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
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'First Name cannot be empty.'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'Regex',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'pattern' => '/^[a-zA-Z\s]+$/',
                                    'messages' => array(
                                        \Zend\Validator\Regex::NOT_MATCH => 'Please use only alphabetic characters or spaces in first name.'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'StringLength',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'max' => 50,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_LONG => 'First Name must be maximum of 50 characters.'
                                    )
                                ),
                            )
                        )
            )));



            $inputFilter->add($factory->createInput(array(
                        'name' => 'last_name',
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
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Last Name cannot be empty.'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'Regex',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'pattern' => '/^[a-zA-Z\s]+$/',
                                    'messages' => array(
                                        \Zend\Validator\Regex::NOT_MATCH => 'Please use only alphabetic characters or spaces in Last name.'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'StringLength',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'max' => 50,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_LONG => 'Last Name must be maximum of 50 characters.'
                                    )
                                ),
                            )
                        )
            )));


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
                            array(
                                'name' => 'StringLength',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'min' => 6,
                                    'max' => 100,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_SHORT => 'Password is too short, use a minimum of 6 characters.',
                                        \Zend\Validator\StringLength::TOO_LONG => 'Password is too long, use a maximum of 100 characters.'
                                    )
                                ),
                            ),
                        )
            )));

            $inputFilter->add($factory->createInput(array(
                        'name' => 'confirm_password',
                        'filters' => array(
                            array('name' => 'StringTrim'),
                            array('name' => 'StripTags'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'Identical',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'token' => 'password',
                                    'messages' => array(
                                        \Zend\Validator\Identical::NOT_SAME => 'Password and Confirm password must be same.',
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
                            array(
                                'name' => 'Regex',
                                'break_chain_on_failure' => true,
                                'options' => array(
                                    'pattern' => '/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
                                    'messages' => array(
                                        \Zend\Validator\Regex::NOT_MATCH => 'Please enter valid email'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'Zend\Validator\Db\NoRecordExists',
                                'options' => array(
                                    'table' => 'user',
                                    'field' => 'email',
                                    'adapter' => $this->dbAdapter,
                                    'messages' => array(
                                        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Email already exists'
                                    ),
                                ),
                            )
                        )
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}
