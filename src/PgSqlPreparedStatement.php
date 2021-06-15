<?php

declare(strict_types=1);

namespace bohyn\PgSql;

class PgSqlPreparedStatement
{

	/** @var PgSqlConnection */
	private $db;
	/** @var string */
	private $name;

	public function __construct(PgSqlConnection $db, string $name)
	{
		$this->db = $db;
		$this->name = $name;
	}

	public function __destruct()
	{
		if ($this->db->transactionStatus() === PGSQL_TRANSACTION_INERROR) {
			return;
		}

		try {
			$this->db->query('DEALLOCATE ' . $this->db->escapeIdentifier($this->name));
		} catch (PgSqlException $e) {
			// Do nothing
		}
	}

	/**
	 * @param mixed[] $params
	 * @return PgSqlStatement
	 * @throws PgSqlException
	 */
	public function execute(array $params = []): PgSqlStatement
	{
		return $this->db->execute($this->name, $params);
	}
}
