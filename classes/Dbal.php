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
	}

	/**
	 * {@inheritdoc}
	 */
	public static function forge(
		$instance = 'default',
		Configuration $config = null,
		EventManager $eventManager = null
	) {
		$params = \Config::get('dbal.' . $instance, false);

		if ($params === false)
		{
			if ($instance === null)
			{
				$instance = \Config::get('db.active', 'default');
			}

			$params = \Config::get('db.' . $instance, false);

			if ($params === false)
			{
				throw new \InvalidArgumentException('This is not a valid instance: '. $instance);
			}

			$params = static::parseFuelConfig($params);
		}

		$conn = DriverManager::getConnection($params, $config, $eventManager);

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
