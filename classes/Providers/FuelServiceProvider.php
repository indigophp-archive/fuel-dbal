<?php

/*
 * This file is part of the Fuel DBAL package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Fuel\DBAL\Providers;

use Fuel\Dependency\ServiceProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;

/**
 * Provides DBAL service
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class FuelServiceProvider extends ServiceProvider
{
	/**
	 * {@inheritdoc}
	 */
	public $provides = [
		'dbal',
		'dbal.logger'
	];

	/**
	 * Default configuration values
	 *
	 * @var []
	 */
	protected $defaultConfig = [];

	public function __construct()
	{
		\Config::load('db', true);
		\Config::load('dbal', true);

		$config = \Config::get('dbal', []);
		$this->defaultConfig = \Arr::filter_keys($config, ['connections', 'types'], true);

		// Register types
		foreach (\Arr::get($config, 'types', []) as $type => $class)
		{
			Type::addType($type, $class);
		}

		// We don't have defined connections
		if ($connections = \Arr::get($config, 'connections', false) and ! empty($connections))
		{
			\Config::set('dbal.connections.__default__', $this->defaultConfig);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function provide()
	{
		$this->register('dbal', function($dic, $instance = '__default__', array $config = [])
		{
			// Legacy fuel db config support
			if ($db = \Config::get('db.' . $instance, []))
			{
				$db = $this->parseFuelConfig($db);
			}

			$config = array_merge($db, $this->defaultConfig, \Config::get('dbal.connections.', $instance, []), $config);

			$conn = DriverManager::getConnection($config);

			// Register mapping types
			if (isset($config['mapping_types']))
			{
				$platform = $conn->getDatabasePlatform();

				foreach ($config['mapping_types'] as $dbType => $doctrineType)
				{
					$platform->registerDoctrineTypeMapping($dbType, $doctrineType);
				}
			}

			if (\Arr::get($config, 'profiling', false))
			{
				$logger = $dic->resolve('dbal.logger', [$conn]);
				$conn->getConfiguration()->setSQLLogger($logger);
			}
		});

		$this->register('dbal.logger', 'Doctrine\\DBAL\\Logging\\ProfilerLogger');
	}

	/**
	 * Parses Fuel db config to DBAL compatible configuration
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function parseFuelConfig(array $config)
	{
		$params = array();

		$params['driver'] = $config['type'];

		if ($params['driver'] === 'pdo')
		{
			list($type, $dsn) = explode(':', $config['connection']['dsn'], 2);

			$params['driver'] .= '_' . $type;

			$dsn = explode(';', $dsn);

			foreach ($dsn as $d)
			{
				list($k, $v) = explode('=', $d);

				$params[$k] = $v;
			}
		}
		else
		{
			$params['dbname'] = $config['connection']['database'];
			$params['host'] = $config['connection']['hostname'];
			$params['port'] = \Arr::get($config, 'connection.port');
		}

		$params['user'] = \Arr::get($config, 'connection.username');
		$params['password'] = \Arr::get($config, 'connection.password');
		$params['charset'] = \Arr::get($config, 'charset');

		// Introduced this from Fuel, also available in DBAL config
		$params['profiling'] = \Arr::get($config, 'profiling', false);

		return $params;
	}
}
