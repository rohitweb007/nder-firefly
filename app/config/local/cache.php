<?php

return array(
	'driver' => 'database',
	'path' => storage_path().'/cache',
	'connection' => 'mysql',
	'table' => 'cache',
	'memcached' => array(

		array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100),

	),
	'prefix' => 'Firefly',

);
