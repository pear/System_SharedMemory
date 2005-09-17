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

class System_SharedMemory_Sharedance extends System_SharedMemory_Common
{
    /**
    * true if plugin was connected to backend
    *
    * @var bool
    *
    * @access private
    */
    var $_connected;

    /**
    * connection handler
    *
    * @var string
    *
    * @access private
    */
    var $_h;

    /**
     * Constructor. Init all variables.
     *
     * @param array $options
     *
     * @access public
     */

    /**
    * Contains internal options
    *
    * @var string
    *
    * @access private
    */
    var $_options;

    function System_SharedMemory_Sharedance($options)
    {
        $this->_options = ($this->_default($options, array
        (
			'host' => '127.0.0.1',
        	'port' => 1042,
        	'timeout' => 10,
		)));

		$this->_h = null;
		$this->_open();
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
    * @param string $name name of variable
    *
    * @return mixed value of the variable
    * @access public
    */
    function get($name)
    {
    	$this->_open();
        $s = 'F' . pack('N', strlen($name)) . $name;
        fwrite($this->_h, $s);

        for ($data = ''; !feof($this->_h);) {
        	$data .= fread($this->_h, 4096);
        }

        $this->_close();

        return $data === '' ? null : unserialize($data);
    }

   /**
    * returns value of variable in shared mem
    *
    * @param string $name  name of the variable
    * @param string $value value of the variable
    * @param int $ttl (optional) time to life of the variable
    *
    * @return bool true on success
    * @access public
    */
    function set($name, $value)
    {
    	$this->_open();
    	$value = serialize($value);
        $s = 'S' . pack('NN', strlen($name), strlen($value)) . $name . $value;

        fwrite($this->_h, $s);
        $ret = fgets($this->_h);
        $this->_close();

        return $ret === "OK\n";
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
    	$this->_open();
        $s = 'D' . pack('N', strlen($name)) . $name;
        fwrite($this->_h, $s);
        $ret = fgets($this->_h);
        $this->_close();

	    return $ret === "OK\n";
    }

    /**
    * close connection to backend
    * (sharedance isn't support persistent connection)
    *
    * @access private
    */
    function _close()
    {
    	fclose($this->_h);
    	$this->_h = false;
    }

    /**
    * open connection to backend if it doesn't connected yet
    *
    * @access private
    */
    function _open()
    {
    	if (!is_resource($this->_h)) {
	  		$this->_h = fsockopen($this->_options['host'], $this->_options['port'], $_, $_, $this->_options['timeout']);
			$this->_connected = is_resource($this->_h); 		
    	}
    }
}
?>