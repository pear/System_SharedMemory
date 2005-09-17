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

class System_SharedMemory_Systemv extends System_SharedMemory_Common
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
    * handler for shmop_* functions
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
    function System_SharedMemory_Systemv($options)
    {
        extract($this->_default($options, array
        (
	       	'size'    => false,
	       	'project' => 's',
		)));

       if ($size === false) {
           $this->_h = shm_attach($this->_ftok($project));
       } else {
           if ($size < SHMMIN || $size > SHMMAX) {
               return $this->_connection = false;
           }

           $this->_h = shm_attach($this->_ftok($project), $size);
       }

	   $this->_connection = true;
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
     * @param int $name name of variable
     *
     * @return mixed value of the variable
     * @access public
     */
    function get($name)
    {
		return shm_get_var($this->_h, $this->_s2i($name));
    }

    /**
     * returns value of variable in shared mem
     *
     * @param string $name  name of the variable
     * @param string $value value of the variable
     *
     * @return bool true on success, false on fail
     * @access public
     */
    function set($name, $value)
    {
		return shm_put_var($this->_h, $this->_s2i($name), $value);
    }

    /**
     * remove variable from memory
     *
     * @param string $name  name of the variable
     *
     * @return bool true on success, false on fail
     * @access public
     */
    function rm($name)
    {
		return shm_remove_var($this->_h, $this->_s2i($name));
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
     * convert string to int
     *
     * @param string $name string to conversion
     *
     * @access private
     */
    function _s2i($name)
    {
    	return unpack('N', str_pad($name, 4, "\0", STR_PAD_LEFT));
    }
}
?>