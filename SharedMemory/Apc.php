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

class System_SharedMemory_Apc extends System_SharedMemory_Common
{
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
        return apc_fetch($name);
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
    function set($name, $value, $ttl = 0)
    {
        return apc_store($name, $value, $ttl);
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
        return apc_delete($name);
    }
}
?>