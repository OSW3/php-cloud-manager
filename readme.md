# URL Manager

Provides cloud connection and file manipulation tools.

## How to install

```shell
composer require osw3/php-cloud-manager
```

## Supported services

- FTP
- *forthcoming* ~~FTPS~~
- *forthcoming* ~~SFTP~~
- *forthcoming* ~~SSH~~
- *forthcoming* ~~Google Drive~~
- *forthcoming* ~~Dropbox~~
- *forthcoming* ~~pCloud~~
- *forthcoming* ~~Mega~~

## How to use

### Create new connection

#### Prepare DSN

```php 
$driver = "ftp";
$user   = "username";
$pass   = "password";
$host   = "site.com";
$dsn    = "{$driver}://{$user}:{$pass}@{$host}";
```

#### Connection : Method 1

```php
use OSW3\CloudManager\Client;
$client = new Client($dsn);
```

##### Don't auto connect

```php 
$auto_connect = false;
$auto_auth    = false;
$client       = new Client($dsn, $auto_connect);

$client->connect($auto_auth);
```

##### Don't auto authenticate

```php 
$auto_connect = true;
$auto_auth    = false;
$client       = new Client($dsn, $auto_connect, $auto_auth);

$client->authenticate();
```

#### Connection : Method 2

```php 
$client = new Client();
$client->dsn()->setDriver($driver);
$client->dsn()->setHost($host);
$client->dsn()->setUser($user);
$client->dsn()->setPass($pass);

$client->connect(true);
```

### DSN Infos

- `hasDsn(): bool`

    ```php
    $client->hasDsn();
    ```

- `getDriver(): ?string`

    ```php
    $client->dsn()->getDriver(); // ftp
    ```

- `getHost(): ?string`

    ```php
    $client->dsn()->getHost(); // site.com
    ```

- `getUser(): ?string`

    ```php
    $client->dsn()->getUser(); // user
    ```

- `getPass(): ?string`

    ```php
    $client->dsn()->getPass(); // pass
    ```

- `getPort(): ?int`

    ```php
    $client->dsn()->getPort(); // 21
    ```

- `get(): ?string`

    ```php
    $client->dsn()->get(); // ftp://user:pass@site.com";
    ```

### Connection status

- `getConnection(): resource`

    return connection resource (e.g.: `\FTP\Connection` for FTP Driver)

    ```php
    $client->getConnection();
    ```

- `isConnected(): bool`

    return `true` if the host is connected.

    ```php
    $client->isConnected();
    ```

- `hasCredential(): bool`

    return `true` if the user is authenticated.

    ```php
    $client->hasCredential();
    ```

### Temp directory

- `setLocalTempDirectory(string $directory): static`

    Set the local server Temp directory for file manipulation.

    ```php
    $client->setLocalTempDirectory("my/temp/dir");
    ```

### Navigation

- `location(): string`

    return the pointer location.

    ```php
    $client->location();
    ```

- `navigateTo(string $directory): bool`

    Set the pointer to a directory.

    ```php
    $client->location("/www");
    ```

- `parent(): bool`

    Set the pointer to a parent directory.

    ```php
    $client->parent();
    ```

- `root(): bool`

    Set the pointer to the root.

    ```php
    $client->root();
    ```

### Directories and files infos

- `isDirectory(string $path): bool`

    return `true` if the `$path` is a directory.

    ```php
    $client->isDirectory("/www/my-dir");
    ```

- `isFile(string $path): bool`

    return `true` if the `$path` is a file.

    ```php
    $client->isFile("/www/my-dir");
    ```

- `isLink(string $path): bool`

    return `true` if the `$path` is a link.

    ```php
    $client->isLink("/www/my-dir");
    ```

- `isBlock(string $path): bool`

    return `true` if the `$path` is a block.

    ```php
    $client->isBlock("/www/my-dir");
    ```

- `isCharacter(string $path): bool`

    return `true` if the `$path` is a character.

    ```php
    $client->isCharacter("/www/my-dir");
    ```

- `isSocket(string $path): bool`

    return `true` if the `$path` is a socket.

    ```php
    $client->isSocket("/www/my-dir");
    ```

- `isPipe(string $path): bool`

    return `true` if the `$path` is a Pipe.

    ```php
    $client->isPipe("/www/my-dir");
    ```

- `infos(?string $path=null, ?string $part=null): array|string`

    return some infos about the $path.

    ```php
    $client->infos("/www/my-dir"); // [...]
    $client->infos("/www/my-dir", "type"); // directory
    ```

### Permissions

- `setPermission(string $path, int $code): bool`

    Set permissions code to a $path

    ```php
    $client->setPermission("/www/my-dir", 0777);
    ```

