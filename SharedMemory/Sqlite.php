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

class System_SharedMemory_Sqlite extends System_SharedMemory_Common
{
    /**
    * SQLite object handler
    *
    * @var object
    *
    * @access private
    */
    var $_h;

    /**
    * true if plugin was connected to backend
    *
    * @var bool
    *
    * @access private
    */
    var $_connected;

    /**
    * hash of SQLite table options
    *
    * @var array
    *
    * @access private
    */
    var $_options;

    /**
     * Constructor. Init all variables.
     * SQLite table must be created:
     * CREATE sharedmemory(var text PRIMARY KEY, value TEXT)
     * It's very important!
     *
     * @param array $options
     *
     * @access public
     */
    function System_SharedMemory_Sqlite($options)
    {
        $this->_options = $this->_default($options, array
        (
	       	'db' => ':memory:',
        	'table'  => 'sharedmemory',
        	'var' => 'var',
        	'value' => 'value',
        	'persistent' => false,
		));

		$func = $this->_options['persistent'] ? 'sqlite_popen' : 'sqlite_open';

    	$this->_h = $func($this->_options['db']);
    	$this->_connected = is_resource($this->_h);
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
    	$name   = sqlite_escape_string($name);
    	$sql = "SELECT {$this->_options['value']} 
    	        FROM {$this->_options['table']} 
    	        WHERE {$this->_options['var']}='$name'
    	        LIMIT 1";

		$result = sqlite_query($this->_h, $sql);
		if (sqlite_num_rows($result)) {
			return unserialize(sqlite_fetch_single($result));
		}

		return null;
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
    	$name  = sqlite_escape_string($name);
    	$value = sqlite_escape_string(serialize($value));

    	$sql  = "REPLACE INTO {$this->_options['table']}
    	         ({$this->_options['var']}, {$this->_options['value']})
    	         VALUES ('$name', '$value')";

    	sqlite_query($this->_h, $sql);
        return true;
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
    	$name  = sqlite_escape_string($name);

    	$sql  = "DELETE FROM {$this->_options['table']}
    	         WHERE {$this->_options['var']}='$name'";

    	sqlite_query($this->_h, $sql);
        return true;
    }
}
?>