<?php

/*
 * This file is part of the Fuel DBAL package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\DBAL\Types;

use Codeception\TestCase\Test;

/**
 * Tests for Enum Type
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @coversDefaultClass Doctrine\DBAL\Types\AbstractEnumType
 * @group              Dbal
 */
class EnumTest extends Test
{
	/**
	 * Platform mock
	 *
	 * @var Doctrine\DBAL\Platforms\AbstractPlatform
	 */
	protected $platform;

	/**
	 * Enum type
	 *
	 * @var AbstractEnumType
	 */
	protected $type;

	/**
	 * {@inheritdoc}
	 */
	public function _before()
	{
		$this->platform = \Mockery::mock('Doctrine\\DBAL\\Platforms\\AbstractPlatform');
		$this->type = Type::getType('enumdummy');
	}

	/**
	 * @covers ::convertToDatabaseValue
	 */
	public function testEnumConvertsToDatabaseValue()
	{
		$this->assertEquals('TRUE', $this->type->convertToDatabaseValue('TRUE', $this->platform));
	}

	/**
	 * @covers            ::convertToDatabaseValue
	 * @expectedException InvalidArgumentException
	 */
	public function testConversionFailure()
	{
		$this->type->convertToDatabaseValue('NULL', $this->platform);
	}

	/**
	 * @covers ::getSqlDeclaration
	 * @covers ::escapeValue
	 */
	public function testSqlDeclaration()
	{
		$expected = "ENUM('TRUE', 'FALSE') COMMENT '(DC2Type:enumdummy)'";

		$this->assertEquals($expected, $this->type->getSqlDeclaration([], $this->platform));
	}

	/**
	 * @covers ::getName
	 */
	public function testName()
	{
		$this->assertEquals('enumdummy', $this->type->getName());
	}

	/**
	 * @covers ::getValues
	 */
	public function testValues()
	{
		$this->assertEquals(['TRUE', 'FALSE'], $this->type->getValues());
	}
}
