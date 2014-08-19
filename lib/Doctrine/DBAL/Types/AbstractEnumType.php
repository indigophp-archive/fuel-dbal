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

use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Abstract Enum Type
 *
 * Use this class to create custom enum types
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 *
 * @link http://docs.doctrine-project.org/en/2.0.x/cookbook/mysql-enums.html
 */
abstract class AbstractEnumType extends Type
{
	/**
	 * Name of type
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Enum values
	 *
	 * @var array
	 */
	protected $values = array();

	/**
	 * {@inheritdoc}
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if (!in_array($value, $this->values))
		{
			throw new \InvalidArgumentException("Invalid '".$this->name."' value");
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		$values = array_map(array($this, 'escapeValue'), $this->values);

		return "ENUM(".implode(", ", $values).") COMMENT '(DC2Type:".$this->name.")'";
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the values
	 *
	 * @return array
	 */
	public function getValues()
	{
		return $this->values;
	}

	/**
	 * Escapes a string value
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function escapeValue($value)
	{
		return "'".$value."'";
	}
}
