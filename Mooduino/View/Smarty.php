<?php

class Mooduino_View_Smarty extends Zend_View_Abstract {

    private $_smarty;

    public function __construct($data) {
        parent::__construct($data);
        require_once "Smarty.class.php";

        $this->_smarty = new Smarty();
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
    }

    public function render($name) {
        return $this->_smarty->fetch(strtolower($name));
    }

    public function _run() {

    }

}
