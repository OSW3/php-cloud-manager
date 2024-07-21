# Cloud Manager

Provides cloud connection and file manipulation tools.

## How to install

```shell
composer require osw3/php-cloud-manager
```

## Supported services

- [Dropbox](documentation/Dropbox.md)
- [FTP](documentation/FTP.md)

## How to use

### Create new connection

#### Prepare the DSN 

```php 
$dsn = "{$driver}://{$user}:{$pass}@{$host}";
```

####  Client instance
```php
use OSW3\CloudManager\Client;
$client = new Client($dsn);
```

##### Don't auto connect

```php 
$client = new Client($dsn, false);
$client->connect();
```

### DSN Infos

- `dsn(): DsnService`
    
    Bridge to DsnService.

    ```php
    $client->dsn()->getDriver(); // ftp
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

- `getAuth(): ?int`

    Get the Auth mode

    ```php
    $client->dsn()->getAuth();
    ```

- `getToken(): ?int`

    ```php
    $client->dsn()->getToken();
    ```

- `getPort(): ?int`

    ```php
    $client->dsn()->getPort(); // 21
    ```

- `get(): ?string`

    Get the DSN string

    ```php
    $client->dsn()->get(); // ftp://user:pass@site.com";
    ```

### Driver Statement

- `connect(): bool`

    Proceed to driver connection and return the connection state

    ```php
    $client->connect();
    ```

- `disconnect(): bool`

    Proceed to disconnection and return the connection status

    ```php
    $client->disconnect();
    ```

- `isConnected(): bool`

    return `true` when the driver is connected

    ```php
    $client->isConnected();
    ```

### Temp directory

- `setTempDirectory(string $directory): static`

    Set the local server Temp directory for file manipulation.

    ```php
    $client->setTempDirectory("my/temp/dir");
    ```

- `getTempDirectory(): string`

    Get the local server Temp directory for file manipulation.

    ```php
    $client->getTempDirectory();
    ```

### Navigation

- `location(): string`

    Return the current location (like PWD)

    ```php
    $client->location();
    ```

- `navigateTo(string $folder): bool`

    Set the pointer to a specific folder.

    ```php
    $client->navigateTo("/www");
    ```

- `parent(): bool`

    Set the pointer to a parent folder.

    ```php
    $client->parent();
    ```

- `root(): bool`

    Set the pointer to the root of the driver.

    ```php
    $client->root();
    ```

### Entry infos

- `infos(?string $path=null, ?string $part=null): array|string`

    Retrieve the entry infos. 
    Return an array if $part is null.

    ```php
    $client->infos("/www/my-dir"); // [...]
    $client->infos("/www/my-dir", "type"); // folder
    ```

- `isFolder(string $path): bool`

    True if $path is a folder type.

    ```php
    $client->isFolder("/www/my-dir");
    ```

- `isDirectory(string $path): bool`

    An alias of isFolder

    ```php
    $client->isDirectory("/www/my-dir");
    ```

- `isFile(string $path): bool`

    True if $path is a file type.

    ```php
    $client->isFile("/www/my-dir");
    ```

- `isLink(string $path): bool`

    True if $path is a link type.

    ```php
    $client->isLink("/www/my-dir");
    ```

- `isBlock(string $path): bool`

    True if $path is a block type

    ```php
    $client->isBlock("/www/my-dir");
    ```

- `isCharacter(string $path): bool`

    True if $path is a character type.

    ```php
    $client->isCharacter("/www/my-dir");
    ```

- `isSocket(string $path): bool`

    True if $path is a socket type.

    ```php
    $client->isSocket("/www/my-dir");
    ```

- `isPipe(string $path): bool`

    True if $path is a pipe type.

    ```php
    $client->isPipe("/www/my-dir");
    ```

### Permissions

- `permissions(string $path, ?int $code=null): string|bool`

    Permissions getter and setter.
    Set permission to path if `$code` is not `null`.
    Get permission code if `$code` is `null`

    ```php
    $client->permissions("/www/my-dir", 0777); // true
    $client->permissions("/www/my-dir"); // 0777
    ```

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

### Folder API

- `browse(?string $directory=null): array`

    Return the content of a directory, like the Unix PWD command.
    Return the current pointer location content if the `$directory` parameter is `null`.

    ```php 
    $client->browse("/www/my-dir"); // [...]
    ```

- `createFolder(string $directory, int $permissions=0700, bool $navigateTo = true): bool`

    Create a directory and set the pointer into the new location id `$navigateTo` is `true`.

    ```php 
    $client->createFolder("/www/my-dir/my-sub-dir");
    ```

- `deleteFolder(?string $directory, bool $recursive=true): bool`

    Delete a directory and its contains.

    ```php 
    $client->deleteFolder("/www/my-dir/my-sub-dir");
    ```

- `duplicateFolder(string $source, string $destination): bool`

    Duplicate a directory on the Client

    ```php 
    $client->duplicateFolder("/www/my-dir", "/www/my-other-dirt");
    ```

- `copyFolder(string $source, string $destination): bool`

    Alias for `duplicateFolder`.

- `moveFolder(string $source, string $destination, bool $override=false): bool`

    Move a folder (cut + paste)

    ```php 
    $client->moveFolder("C://my-dir", "/www/my-dir");
    ```

- `uploadFolder(string $source, string $destination, bool $override=false): bool`

    Send a directory from your server to the remote Client

    ```php 
    $client->uploadFolder("C://my-dir", "/www/my-dir");
    ```

- `downloadFolder(string $source, string $destination, bool $override=false): bool`

    Get a directory from a Client to your server

    ```php 
    $client->downloadFolder("/www/my-dir", "C://my-dir");
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

- `moveFile(string $source, string $destination, bool $override=false): bool`

    Move a file

    ```php 
    $client->moveFile("C://my-dir/my-file.txt", "/www/my-dir/my-file.txt");
    ```

- `uploadFile(string $source, string $destination, bool $override=false): bool`

    Send a file from your server to the remote Client

    ```php 
    $client->uploadFile("C://my-dir/my-file.txt", "/www/my-dir/my-file.txt");
    ```

- `downloadFile(string $source, string $destination, bool $override=false): bool`

    Get a file from a Client to your server

    ```php 
    $client->downloadFile("/www/my-dir/my-file.txt", "C://my-dir/my-file.txt");
    ```

### Folder & Files API (Hybrid aliases of previous API)

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

- `upload(string $source, string $destination, bool $override=false): bool`;

    Send a directory or a file from your server to the Client

    ```php 
    $client->upload("C://my-dir", "/www/my-dir");
    $client->upload("C://my-dir/my-file.txt", "/www/my-dir/my-file.txt");
    ``` 

- `download(string $source, string $destination, bool $override=false): bool`;

    Get a directory or a file from the Client to your server

    ```php 
    $client->download("/www/my-dir", "C://my-dir");
    $client->download("/www/my-dir/my-file.txt", "C://my-dir/my-file.txt");
    ``` 