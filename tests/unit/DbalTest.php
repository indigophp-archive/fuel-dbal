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

use Codeception\TestCase\Test;

/**
 * Tests for Dbal
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Indigo\Fuel\Dbal
 * @group              Dbal
 */
class DbalTest extends Test
{
	/**
	 * {@inheritdoc}
	 */
	public function _before()
	{
		$config = require __DIR__.'/config.php';

		\Config::set('db', $config);
	}

	/**
	 * @covers ::forge
	 */
	public function testForge()
	{
		$conn = Dbal::forge(null);

		$this->assertInstanceOf('Doctrine\\DBAL\\Connection', $conn);
	}

	/**
	 * Provides test data for testParser
	 *
	 * @return []
	 */
	public function configProvider()
	{
		return [
			0 => [
				[
					'type' => 'mysqli',
					'connection' => [
						'hostname' => 'localhost',
						'database' => 'fuel_dev',
					],
				],
				[
					'driver'   => 'mysqli',
					'host'     => 'localhost',
					'port'     => null,
					'dbname'   => 'fuel_dev',
					'user'     => null,
					'password' => null,
				],
			],
			1 => [
				[
					'type' => 'pdo',
					'connection' => [
						'dsn' => 'mysql:host=localhost;dbname=fuel_dev',
					],
				],
				[
					'driver'   => 'pdo_mysql',
					'host'     => 'localhost',
					'dbname'   => 'fuel_dev',
					'user'     => null,
					'password' => null,
				],
			],
		];
	}

	/**
	 * @covers       ::parseFuelConfig
	 * @dataProvider configProvider
	 */
	public function testParser($config, $expected)
	{
		$this->assertEquals($expected, Dbal::parseFuelConfig($config));
	}
}
