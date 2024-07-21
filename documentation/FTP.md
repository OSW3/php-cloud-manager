# FTP Driver

## Create a Client

### Prepare DSN

```php 
$driver = "ftp";
$user   = "username";
$pass   = "password";
$host   = "site.com";
$dsn    = "{$driver}://{$user}:{$pass}@{$host}";
```

### Connection

```php
use OSW3\CloudManager\Client;
$client = new Client($dsn);
```

## API Methods

Use methods of the [Client API](../readme.md)