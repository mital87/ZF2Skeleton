<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Validators;

use Zend\File\Transfer\Adapter\Http;

class FileValidator extends Http {

    /**
     * Checks if the files are valid
     *
     * @param  string|array $files (Optional) Files to check
     * @return bool True if all checks are valid
     */
    public function isValid($files = null) {
        if (is_array($this->validators)) {
            $fileerrors = [];
            foreach ($this->validators as $class => $validator) {
                if ($class === "Zend\Validator\File\Upload") {
                    continue;
                }
                if (method_exists($validator, 'isValid')) {
                    foreach ($this->files as $file) {
                        if (isset($file['tmp_name']) && $file['tmp_name'] != "") {
                            if (!$validator->isValid($file)) {
                                $fileerrors += $validator->getMessages();
                            }
                        }
                    }
                }
            }
            $this->messages += $fileerrors;
            if (count($this->messages) > 0) {
                return false;
            }
        }
        return parent::isValid($files);
    }

    /**
     * overwrite the parent class function
     * Prepare the $_FILES array to match the internal syntax of one file per entry
     * 
     * @return Http
     */
    protected function prepareFiles() {
        parent::prepareFiles();
        foreach ($_FILES as $form => $content) {
            if (is_array($content['name'])) {
                foreach ($this->files[$form]['multifiles'] as $key => $value) {
                    $this->files[$value]['name'] = $this->__uniqueFileName($this->files[$value]['name']);
                }
            } else {
                $this->files[$form]['name'] = $this->__uniqueFileName($content['name']);
            }
        }
        return $this;
    }

    /**
     * Generate Unique file name
     * @param string $fileName
     * @return string
     */
    private function __uniqueFileName($fileName) {
        $fileName = str_ireplace(' ', '_', strtolower($fileName));
        return uniqid() . $fileName;
    }

}