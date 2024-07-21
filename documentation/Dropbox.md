# Dropbox Driver

## Crate Dropbox App

### Settings your App

1. Go to [Dropbox developers homepage](https://www.dropbox.com/developers)
2. Open `App Console`
3. Create a new App
    - Select "Scoped access"
    - Select the type of access
    - Choose your App name
4. Open the App settings page and select `Permissions` tab to select scope. Remanded scope:
    - files.metadata.write
    - files.metadata.read
    - files.content.write
    - files.content.read
    - sharing.write
    - sharing.read
5. Select `settings` tabs to `Generate access token`



## Create a Client

### Prepare DSN

```php 
$dropbox_access_token = "xxxxxxxx";
$dsn = "dropbox:token://{$dropbox_access_token}";
```

### Connection

```php
use OSW3\CloudManager\Client;
$client = new Client($dsn);
```

## API Methods

Use methods of the [Client API](../readme.md)