- `getPermission(string $path): string`

    Read permissions code from a $path

    ```php
    $client->getPermission("/www/my-dir"); // 0777
    ```

- `permissions(string $path, ?int $code=null): string|bool`

    Hybrid permissions getter/setter.
    Set permission to path if `$code` is not `null`.
    Get permission code if `$code` is `null`

    ```php
    $client->permissions("/www/my-dir", 0777); // true
    $client->permissions("/www/my-dir"); // 0777
    ```

### Directories API

- `directoryList(?string $directory=null): array`

    Return the content of a directory, like the Unix PWD command.
    Return the current pointer location content if the `$directory` parameter is `null`.

    ```php 
    $client->directoryList("/www/my-dir"); // [...]
    ```

- `createDirectory(string $directory, int $permissions=0700, bool $navigateTo = true): bool`

    Create a directory and set the pointer into the new location id `$navigateTo` is `true`.

    ```php 
    $client->createDirectory("/www/my-dir/my-sub-dir");
    ```

- `deleteDirectory(?string $directory, bool $recursive=true): bool`

    Delete a directory and its contains.

    ```php 
    $client->deleteDirectory("/www/my-dir/my-sub-dir");
    ```

- `duplicateDirectory(string $source, string $destination): bool`

    Duplicate a directory on the Client

    ```php 
    $client->duplicateDirectory("/www/my-dir", "/www/my-other-dirt");
    ```

- `copyDirectory(string $source, string $destination): bool`

    Alias for `duplicateDirectory`.

- `sendDirectory(string $source, string $destination, bool $override=false): bool`

    Send a directory from your server to the remote Client

    ```php 
    $client->sendDirectory("C://my-dir", "/www/my-dir");
    ```

- `getDirectory(string $source, string $destination, bool $override=false): bool`

    Get a directory from a Client to your server

    ```php 
    $client->getDirectory("/www/my-dir", "C://my-dir");
    ```

### Files API

- `createFile(string $filename, string $content="", bool $binary=false): bool`

    Create a file from Stream to the Client.

    ```php 
    $client->createFile("/www/my-dir/my-file.txt", "Hi!\This is my file.");
    ```

- `deleteFile(?string $filename): bool`

    Delete a file from a Client.

    ```php 
    $client->deleteFile("/www/my-dir/my-file.txt");
    ```

- `duplicateFile(string $source, string $destination): bool`

    Duplicate a file on the Client

    ```php 
    $client->duplicateFile("/www/my-dir/my-file.txt", "/www/my-other-dir/my-file.txt");
    ```

- `copyFile(string $source, string $destination): bool`

    Alias for `duplicateFile`.

- `sendFile(string $source, string $destination, bool $override=false): bool`

    Send a file from your server to the remote Client

    ```php 
    $client->sendFile("C://my-dir/my-file.txt", "/www/my-dir/my-file.txt");
    ```

- `getFile(string $source, string $destination, bool $override=false): bool`

    Get a file from a Client to your server

    ```php 
    $client->getFile("/www/my-dir/my-file.txt", "C://my-dir/my-file.txt");
    ```

### Directories & Files API (Hybrid aliases of previous API)

- `delete(?string $path, bool $recursive=true): bool`;

    Delete a directory or a file

    ```php 
    $client->delete("/www/my-dir/my-file.txt");
    $client->delete("/www/my-dir");
    ``` 

- `duplicate(string $source, string $destination): bool`;

    Duplicate a directory or a file

    ```php 
    $client->duplicate("/www/my-dir/my-file.txt", "/www/my-sub-dir/my-file.txt");
    $client->duplicate("/www/my-dir", "/www/my-sub-dir");
    ``` 

- `copy(string $source, string $destination): bool`;

    Alias of `duplicate`

- `move(string $source, string $destination): bool`;

    Duplicate a directory or a file and delete the `$source`.

    ```php 
    $client->move("/www/my-dir/my-file.txt", "/www/my-sub-dir/my-file.txt");
    $client->move("/www/my-dir", "/www/my-sub-dir");
    ``` 

- `rename(string $source, string $destination): bool`;

    Alias of `move`

- `send(string $source, string $destination, bool $override=false): bool`;

    Send a directory or a file from your server to the Client

    ```php 
    $client->send("C://my-dir", "/www/my-dir");
    $client->send("C://my-dir/my-file.txt", "/www/my-dir/my-file.txt");
    ``` 

- `get(string $source, string $destination, bool $override=false): bool`;

    Get a directory or a file from the Client to your server

    ```php 
    $client->get("/www/my-dir", "C://my-dir");
    $client->get("/www/my-dir/my-file.txt", "C://my-dir/my-file.txt");
    ``` 