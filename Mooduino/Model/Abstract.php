<?php
/*  Copyright 2010  Michael Hodgins  (email : michael_hodgins@hotmail.)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Description of Mooduino_Model_Abstract
 *
 * @author Michael
 */
abstract class Mooduino_Model_Abstract extends Zend_Db_Table_Row_Abstract {

    private $_validation_errors = null;

    /**
     * Returns true if the model is valid (it passes the validation rules).
     * @return boolean
     */
    public function isValid() {
        if (is_null($this->_validation_errors)) {
            $errors = $this->validate();
            if (is_array($errors)) {
                $this->_validation_errors = $errors;
            } else {
                throw new Exception('Validate method didn\'t return an array.');
            }
        }
        return count($this->_validation_errors) == 0;
    }

    /**
     * Returns all validation errors if there are any or null if there are none.
     * @return array[string]string|array[string]array[int]string
     */
    public function getErrors() {
        if (is_array($this->_validation_errors)) {
            return $this->_validation_errors;
        } else {
            throw new Exception('Model has not been validated');
        }
    }

    /**
     * Returns the validation error or array of errors for the given field name.
     * Be sure that there is an error before calling this method by calling
     * hasError() first.
     * @param string $field
     * @return string|array[int]string
     */
    public function getError($field) {
        $errors = $this->getErrors();
        if (array_key_exists($field, $errors)) {
            return $this->_validation_errors[$field];
        } else {
            throw new Exception('Field not found');
        }
    }

    /**
     * Returns true if the given field name has a validation error or false if
     * it hasn't.
     * @param string $field
     * @return boolean
     */
    public function hasError($field) {
        return is_array($this->_validation_errors) && array_key_exists($field, $this->_validation_errors);
    }

    /**
     * An implementation of the method should validate the instance of the
     * implementing class and return an array of error messages. If there are
     * no errors, an empty array should be returned.
     * @return array[string]string|array[string]array[int]string
     */
    public abstract function validate();

    /**
     * Overrides the save() method in Zend_Db_Table_Row_Abstract so that it is
     * only called if $this->isValid() returns true.
     * @return mixed
     */
    public final function save() {
        if ($this->isValid()) {
            return parent::save();
        } else {
            throw new Exception('Model can\'t be saved as it isn\'t valid');
        }
    }

    /**
     * Given an Iterator such as a Zend_Form, this method will set any error
     * messages to the form elements.
     * @param Iterator $iterator
     */
    public final function discoverErrors(Iterator $iterator) {
        foreach ($iterator as $element) {
            if ($element instanceof Iterator) {
                $this->discoverErrors($element);
            } elseif ($element instanceof Zend_Form_Element) {
                $this->discoverError($element);
            }
        }
    }

    /**
     * If there is a validation error for the given field, it will be set.
     * @param Zend_Form_Element $element
     */
    private final function discoverError(Zend_Form_Element $element) {
        if ($this->hasError($element->getName())) {
            $element->addErrors($this->getError($element->getName()));
        }
    }
}
