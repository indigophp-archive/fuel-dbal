<?php

/*
 * This file is part of the Fuel DBAL package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\DBAL\Logging;

use Doctrine\DBAL\SQLParserUtils;
use Doctrine\DBAL\Connection;

/**
 * Log Doctrine DBAL queries to Fuel profiler and internal array
 *
 * @author Aspen Digital
 *
 * @see https://github.com/aspendigital/fuel-doctrine2/blob/master/classes/Fuel/Doctrine/Logger.php
 */
class ProfilerLogger implements SQLLogger
{
	/**
	 * Connection
	 *
	 * @var Connection
	 */
	protected $conn;

	/**
	 * Whether there is currently a benchmark running
	 *
	 * @var mixed
	 */
	protected $benchmark;

	/**
	 * Queries
	 *
	 * @var array
	 */
	protected $queries = [];

	/**
	 * Creates a new Profiler Logger
	 *
	 * @param string Connection
	 */
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}

	/**
	 * Returns the connection
	 *
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->conn;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function startQuery($sql, array $params = null, array $types = null)
	{
		$this->benchmark = false;

		// Don't re-log EXPLAIN statements from profiler
		if (substr($sql, 0, 7) == 'EXPLAIN')
		{
			return;
		}

		if ($params)
		{
			// Attempt to replace placeholders so that we can log a final SQL query for profiler's EXPLAIN statement
			// (this is not perfect-- getPlaceholderPositions has some flaws-- but it should generally work with ORM-generated queries)

			$isPositional = is_numeric(key($params));

			list($sql, $params, $types) = SQLParserUtils::expandListParameters($sql, $params, $types);

			if (empty($types))
			{
				$types = [];
			}

			$placeholders = SQLParserUtils::getPlaceholderPositions($sql, $isPositional);

			if ($isPositional)
			{
				$map = array_flip($placeholders);
			}
			else
			{
				$map = [];

				foreach ($placeholders as $name => $positions)
				{
					foreach ($positions as $pos)
					{
						$map[$pos] = $name;
					}
				}
			}

			ksort($map);
			$srcPos = 0;
			$finalSql = '';
			$first_param_index = key($params);

			foreach ($map as $pos=>$replace_name)
			{
				$finalSql .= substr($sql, $srcPos, $pos-$srcPos);

				if ($sql[$pos] == ':')
				{
					$srcPos = $pos + strlen($replace_name);
					$index = trim($replace_name, ':');
				}
				else // '?' positional placeholder
				{
					$srcPos = $pos + 1;
					$index = $replace_name + $first_param_index;
				}

				$finalSql .= $this->conn->quote($params[$index], \Arr::get($types, $index));
			}

			$finalSql .= substr($sql, $srcPos);

			$sql = $finalSql;
		}

		$this->benchmark = \Profiler::start("Database (Doctrine: " . $this->conn->getDatabase() . ")", $sql);
		$this->queries[] = $sql;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @codeCoverageIgnore
	 */
	public function stopQuery()
	{
		if ($this->benchmark)
		{
			\Profiler::stop($this->benchmark);

			$this->benchmark = null;
		}
	}

	/**
	 * Returns queries
	 *
	 * @return array
	 */
	public function getQueries()
	{
		return $this->queries;
	}
}
