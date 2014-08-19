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
		$dbal = require __DIR__.'/config/dbal.php';
		$db = require __DIR__.'/config/db.php';

		\Config::set('dbal', $dbal);
		\Config::set('db', $db);
	}

	/**
	 * @covers ::forge
	 */
	public function testForge()
	{
		\Config::delete('dbal.connections');

		$conn = Dbal::forge(null);

		$this->assertInstanceOf('Doctrine\\DBAL\\Connection', $conn);
	}

	/**
	 * @covers ::forge
	 */
	public function testAdvancedForge()
	{
		$conn = Dbal::forge(null);

		$this->assertInstanceOf('Doctrine\\DBAL\\Connection', $conn);
	}

	/**
	 * @covers            ::forge
	 * @expectedException InvalidArgumentException
	 */
	public function testForgeInvalid()
	{
		$conn = Dbal::forge('invalid');
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
					'driver'    => 'mysqli',
					'host'      => 'localhost',
					'port'      => null,
					'dbname'    => 'fuel_dev',
					'user'      => null,
					'password'  => null,
					'charset'   => null,
					'profiling' => false,
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
					'driver'    => 'pdo_mysql',
					'host'      => 'localhost',
					'dbname'    => 'fuel_dev',
					'user'      => null,
					'password'  => null,
					'charset'   => null,
					'profiling' => false,
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
