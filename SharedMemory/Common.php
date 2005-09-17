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

class System_SharedMemory_Common
{
   /**
    * returns true if plugin was 
    * successfully connected to backend
    *
    * @return bool true if connected
    * @access public
    */
    function isConnected()
    {
        return true;
    }

    /**
     * returns name of current engine
     *
     * @return string name of engine
     * @access public
     */
    function engineName()
    {
        return strtolower(substr(basename(__FILE__), 0, -4));
    }

    /**
     * fill non-set properties by def values
     *
     * @param array options array
     * @param array hash of pairs keys and default values
     *
     * @return array filled array
     * @access public
     */
    function _default($options, $def)
    {
    	foreach ($def as $key=>$val) {
    		if (!isset($options[$key])) {
    			$options[$key] = $val;
			}
    	}

    	return $options;
    }
}
?>