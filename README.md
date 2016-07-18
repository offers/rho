# rho

## Usage
Check out the examples directory

## Run the Tests
```
docker-compose build
docker-compose up -d
docker-compose exec rho phpunit
```

### Logging

If you'd like your logs to include a name for the client you are wrapping, you can use Monolog processors like so:

```php
// clone the logger to modify it w/o affecting the original
$myLogger = clone $logger;

$myLogger->pushProcessor(function ($record) {
    $record['extra']['client'] = 'My Client Name';
    return $record;
});

Rho\Retrier::wrap($client, ['logger' => $myLogger]);
```
