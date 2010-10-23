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

class Mooduino_View_Smarty extends Zend_View_Abstract {

    private $_smarty;

    public function __construct($data) {
        parent::__construct($data);
        require_once "Smarty.class.php";

        $this->_smarty = new Smarty();
        $this->_smarty->deprecation_notices = false;
        $this->_smarty->template_dir = array($data['template_dir'], $data['layout_dir']);
        $this->_smarty->compile_dir = $data['compile_dir'];
        $this->_smarty->config_dir = $data['config_dir'];
        $this->_smarty->cache_dir = $data['cache_dir'];
        $this->_smarty->caching = $data['caching'];
        $this->_smarty->compile_check = $data['compile_check'];
        $this->assign('_view', $this);
        $this->assign('_layout', $this->layout());
    }

    public function getEngine() {
        return $this->_smarty;
    }

    public function __set($key, $val) {
        $this->_smarty->assign($key, $val);
    }

    public function __get($key) {
        return $this->_smarty->get_template_vars($key);
    }

    public function __isset($key) {
        return $this->_smarty->get_template_vars($key) != null;
    }

    public function __unset($key) {
        $this->_smarty->clear_assign($key);
    }

    public function assign($spec, $value=null) {
        if (is_array($spec)) {
            $this->_smarty->assign($spec);
            return;
        }
        $this->_smarty->assign($spec, $value);
    }

    public function clearVars() {
        $this->_smarty->clear_all_assign();
        $this->assign('_view', $this);
        $this->assign('_layout', $this->layout());
    }

    public function render($name) {
        return $this->_smarty->fetch(strtolower($name));
    }

    public function _run() {

    }

}
