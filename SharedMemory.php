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


class System_SharedMemory {

    /**
     * Create a new shared mem object
     *
     * @param string $type  the shared mem type (or false on autodetect)
     * @param array  $options  an associative array of option names and values
     *
     * @return object  a new System_Shared object
     *
     */
   	
   	function &factory($type = false, $options = array())
   	{
   		if ($type === false) {
   			$type = System_SharedMemory::getAvailableTypes(true);
   		} else {
   			$type = ucfirst(strtolower($type));
   		}

		require_once 'System/SharedMemory/'.$type . '.php';
		$class = 'System_SharedMemory_' . $type;

		$ref = &new $class($options);
		return $ref;
   	}

    /**
     * Get available types or first one
     *
     * @param bool $only_first false if need all types and true if only first one
     *
     * @return mixed list of available types (array) or first one (string)
     *
     */

     function getAvailableTypes($only_first = false)
     {
        $detect = array
        (
        	'eaccelerator' => 'Eaccelerator',	// Eaccelerator (Turck MMcache fork)
        	'mmcache'      => 'Mmcache',    	// Turck MMCache
        	'Memcache'     => 'Memcached',		// Memched
        	'shmop_open'   => 'Shmop',			// Shmop
        	'apc_fetch'    => 'Apc',			// APC
        	'apache_note'  => 'Apachenote',		// Apache note
        	'shm_get_var'  => 'Systemv',        // System V
        	'sqlite_open'  => 'Sqlite',			// SQLite
        	'file'         => 'File',			// Plain text
        	'fsockopen'    => 'Sharedance',		// Sharedance
        );

        $types = array();

        foreach ($detect as $func=>$val) {
        	if (function_exists($func) || class_exists($func)) {
				if ($only_first) {
					return $val;
				}

				$types[] = $val;
        	}
        }

        return $types;
     }
}
?>