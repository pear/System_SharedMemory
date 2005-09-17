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

class System_SharedMemory_File extends System_SharedMemory_Common
{
    /**
    * Contains internal options
    *
    * @var string
    *
    * @access private
    */
    var $_options;

    /**
    * true if plugin was connected to backend
    *
    * @var bool
    *
    * @access private
    */
    var $_connected;

    /**
     * Constructor. Init all variables.
     *
     * @param array $options
     *
     * @access public
     */
    function System_SharedMemory_File($options)
    {
        $this->_options = $this->_default($options, array
        (
        	'tmp'  => '/tmp',
		));

        $this->_connected = is_writeable($this->_options['tmp']) && is_dir($this->_options['tmp']);
    }

    /**
     * returns true if plugin was 
     * successfully connected to backend
     *
     * @return bool true if connected
     * @access public
     */
    function isConnected()
    {
        return $this->_connected;
    }

    /**
     * returns value of variable in shared mem
     *
     * @param string $name  name of the variable
     * @param string $value value of the variable
     *
     * @return mixed true on success or PEAR_error on fail
     * @access public
     */
    function get($name)
    {
    	$name = $this->_options['tmp'].'/smf_'.md5($name);

    	if (!file_exists($name)) {
    		return array();
    	}

        $fp = fopen($name, 'rb');
        if (is_resource($fp)) {
            flock ($fp, LOCK_SH);

			$str = fread($fp, filesize($name));
			fclose($fp);
            return $str == '' ? array() : unserialize($str);
        }

        return PEAR::raiseError('Cannot open file.', 1);
    }

    /**
     * returns value of variable in shared mem
     *
     * @param string $name  name of the variable
     * @param string $value value of the variable
     *
     * @return mixed true on success or PEAR_error on fail
     * @access public
     */
    function set($name, $value)
    {
        $fp = fopen($this->_options['tmp'].'/smf_'.md5($name), 'ab');
        if (is_resource($fp)) {
            flock ($fp, LOCK_EX);
            ftruncate($fp, 0);
            fseek($fp, 0);

            fwrite($fp, serialize($value));
            fclose($fp);
            clearstatcache();
            return true;
        }

        return PEAR::raiseError('Cannot write to file.', 2);
    }

    /**
     * remove variable from memory
     *
     * @param string $name  name of the variable
     *
     * @return mixed true on success or PEAR_error on fail
     * @access public
     */
    function rm($name)
    {
		$name = $this->_options['tmp'].'/smf_'.md5($name);

    	if (file_exists($name)) {
    		unlink($name);
    	}
    }

}
?>