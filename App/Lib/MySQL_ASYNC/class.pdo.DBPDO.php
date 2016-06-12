<?php
class DBPDO {
	private $hostname         = DATABASE_HOST;
	private $database         = DATABASE_NAME;
	private $username         = DATABASE_USER;
	private $password         = DATABASE_PASS;
	private $char_set         = 'UTF-8';
	private $conn_id          = null;
	static private $_mem_obj  = null;

	/** @var int number of seconds to wait in retrieving data */
	public $timeout = 10;
	protected $credentials = array();
	protected $connections = array();
	
	/** Specify connection credentials
	 * @param ... used for mysqli_connect()
	*/
	function __construct() {
		$this->credentials = func_get_args();
	}
	
	/** Close all opened connections
	 */
	function __destruct() {
		foreach ($this->connections as $connection) {
			$connection->close();
		}
	}
	
	/** Execute query or get its data
	 * @param string query identifier
	 * @param array array(string $query, [array $credentials]) for executing query, array() for getting data
	 * @return bool|mysqli_result
	 */
	function __call($name, array $args) {
		if(isset($args[0]) && !empty($args[0])) {
			$query = $args[0];
			$time_start = microtime(true);
			$arr = array();
			//$connection = call_user_func_array('mysqli_connect', array($this->hostname,$this->username,$this->password,$this->database));
			$connection = mysqli_connect($this->hostname,$this->username,$this->password,$this->database);
			mysqli_set_charset($connection,$this->char_set);
			$this->connections[$name] = $connection;
			$connection->query($query, MYSQLI_ASYNC);
			// get data
			if (!isset($this->connections[$name])) { // wrong identifier
				return false;
			}
			//! handle second call with the same $name
			$connection = $this->connections[$name];
			//var_dump(get_class_methods($connection));
			do {
				$links = $errors = $reject = $this->connections;
				mysqli_poll($links, $errors, $reject, $this->timeout);
			} while (!in_array($connection, $links, true) && !in_array($connection, $errors, true) && !in_array($connection, $reject, true));
			return $connection->reap_async_query();
		}

	}
}
