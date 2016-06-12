# React MySQLi

Asynchronous & non-blocking MySQLi driver for [React.PHP](https://github.com/reactphp/react).

Require [php-mysqlnd](http://php.net/manual/ru/book.mysqlnd.php) extension

## Install

```bash
composer require pahenrus/react-mysqli
```

## Example

```php
$loop = \React\EventLoop\Factory::create();

$makeConnection = function () {
    return mysqli_connect('localhost', 'vagrant', '', 'test');
};

$mysql = new \React\MySQLi\Client($loop, new \React\MySQLi\Pool($makeConnection, 10));

$mysql->query('select * from test')->then(
    function (\React\MySQLi\Result $result) {
        print_r($result->all());
    },
    function ($error) {
        error_log($error);
    }
);

$loop->run();
```