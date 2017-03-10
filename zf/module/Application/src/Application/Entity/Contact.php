<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contact
 *
 * @ORM\Table(name="contact", uniqueConstraints={@ORM\UniqueConstraint(name="email", columns={"email"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Contact {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=250, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="contactno", type="string", length=250, nullable=false)
     */
    private $contactno;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=250, nullable=false)
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(name="post", type="integer", nullable=false)
     */
    private $post;

    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Contact
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set contactno
     *
     * @param string $contactno
     * @return Contact
     */
    public function setContactno($contactno) {
        $this->contactno = $contactno;

        return $this;
    }

    /**
     * Get contactno
     *
     * @return string 
     */
    public function getContactno() {
        return $this->contactno;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Contact
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set post
     *
     * @param integer $post
     * @return Contact
     */
    public function setPost($post) {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return integer 
     */
    public function getPost() {
        return $this->post;
    }

   
public function exchangeArray($data = array()) {
        if (isset($data['id']))
            $this->id = $this->checkNull($data['id']);

        if (isset($data['name']))
            $this->name = $this->checkNull($data['name']);

        if (isset($data['contactno']))
            $this->contactno = $this->checkNull($data['contactno']);

        if (isset($data['email']))
            $this->email = $this->checkNull($data['email']);

        if (isset($data['post']))
            $this->post = $this->checkNull($data['post']);

    }
    
    private function checkNull($string) {
        return (isset($string) && $string != "") ? $string : NULL;
    }
//    public function exchangeArray($data) {
//        $filter = new \Zend\Filter\Word\UnderscoreToCamelCase();
//        $variable = get_object_vars($this);
//        foreach ($data as $key => $val) {
//            $key = lcfirst($filter->filter($key));
//            if (array_key_exists($key, $variable)) {
//                $this->$key = $val;
//            }
//        }
//    }

    public function getGenderValue() {
        if ($this->gender == 0) {
            return "Male";
        } else if ($this->gender == 1) {
            return "Female";
        }
        return "";
    }

    public function getAge() {
        $dob = explode("-", $this->birthDate->format("Y-m-d"));
        $curMonth = date("m");
        $curDay = date("j");
        $curYear = date("Y");
        $age = $curYear - $dob[0];
        if ($curMonth < $dob[1] || ($curMonth == $dob[1] && $curDay < $dob[2]))
            $age--;
        return $age;
        
    }

    
    
}
