<?php
/**
 * API moveStep Unit tests for HTML_Progress2 class.
 *
 * @version    $Id: HTML_Progress2_TestCase_moveStep.php,v 1.3 2005/08/18 09:40:39 farell Exp $
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @package    HTML_Progress2
 * @ignore
 */

class HTML_Progress2_TestCase_moveStep extends PHPUnit_TestCase
{
    /**
     * HTML_Progress2 instance
     *
     * @var        object
     */
    var $progress;

    function HTML_Progress2_TestCase_moveStep($name)
    {
        $this->PHPUnit_TestCase($name);
    }

    function setUp()
    {
        error_reporting(E_ALL & ~E_NOTICE);

        $prefs= array('push_callback' => array(&$this, '_handleError'));
        $this->progress = new HTML_Progress2($prefs, HTML_PROGRESS2_BAR_HORIZONTAL, 10, 100);
    }

    function tearDown()
    {
        unset($this->progress);
    }

    function _methodExists($name)
    {
        if (substr(PHP_VERSION,0,1) < '5') {
            $n = strtolower($name);
        } else {
            $n = $name;
        }
        if (in_array($n, get_class_methods($this->progress))) {
            return true;
        }
        $this->assertTrue(false, 'method '. $name . ' not implemented in ' . get_class($this->progress));
        return false;
    }

    function _handleError($code, $level)
    {
        // don't die if the error is an exception (as default callback)
        return PEAR_ERROR_RETURN;
    }

    function _getResult()
    {
        if ($this->progress->hasErrors()) {
            $err = $this->progress->getError();
            $msg = $err->getMessage() . '&nbsp;&gt;&gt;';
            $this->assertTrue(false, $msg);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * TestCases for method moveStep().
     */
    function test_moveStep_fail_no_integer()
    {
        if (!$this->_methodExists('moveStep')) {
            return;
        }
        $this->progress->moveStep('25');
        $this->_getResult();
    }

    function test_moveStep_fail_less_than_min()
    {
        if (!$this->_methodExists('moveStep')) {
            return;
        }
        $this->progress->moveStep(-1);
        $this->_getResult();
    }

    function test_moveStep_fail_greater_than_max()
    {
        if (!$this->_methodExists('moveStep')) {
            return;
        }
        $this->progress->moveStep(200);
        $this->_getResult();
    }

    function test_moveStep()
    {
        if (!$this->_methodExists('moveStep')) {
            return;
        }
        $this->progress->moveStep(15);
        $this->_getResult();
    }
}
?>