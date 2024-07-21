<?php 
namespace OSW3\CloudManager\Drivers;

use OSW3\CloudManager\Enum\Metadata;
use OSW3\CloudManager\Enum\MetadataType;
use OSW3\CloudManager\Interfaces\DriverInterface;
use OSW3\CloudManager\Enum\DropboxRequestVisibility;

class DropboxDriver extends AbstractDriver implements DriverInterface
{
    const BASE_API = "https://api.dropboxapi.com/2/";
    const BASE_CONTENT = "https://content.dropboxapi.com/2/";

    /**
     * Virtual current directory
     *
     * @var string
     */
    private string $currentPath = "/";


    // Driver Statement
    // --

    public function connect(): bool
    {
        $witness = uniqid();
        $response = $this->dbxCheckUser($witness);

        if (isset($response->result) && $response->result === $witness)
        {
            $this->connection = true;
        }

        return $this->isConnected();
    }
    public function disconnect(): bool
    {
        if ($this->isConnected())
        {
            $this->dbxRevokeToken();
            $this->connection = false;
        }

        return $this->isConnected();
    }

    // Pointer location & navigation
    // --

    public function location(): ?string
    {
        if (!$this->isConnected()) {
            return null;
        }
        
        return $this->pwd();
    }
    public function navigateTo(string $directory): bool 
    {
        if (!$this->isConnected()) {
            return false;
        }
        
        return $this->cd($directory);
    }

    // Entry infos
    // --
    
    public function infos(?string $path=null, ?string $part=null): array|string|null
    {
        if (!$this->isConnected()) {
            return null;
        }

        if (!$path) $path = $this->pwd();

        $metadata = $this->dbxMetadata($path);

        if (isset($metadata->error)) {
            return null;
        }

        $metadata = json_decode(json_encode($metadata), true);
        $id       = $metadata['id'] ?? null;
        $type     = $metadata['.tag'] ?? null;
        $name     = $metadata['name'] ?? null;
        $path     = $metadata['path_lower'] ?? null;
        $size     = $metadata['size'] ?? null;
        $audience = $metadata['link_permissions']['effective_audience']['.tag'] ?? null;
        $level    = $metadata['link_permissions']['link_access_level']['.tag'] ?? null;
        $modified = $metadata['client_modified'] ?? null;

        $infos = array_merge(static::METADATA, [
            Metadata::ID->value       => $id,
            Metadata::TYPE->value     => $type,
            Metadata::NAME->value     => $name,
            Metadata::PATH->value     => $path,
            Metadata::SIZE->value     => $size,
            Metadata::AUDIENCE->value => $audience,
            Metadata::LEVEL->value    => $level,
            Metadata::MODIFIED->value => $modified,
        ]);

        if ($part == null)
        {
            return $infos;
        }

        return $infos[$part] ?? null;
    }
    public function permissions(string $path, null|string|int $mode=null): string|bool|null
    {
        if (!$this->isConnected()) {
            return false;
        }

        if ($mode === null)
        {
            $infos = $this->infos($path, Metadata::PERMISSIONS->value);

            return $infos;
        }

        if (in_array($mode, DropboxRequestVisibility::toArray()))
        {
            return $this->dbxChangeVisibility($path, $mode);
        }

        return false;
    }

    // Folder API
    // --
    
    public function browse(?string $directory=null): array
    {
        if (!$this->isConnected()) {
            return false;
        }

        if (!$directory) $directory = $this->pwd();

        $response = $this->dbxListFolder($directory);
        $entries = [];

        if (isset($response->entries)) foreach ($response->entries as $entry)
        {
            $metadata = json_decode(json_encode($entry), true);
            $id       = $metadata['id'] ?? null;
            $type     = $metadata['.tag'] ?? null;
            $name     = $metadata['name'] ?? null;
            $path     = $metadata['path_lower'] ?? null;
            $size     = $metadata['size'] ?? null;
            $audience = $metadata['link_permissions']['effective_audience']['.tag'] ?? null;
            $level    = $metadata['link_permissions']['link_access_level']['.tag'] ?? null;
            $modified = $metadata['client_modified'] ?? null;
    
            array_push($entries, array_merge(static::METADATA, [
                Metadata::ID->value       => $id,
                Metadata::TYPE->value     => $type,
                Metadata::NAME->value     => $name,
                Metadata::PATH->value     => $path,
                Metadata::SIZE->value     => $size,
                Metadata::AUDIENCE->value => $audience,
                Metadata::LEVEL->value    => $level,
                Metadata::MODIFIED->value => $modified,
            ]));
        }

        return $entries;
    }
    public function createFolder(string $folder, int $permission=0700, bool $navigateTo=true): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        $response = $this->dbxCreateFolder($folder);
        $isSuccess = isset($response->metadata);

