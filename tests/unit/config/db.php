<?php

/*
 * This file is part of the Fuel DBAL package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
	'default' => [
		'type'        => 'pdo',
		'connection'  => [
			'dsn'        => 'mysql:host=localhost;dbname=fuel_dev',
			'persistent' => false,
			'compress'   => false,
		],
		'identifier'   => '`',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'collation'    => false,
		'enable_cache' => true,
		'profiling'    => true,
		'readonly'     => false,
	],
];
