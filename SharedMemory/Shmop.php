<?php

/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Evgeny Stepanischev <bolk@lixil.ru>                         |
// +----------------------------------------------------------------------+
// Project home page (Russian): http://bolk.exler.ru/files/shared/
//
// $Id$

require_once 'System/SharedMemory/Common.php';
require_once "PEAR.php";

class System_SharedMemory_Shmop extends System_SharedMemory_Common
{
    /**
    * handler for shmop_* functions
    *
    * @var string
    *
    * @access private
    */
    var $_h;

    /**
    * Contains internal options
    *
    * @var string
    *
    * @access private
    */
    var $_options;

    /**
     * Constructor. Init all variables.
     *
     * @param array $options
     *
     * @access public
     */
    function System_SharedMemory_Shmop($options)
    {
        $this->_options = $this->_default($options, array
        (
        	'size' => 1048576,
        	'tmp'  => '/tmp',
        	'project' => 's'
		));

       $this->_h = $this->_ftok($this->_options['project']);
    }

    /**
    * returns value of variable in shared mem
    *
    * @param mixed $name name of variable or false if all variables needs
    *
    * @return mixed PEAR_error or value of the variable
    * @access public
    */
    function get($name = false)
    {
        $id = shmop_open($this->_h, 'c', 0600, $this->_options['size']);

        if ($id !== false) {
            $ret = unserialize(shmop_read($id, 0, shmop_size($id)));
            shmop_close($id);

            if ($name === false) {
         		return $ret;
            }
            return isset($ret[$name]) ? $ret[$name] : null;
        }

        return PEAR::raiseError('Cannot open shmop.', 1);
    }

    /**
    * returns value of variable in shared mem
    *
    * @param string $name  name of the variable
    * @param string $value value of the variable
    *
    * @return bool true on success
    * @access public
    */
    function set($name, $value)
    {
    	$lh = $this->_lock();
        $val = $this->get();
        if (!is_array($val)) {
        	$val = array();
        }

        $val[$name] = $value;
        $val = serialize($val);
        return $this->_write($val, $lh);
    }

    /**
    * remove variable from memory
    *
    * @param string $name  name of the variable
    *
    * @return bool true on success
    * @access public
    */
    function rm($name)
    {
    	$lh = $this->_lock();

        $val = $this->get();
        if (!is_array($val)) {
			$val = array();
        }
        unset($val[$name]);
        $val = serialize($val);

        return $this->_write($val, $lh);
    }

    /**
     * ftok emulation for Windows
     *
     * @param string $project project ID
     *
     * @access private
     */
    function _ftok($project)
    {
        if (function_exists('ftok')) {
	        return ftok(__FILE__, $project);
        }

        $s = stat(__FILE__);
        return sprintf("%u", (($s['ino'] & 0xffff) | (($s['dev'] & 0xff) << 16) |
        (($project & 0xff) << 24)));
    }

    /**
     * write to the shared memory
     *
     * @param string $val values of all variables
     * @param resource $lh lock handler
     *
     * @return mixed PEAR_error or true on success
     * @access private
     */
    function _write(&$val, &$lh)
    {
        $id  = shmop_open($this->_h, 'c', 0600, $this->_options['size']);
        if ($id) {
           $ret = shmop_write($id, $val, 0) == strlen($val);
           shmop_close($id);
           $this->_unlock($lh);
           return $ret;
        }

        $this->_unlock($lh);
        return PEAR::raiseError('Cannot write to shmop.', 2);
    }

    /**
     * access locking function
     *
     * @return resource lock handler
     * @access private
     */
	function &_lock()
	{
		if (function_exists('sem_get')) {
			$fp = PHP_VERSION < 4.3 ? sem_get($this->_h, 1, 0600) : sem_get($this->_h, 1, 0600, 1);
			sem_acquire ($fp);
		} else {
			$fp = fopen($this->_options['tmp'].'/sm_'.md5($this->_h), 'w');
			flock($fp, LOCK_EX);
		}

		return $fp;
	}

    /**
     * access unlocking function
     *
     * @param resource $fp lock handler
     *
     * @access private
     */
	function _unlock(&$fp)
	{
		if (function_exists('sem_get')) {
			sem_release($fp);
		} else {
			fclose($fp);
		}
	}
}
?>