        if ($isSuccess && $navigateTo)
        {
            $this->cd($folder);
        }

        return $isSuccess;
    }
    public function deleteFolder(?string $path, bool $recursive=true): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        if (!$this->isDir($path))
        {
            return false;
        }

        if ($recursive)
        {
            $list = $this->dbxListFolder($path);

            if (isset($list->entries)) foreach ($list->entries as $entry)
            {
                $entry = json_decode(json_encode($entry), true);

                if ($entry['.tag'] === MetadataType::FILE->value)
                {
                    $this->deleteFile($entry['path_lower']);
                }
                else if ($entry['.tag'] === MetadataType::FOLDER->value)
                {
                    $this->deleteFolder($entry['path_lower'], $recursive);
                }
            }
        }
        
        if ($this->isEmpty($path)) 
        {
            return $this->dbxDeleteFile($path);
        }

        return false;
    }
    public function duplicateFolder(string $source, string $destination): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->dbxCopyFile($source, $destination);
    }
    public function moveFolder(string $source, string $destination): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->dbxMoveFile($source, $destination);
    }
    public function uploadFolder(string $source, string $destination, bool $override=false): bool
    {
        $state = false;

        if (!$this->isConnected()) {
            return false;
        }

        if (is_dir($source))
        {
            $entries = $this->scandir($source);

            foreach ($entries as $entry)
            {
                $filename          = str_replace($source, "", $entry['path']);
                $entrySource       = preg_match("/\/$/", $source) ? substr($source, 0, -1) : $source;
                $entrySource      .= $filename;
                $entryDestination  = preg_match("/\/$/", $destination) ? substr($destination, 0, -1) : $destination;
                $entryDestination .= $filename;

                $state = match ($entry['type']) {
                    MetadataType::FOLDER->value => $this->createFolder($entryDestination),
                    MetadataType::FILE->value => $this->uploadFile($entrySource, $entryDestination, $override),
                    default => false
                };
                if (!$state) break;
            }
        }

        return $state;
    }
    public function downloadFolder(string $source, string $destination, bool $override=false): bool
    {
        $state = false;

        if (!$this->isConnected()) {
            return false;
        }

        $entries = $this->dbxListFolderRecursive($source);

        foreach ($entries as $entry)
        {
            $sourcePath = $entry['path_lower'];
            $sourceFile = str_replace($source, "", $sourcePath);

            $entryDestination  = preg_match("/\/$/", $destination) ? substr($destination, 0, -1) : $destination;
            $entryDestination .= $sourceFile;
            $entryPermissions =  0777;

            if ($entry['.tag'] === MetadataType::FOLDER->value && !is_dir($entryDestination))
            {
                $state = mkdir($entryDestination, $entryPermissions, true);
            }
            else if ($entry['.tag'] === MetadataType::FILE->value && !file_exists($entryDestination))
            {
                $state = $this->downloadFile($sourcePath, $entryDestination, $override);
            }

            if (!$state) break;
        }

        return $state;
    }

    // File API
    // --

    public function deleteFile(?string $filename): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        if (!$this->isFile($filename))
        {
            return false;
        }

        return $this->dbxDeleteFile($filename);
    }
    // TODO: 
    public function createFile(string $filename, string $content="", bool $binary=false): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        
        return false;
    }
    public function duplicateFile(string $source, string $destination): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->dbxCopyFile($source, $destination);
    }
    public function moveFile(string $source, string $destination): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        return $this->dbxMoveFile($source, $destination);
    }
    public function uploadFile(string $source, string $destination, bool $override=false): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        
        return $this->dbxUploadFile($source, $destination, $override);
    }
    public function downloadFile(string $source, string $destination, bool $override=false): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        $source = $this->resolvePath($source);
        $fileId = $this->infos($source, 'id');

        if ($fileId === null) {
            return false;
        }

        $content = $this->dbxDownloadFile($fileId);
        
        return file_put_contents($destination, $content);
    }

    // Folder & File both
    // --

    public function delete(?string $path, bool $recursive=true): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        
        return match ($this->infos( $path, 'type' )) 
        {
            MetadataType::FOLDER->value => $this->deleteFolder($path, $recursive),
            MetadataType::FILE->value => $this->deleteFile($path),
            default => false
        };
    }
    public function duplicate(string $source, string $destination): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        
        return match ($this->infos( $source, 'type' )) 
        {
            MetadataType::FOLDER->value => $this->duplicateFolder($source, $destination),
            MetadataType::FILE->value => $this->duplicateFile($source, $destination),
            default => false
        };
    }
    public function move(string $source, string $destination): bool
    {
        if (!$this->isConnected()) {
            return false;
        }
        
        return match ($this->infos( $source, 'type' )) 
        {
            MetadataType::FOLDER->value => $this->moveFolder($source, $destination),
            MetadataType::FILE->value => $this->moveFile($source, $destination),
            default => false
        };
    }
    public function upload(string $source, string $destination, bool $override=false): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        if (is_dir($source))
        {
            return $this->uploadFolder($source, $destination, $override);
        }
        else if (is_file($source))
        {
            return $this->uploadFile($source, $destination, $override);
        }
        
        return false;
    }
    public function download(string $source, string $destination, bool $override=false): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        if (preg_match("/\/$/", $source)) 
        {
            $source = substr($source, 0, -1);
        }

        return match($this->infos($source, 'type'))
        {
            MetadataType::FOLDER->value => $this->downloadFolder($source, $destination, $override),
            MetadataType::FILE->value => $this->downloadFile($source, $destination, $override),
            default => false
        };
    }








    private function requestHeaders()
    {
        $headers = [];

        switch ($this->getAuth())
        {
            case 'basic':
                $app_key    = $this->getUser();
                $app_secret = $this->getPass();
                $headers    = ["Authorization" => "Basic {$app_key}:{$app_secret}"];
            break;

            case 'token':
                $access_token = $this->getToken();
                $headers      = ["Authorization" => "Bearer $access_token"];
            break;
        }

        return $headers;
    }
    private function resolvePath(string $path): string
    {
        if (!preg_match("/^\//", $path))
        {
            $current = $this->currentPath;

            if (preg_match("/\/$/", $current))
            {
                $current = substr($current, 0, -1);
            }

            $path = $current."/".$path;
        }

        return $path;
    }








    private function pwd(): string|false
    {
        if (!$this->isConnected())
        {
            return false;
        }

        return empty($this->currentPath) ? "/" : $this->currentPath;
    }
    private function cd(string $directory): bool
    {
        if ($directory === "/") 
        {
            $this->currentPath = "/";

            return true;
        }

        if ($directory === "./") 
        {
            return true;
        }

        if ($directory === "../") 
        {
            $paths = explode("/", $this->currentPath);
            array_pop($paths);

            $this->currentPath = implode("/", $paths);

            return true;
        }

        $directory = $this->resolvePath($directory);
        if ($this->isDir($directory))
        {
            $this->currentPath = $directory;
            return true;
        }

        return false;
    }
    private function isDir(string $directory): bool
    {
        $dbx = $this->dbxListFolder($directory);
        return isset($dbx->entries);
    }
    private function isFile(string $path): bool
    {
        $data = $this->dbxMetadata($path);
        $data = json_decode(json_encode($data), true);

        return isset($data['.tag']) && $data['.tag'] === MetadataType::FILE->value;
    }
    private function isEmpty(string $path): bool
    {
        $response = $this->dbxListFolder($path);

        return isset($response->entries) && empty($response->entries);
    }

    private function dbxCheckUser(string $query)
    {
        $endpoint = self::BASE_API . "check/user";
        $data = ["query" => $query];

        return $this->fetchPost($endpoint, $data, $this->requestHeaders());
    }
    private function dbxRevokeToken()
    {
        $endpoint = self::BASE_API . "auth/token/revoke";
        return $this->fetchPost($endpoint, [], $this->requestHeaders());
    }
    private function dbxListFolder(string $path)
    {
        $endpoint = self::BASE_API . "files/list_folder";

        $data = [
            "path"      => $this->resolvePath($path),
            "recursive" => false,
        ];

        return $this->fetchPost(
            $endpoint, 
            $data, 
            $this->requestHeaders()
        );
    }
    private function dbxListFolderRecursive(string $source, array $entries=[])
    {
        $dbx = $this->dbxListFolder($source);
        $dbx = json_decode(json_encode($dbx), true);

        if (isset($dbx['entries']))
        {
            foreach ($dbx['entries'] as $entry)
            {
                $entries = array_merge($entries, [$entry['path_lower'] => $entry]);

                if ($entry['.tag'] === MetadataType::FOLDER->value)
                {
                    $entries = $this->dbxListFolderRecursive($entry['path_lower'], $entries);
                }
            }
        }

        return $entries;
    }
    private function dbxMetadata(string $path)
    {
        $response = $this->dbxGetSharedInfo($path);

        if ($response === null) 
        {
            $endpoint = self::BASE_API . "files/get_metadata";
    
            $data = [
                "path"                                => $this->resolvePath($path),
                // "include_deleted"                     => false,
                // "include_has_explicit_shared_members" => false,
                // "include_media_info"                  => false,
            ];
    
            $response = $this->fetchPost(
                $endpoint, 
                $data, 
                $this->requestHeaders()
            );
        }
        
        return $response;
    }
    private function dbxChangeVisibility(string $path, string $visibility)
    {
        return match($visibility)
        {
            DropboxRequestVisibility::PUBLIC->value => $this->dbxCreateSharedLink($path, $visibility),
            DropboxRequestVisibility::PRIVATE->value => $this->dbxRevokeSharedLink($path),
            default => false
        };
    }
    private function dbxCreateSharedLink(string $path, string $visibility)
    {
        $endpoint = self::BASE_API . "sharing/create_shared_link_with_settings";

        $data = [
            "path"     => $this->resolvePath($path),
            "settings" => ["requested_visibility" => $visibility]
        ];
        
        $response = $this->fetchPost(
            $endpoint, 
            $data, 
            $this->requestHeaders()
        );

        return !isset($response->error);
    }
    private function dbxRevokeSharedLink(string $path): bool
    {
        $endpoint = self::BASE_API . "sharing/revoke_shared_link";

        $info = $this->dbxGetSharedInfo($path);

        if (isset($info->url) && isset($info->link_permissions->can_revoke) && $info->link_permissions->can_revoke == 1)
        {
            $data = [
                "url" => $info->url,
            ];
    
            $response = $this->fetchPost(
                $endpoint, 
                $data, 
                $this->requestHeaders()
            );
            
            return !isset($response->error);
        }

        return false;
    }
    private function dbxGetSharedInfo(string $path): object|null
    {
        $endpoint = self::BASE_API . "sharing/list_shared_links";

        $data = [
            "path" => $this->resolvePath($path),
        ];

        $response = $this->fetchPost(
            $endpoint, 
            $data, 
            $this->requestHeaders()
        );

        return isset($response->links[0])
            ? $response->links[0]
            : null
        ;
    }
    private function dbxCreateFolder(string $path)
    {
        $endpoint = self::BASE_API . "files/create_folder_v2";

        $data = [
            "path" => $this->resolvePath($path),
            "autorename" => false
        ];

        return $this->fetchPost(
            $endpoint, 
            $data, 
            $this->requestHeaders()
        );
    }
    private function dbxDeleteFile(string $path): bool
    {
        $endpoint = self::BASE_API . "files/delete_v2";

        $data = [
            "path" => $this->resolvePath($path),
        ];

        $response = $this->fetchPost(
            $endpoint, 
            $data, 
            $this->requestHeaders()
        );

        return isset($response->metadata);
    }
    private function dbxCopyFile(string $source, string $destination): bool
    {
        $endpoint = self::BASE_API . "files/copy_v2";

        $data = [
            "from_path" => $this->resolvePath($source),
            "to_path" => $this->resolvePath($destination)
        ];

        $response = $this->fetchPost(
            $endpoint, 
            $data, 
            $this->requestHeaders()
        );

        return isset($response->metadata);
    }
    private function dbxMoveFile(string $source, string $destination): bool
    {
        $endpoint = self::BASE_API . "files/move_v2";

        $data = [
            "from_path" => $this->resolvePath($source),
            "to_path" => $this->resolvePath($destination)
        ];

        $response = $this->fetchPost(
            $endpoint, 
            $data, 
            $this->requestHeaders()
        );

        return isset($response->metadata);
    }
    private function dbxUploadFile(string $source, string $destination, bool $override=false): bool
    {
        $endpoint = self::BASE_CONTENT . "files/upload";

        $args = [
            "path"            => $destination,
            "mode"            => $override ? "overwrite" : "add",
            "autorename"      => false,
            "mute"            => false,
            "strict_conflict" => true
        ];

        $headers = array_merge($this->requestHeaders(), [
            "Content-Type"    => "application/octet-stream",
            "Dropbox-API-Arg" => json_encode($args),
        ]);

        $data = file_get_contents($source);

        $response = $this->fetchPost(
            $endpoint, 
            $data, 
            $headers
        );

        // print_r($response);

        return !isset($response->error);
    }
    private function dbxDownloadFile(string $source): ?string
    {
        $endpoint = self::BASE_CONTENT . "files/download";

        $args = [
            "path" => $source,
        ];

        $headers = array_merge($this->requestHeaders(), [
            "Content-Type"    => null,
            "Dropbox-API-Arg" => json_encode($args),
        ]);

        $response = $this->fetch(
            $endpoint, 
            'GET',
            [], 
            $headers,
            true
        );

        // print_r($response);

        return $response;
    }
}