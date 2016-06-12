<?php

namespace React\MySQLi;

use Exception;
use mysqli;
use React\Promise\Deferred;

class Pool
{
    /**
     * @var callable
     * @return mysqli
     */
    private $makeConnection;

    /**
     * @var int
     */
    private $maxConnections;

    /**
     * pool of all connections (both idle and busy)
     * @var mysqli[]
     */
    private $pool = array();

    /**
     * @var int
     */
    private $pool_i = 0;

    /** array of Deferred objects waiting to be resolved with connection */
    private $waiting = array();


    public function __construct(callable $makeConnection, $maxConnections = 100)
    {
        $this->makeConnection = $makeConnection;
        $this->maxConnections = $maxConnections;
    }


    public function getConnection()
    {
        if (!empty($this->pool)) {
            $key = key($this->pool);
            $conn = $this->pool[$key];
            unset($this->pool[$key]);
            return \React\Promise\resolve($conn);
        }

        if ($this->pool_i >= $this->maxConnections) {
            $deferred = new Deferred();
            $this->waiting[] = $deferred;
            return $deferred->promise();
        }

        /**
         * @var mysqli|false $conn
         */
        $conn = call_user_func($this->makeConnection);
        if ($conn !== false) {
            $this->pool_i++;
        }

        return ($conn === false)
            ? \React\Promise\reject(new Exception(mysqli_connect_error()))
            : \React\Promise\resolve($conn);
    }


    public function free(mysqli $conn)
    {
        if ($conn->errno != 2006) {
            $this->pool[] = $conn;
        } else {
            $this->pool_i--;
        }

        if (!empty($this->waiting)) {
            $key = key($this->waiting);
            $deferred = $this->waiting[$key];
            unset($this->waiting[$key]);
            $this->getConnection()->done(function ($conn) use ($deferred) {
                /**
                 * @var Deferred $deferred
                 */
                $deferred->resolve($conn);
            }, array($deferred, 'reject'));
        }
    }
}
