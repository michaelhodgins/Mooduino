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
require 'lessc.inc.php';

/**
 * A Zend Framework View Helper for compiling less css style sheets and
 * adding them to the view.
 * @author Michael Hodgins
 */
class Mooduino_View_Helper_LessCss extends Zend_View_Helper_Abstract {
    /**
     * These are the methods that the class implements via the __call()
     * method. These names are the same as the equivelent methods in the
     * HeadLink view helper.
     * @var array
     */
    private static $methods = array(
        'appendStylesheet',
        'offsetSetStylesheet',
        'prependStylesheet',
        'setStylesheet',
        'appendAlternate',
        'offsetSetAlternate',
        'prependAlternate',
        'setAlternate'
    );

    /**
     * Returns the LessCss helper.
     * @return Mooduino_View_Helper_LessCss
     */
    public function lessCss() {
        return $this;
    }

    /**
     * Defers to the toString() method in the HeadLink helper.
     * @param int $indent
     */
    public function toString($indent = null) {
        $this->view->headLink()->toString($indent);
    }

    /**
     * Implements the following methods.
     *
     * public function appendStylesheet($lessPath, $file, $media, $conditionalStylesheet, $extras)
     * public function offsetSetStylesheet($index, $lessPath, $file, $media, $conditionalStylesheet, $extras)
     * public function prependStylesheet($lessPath, $file, $media, $conditionalStylesheet, $extras)
     * public function setStylesheet($lessPath, $file, $media, $conditionalStylesheet, $extras)
     * public function appendAlternate($lessPath, $file, $type, $title, $extras)
     * public function offsetSetAlternate($index, $lessPath, $file, $type, $title, $extras)
     * public function prependAlternate($lessPath, $file, $type, $title, $extras)
     * public function setAlternate($lessPath, $file, $type, $title, $extras)
     *
     * @param string $method
     * @param array $args
     * @return void
     */
    public function __call($method, $args) {
        if (in_array($method, self::$methods)) {
            $argOffset = 0;
            if (preg_match('/^offset/', $method) == 1) {
                $argOffset = + 1;
            }
            $styleSheetPath = $this->compileLess($args[$argOffset], $args[$argOffset + 1]);
            array_splice($args, $argOffset, 2, '');
            $this->callHeadLink($styleSheetPath, $method, $args, $argOffset);
            return $this;
        }
        return parent::__call($method, $args);
    }

    /**
     * Compiles the less file or files and returns one or more stylesheet
     * locations.
     * @param string $lessPath
     * @param array|string $file
     * @return array|string
     */
    private function compileLess($lessPath, $file) {
        if (is_array($file)) {
            $retVal = array();
            foreach ($file as $path) {
                $retVal[] = $this->compileLess($lessPath, $path);
            }
            return $retVal;
        } else {
            $styleSheetPath = $lessPath . 'c/' . preg_replace('/.less$/', '.css', $file);
            $pub = realpath(APPLICATION_PATH . '/../public');
            $source = $pub . $lessPath . $file;
            $sink = $pub . $styleSheetPath;
            lessc::ccompile($source, $sink);
            return $styleSheetPath;
        }
    }

    /**
     * Adds the stylesheet or stylesheets to the HeadLink helper.
     * @param array|string $styleSheets
     * @param string $method
     * @param array $args
     * @param int $hrefPosition
     */
    private function callHeadLink($styleSheets, $method, $args, $hrefPosition) {
        Zend_Debug::dump($styleSheets, '$stylesheets', false);
        if (is_array($styleSheets)) {
            foreach ($styleSheets as $styleSheet) {
                $this->callHeadLink($styleSheet, $method, $args, $hrefPosition);
            }
        } else {
            $args[$hrefPosition] = $styleSheets;
            Zend_Debug::dump($args, '$args', false);
            $this->view->headLink()->__call(
                    $method,
                    $args
            );
        }
    }

}
