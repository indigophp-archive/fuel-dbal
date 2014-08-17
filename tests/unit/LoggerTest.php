<?php

/*
 * This file is part of the Indigo DBAL package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\DBAL\Logging;

use Codeception\TestCase\Test;

/**
 * Tests for Profiler Logger
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Doctrine\DBAL\Logging\ProfilerLogger
 * @group              Dbal
 */
class LoggerTest extends Test
{
	/**
	 * @covers ::__construct
	 * @covers ::getConnection
	 * @covers ::getQueries
	 */
	public function testLogger()
	{
		$conn = \Mockery::mock('Doctrine\\DBAL\\Connection');

		$logger = new ProfilerLogger($conn);

		$this->assertSame($conn, $logger->getConnection());
		$this->assertInternalType('array', $logger->getQueries());
	}
}
