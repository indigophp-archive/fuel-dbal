<?php

/*
 * This file is part of the Fuel DBAL package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Fuel;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\ProfilerLogger;
use Doctrine\DBAL\Event\Listeners\MysqlSessionInit;
use Doctrine\DBAL\Types\Type;

/**
 * DBAL Connection Facade
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Dbal extends \Facade
{
	use \Indigo\Core\Facade\Instance;

	/**
	 * {@inheritdoc}
	 */
	protected static $_config = 'dbal';

	/**
	 * Make sure db config is loaded
	 *
	 * @codeCoverageIgnore
	 */
	public static function _init()
	{
		\Config::load('db', true);

		parent::_init();

		// Register types
		$types = \Config::get('dbal.types', array());

		foreach ($types as $type => $class)
		{
			Type::addType($type, $class);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function forge(
		$instance = null,
		Configuration $configuration = null,
		EventManager $eventManager = null
	) {
		// Try to get the default instance
		if ($instance === null)
		{
			static::$_instance = $instance = \Config::get('dbal.default_connection', \Config::get('db.active', 'default'));
		}

		$params = array();

		// Remove some keys from config, not used anymore
		$config = \Config::get('dbal', array());
		$config = \Arr::filter_keys($config, array('default_connection', 'connections', 'types'), true);

		// We have defined connections
		if ($connections = \Config::get('dbal.connections', false))
		{
			// Get connections and retrive connection specific configuration
			if ($params = \Arr::get($connections, $instance, array()))
			{
				$params = array_merge($config, $params);
			}
		}
		elseif ($instance === static::$_instance)
		{
			$params = $config;
		}

		// Legacy fuel db config support
		if ($db = \Config::get('db.' . $instance, false))
		{
			$db = static::parseFuelConfig($db);
			$params = array_merge($db, $params);
		}

		// We don't have any data
		if (empty($params))
		{
			throw new \InvalidArgumentException('No connection data for this instance: ' . $instance);
		}

		$conn = DriverManager::getConnection($params, $configuration, $eventManager);

		// PDO ignores the charset property before 5.3.6 so the init listener has to be used instead
		//@codeCoverageIgnoreStart
		if (isset($params['charset']) and version_compare(PHP_VERSION, '5.3.6', '<'))
		{
			if (
				(isset($params['driver']) and stripos($params['driver'], 'mysql') !== false) or
				(isset($params['driver_class']) and stripos($params['driver_class'], 'mysql') !== false)
			) {
				$mysqlSessionInit = new MysqlSessionInit($params['charset']);
				$conn->getEventManager()->addEventSubscriber($mysqlSessionInit);
			}
		}
		//@codeCoverageIgnoreEnd

		// Register mapping types
		if (isset($params['mapping_types']))
		{
			$platform = $conn->getDatabasePlatform();

			foreach ($params['mapping_types'] as $dbType => $doctrineType)
			{
				$platform->registerDoctrineTypeMapping($dbType, $doctrineType);
			}
		}

		if (\Arr::get($params, 'profiling', false))
		{
			$logger = new ProfilerLogger($conn);
			$conn->getConfiguration()->setSQLLogger($logger);
		}

		return static::newInstance($instance, $conn);
	}

	/**
	 * Parses Fuel db config to DBAL compatible configuration
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public static function parseFuelConfig(array $config)
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
