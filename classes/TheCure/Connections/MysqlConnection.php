<?php
/**
 * A MySQL connection
 *
 * @package     TheCure
 * @category    Connection
 * @copyright   Gignite, 2012
 * @license     MIT
 */
namespace TheCure\Connections;

class MysqlConnection implements Connection {

	/**
	 * @var \mysqli
	 */
	protected $connection;

	/**
	 * Setup a new MySQL connection.
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * Returns the config key if it exists otherwise
	 * returns the default value.
	 *
	 * @param  string $key
	 * @param  null   $default
	 * @return string|null
	 */
	protected function config($key, $default = NULL)
	{
		if (isset($this->config[$key]))
		{
			return $this->config[$key];
		}

		return $default;
	}

	/**
	 * Creates a new MySQL connection using the mysqli PHP driver.
	 *
	 * @return \mysqli
	 */
	protected function connect()
	{
		$mysqli = new \mysqli(
			$this->config('connection'),
			$this->config('username'),
			$this->config('password'),
			$this->config('db')
		);

		if ($mysqli->connect_errno) {
			    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
		}

		return $mysqli;
	}

	/**
	 * Connects to MySQL if it hasn't already and selects the DB
	 * specified in the configuration.
	 *
	 * @return \mysqli
	 */
	public function get()
	{
		if ($this->connection === NULL)
		{
			$this->connection = $this->connect();
		}

		return $this->connection;
	}

}
