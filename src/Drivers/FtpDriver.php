<?php 
namespace OSW3\CloudManager\Drivers;

use OSW3\CloudManager\Enum\Metadata;
use OSW3\CloudManager\Enum\MetadataType;
use OSW3\CloudManager\Drivers\AbstractDriver;
use OSW3\CloudManager\Interfaces\DriverInterface;

class FtpDriver extends AbstractDriver implements DriverInterface
{
    private bool $isPassive = false;


    // Driver Statement
    // --

    public function connect(): bool
    {
        $connection = null;
        $credential = null;

        if (!$this->isConnected())
        {
            $connection = @ftp_connect($this->getHost(), $this->getPort());
            $this->setActiveMode();

            if ($connection) $credential = @ftp_login(
                $connection, 
                $this->getUser(), 
                $this->getPass()
            );
            
            if (!$credential)
            {
                $this->disconnect();
            }
        }

        $this->connection = $connection;

        return $this->isConnected();
    }
    public function disconnect(): bool
    {
        if ($this->isConnected())
        {
            $this->connection = !ftp_close( $this->connection );
        }

        return $this->isConnected();
    }

    // Pointer location & navigation
    // --

    public function location(): ?string 
    {
        return $this->pwd();
    }
    public function navigateTo(string $directory): bool 
    {
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
        
        $infos = null;

        // Get path parent directory
        $parent = explode("/", $path);
        array_pop($parent);
        $parent = implode("/", $parent);

        if (empty($parent)){
            $parent = "/";
        }

        // Get parent directory contents
        $contents = $this->ls($parent);
        
        // find path
        $find = false;
        foreach ($contents as $infos)
        {
            if ($infos[Metadata::PATH->value] === $path) {
                $find = true;
                break;
            }
        }

        if ($part == null && $find)
        {
            return $infos;
        }

        return $infos[$part] ?? null;
    }
    public function permissions(string $path, null|string|int $mode = null): string|bool|null
    {
        if (!$this->isConnected()) {
            return false;
        }

        if ($mode === null)
        {
            $infos = $this->infos($path, Metadata::PERMISSIONS->value);

            return $infos;
        }

        return $this->chmod($mode, $path);
    }

    // Folder API
    // --

    public function browse(?string $directory=null): array 
    {
        return $this->ls($directory);
    }
    public function createFolder(string $directory, int $permissions=0700, bool $navigateTo = true): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        $proceed = $this->mkdir($directory, $permissions);

        if ($proceed['code'] === 0 && $navigateTo)
        {
            $this->cd($directory);
        }

        return $proceed['code'] === 0;
    }
    public function deleteFolder(?string $directory, bool $recursive=true): bool
    {
        return $this->rmdir($directory, $recursive);
    }
    public function duplicateFolder(string $source, string $destination): bool
    {
        $state = false;
        $checker = [];

        if (!$this->isConnected()) {
            return false;
        }

        if (!$this->isDir($source))
        {
            return false;
        }

        $entries = $this->getRecursiveFolderEntries($source, $destination);

        foreach ($entries as $entry)
        {
            if ($entry[0] === MetadataType::FOLDER->value)
            {
                $state = $this->createFolder($entry[2]);
            }
            else if ($entry[0] === MetadataType::FILE->value)
            {
                $state = $this->duplicateFile($entry[1], $entry[2]);
            }

            if (!$state) break;
        }

        return $state;
    }
    public function moveFolder(string $source, string $destination): bool
    {
        $state = false;

        if ($this->isConnected() && $this->isDir($source))
        {
            if ($this->duplicateFolder($source, $destination))
            {
                $state = $this->deleteFolder($source);
            }
        }

        return $state;
    }
    public function uploadFolder(string $source, string $destination, bool $override=false): bool
    {
        $state = false;
        $checker = [];

        if ($this->isConnected() && is_dir($source))
        {
            $content = scandir($source);

            foreach ($content as $entry)
            {
                if ($entry != '.' && $entry != '..')
                {
                    $entrySource       = $source."/".$entry;
                    $entryDestination  = preg_match("/\/$/", $destination) ? $destination : $destination."/";
                    $entryDestination .= $entry;

                    array_push($checker, $entryDestination);
                    
                    if (is_dir($entrySource))
                    {
                        $this->createFolder($entryDestination); // true / false
                        $this->uploadFolder($entrySource, $entryDestination);
                    }
                    else if (is_file($entrySource))
                    {
                        $this->uploadFile($entrySource, $entryDestination, $override);
                    }
                }
            }

            foreach ($checker as $entry)
            {
                $state = !!$this->infos($entry);

                if (!$state) break;
            }
        }

        return $state;
    }
    public function downloadFolder(string $source, string $destination, bool $override=false): bool
    {
        $state = false;
        $checker = [];

        if (!$this->isConnected()) {
            return false;
        }

        if (!$this->isDir($source)) {
            return false;
        }

        if (!is_dir($destination) || $override)
        {
            foreach ($this->browse($source) as $entry)
            {
                $entrySource       = preg_match("/\/$/", $source) ? $source : $source."/";
                $entrySource      .= $entry['name'];
                $entryDestination  = preg_match("/\/$/", $destination) ? $destination : $destination."/";
                $entryDestination .= $entry['name'];
                $entryPermissions =  0700; //$entry['permissions']['codes'];

                array_push($checker, $entryDestination);

                if ($entry['type'] === MetadataType::FOLDER->value)
                {
                    if (!is_dir($entryDestination))
                    {
                        mkdir($entryDestination, $entryPermissions, true);
                    }
                    $this->downloadFolder($entrySource, $entryDestination, $override);
                }
                else if ($entry['type'] === 'file')
                {
                    $this->downloadFile($entrySource, $entryDestination, $override); // true / false
                }
            }
        }

        foreach ($checker as $entry)
        {
            $state = !!$this->infos($entry);

            if (!$state) break;
        }

        return $state;
    }

    // File API
    // --

    public function createFile(string $filename, string $content="", bool $binary=false): bool 
    {
        $state = false;

        if (!$this->isConnected()) {
            return false;
        }

        if ($this->type($filename) === MetadataType::FILE->value) {
            return false;
        }

        $mode = $binary ? FTP_BINARY : FTP_ASCII;

        // Create parent directory if don't exists
        $d = explode("/", $filename);
             array_pop($d);
        $d = implode("/", $d);

        $this->createFolder($d);

        $temp = fopen('php://temp', 'r+');
                fwrite($temp, $content);
                fseek($temp, 0, SEEK_END);
        $filesize = ftell($temp);

        $this->setPassiveMode();
        $this->allocateSpace($filesize);
        $state = ftp_fput($this->connection, $filename, $temp, $mode);
        $this->setActiveMode();

        fclose($temp);

        return $state;
    }
    public function deleteFile(?string $filename): bool
    {
        return $this->rm($filename);
    }
    public function duplicateFile(string $source, string $destination): bool
    {
        $state = false;

        if (!$this->isConnected()) {
            return false;
        }

        $sourceExists = $this->type($source) === MetadataType::FILE->value;
        $destinationExists = $this->type($destination) === 'unknown';
        
        if ($sourceExists && $destinationExists)
        {
            // Create parent directory if don't exists
            $destinationParentDirectory = explode("/", $destination);
            array_pop($destinationParentDirectory);
            $destinationParentDirectory = implode("/", $destinationParentDirectory);
            
            $this->createFolder($destinationParentDirectory);
            $this->setPassiveMode();

            $temp_file = $this->getLocalTempFilename();

            // $mode = FTP_ASCII;
            $mode = FTP_BINARY;
            if (@ftp_get($this->connection, $temp_file, $source, $mode))
            {
                $state = @ftp_put($this->connection, $destination, $temp_file, $mode);
                unlink($temp_file);
            }

            $this->setActiveMode();
        }

        return $state;
    }
    public function moveFile(string $source, string $destination): bool
    {
        $state = false;

        if ($this->isConnected() && $this->type($source) === MetadataType::FILE->value)
        {
            if ($this->duplicateFile($source, $destination))
            {
                $state = $this->deleteFile($source);
            }
        }

        return $state;
    }
    public function uploadFile(string $source, string $destination, bool $override=false): bool
    {
        $state = false;

        if ($this->isConnected() && is_file($source))
        {
            if ($this->type($destination) !== MetadataType::FILE->value || $override)
            {
                // Create parent directory if don't exists
                $destinationParentDirectory = explode("/", $destination);
                array_pop($destinationParentDirectory);
                $destinationParentDirectory = implode("/", $destinationParentDirectory);
                
                $this->createFolder($destinationParentDirectory);

                $this->setPassiveMode();
                $state = ftp_put($this->connection, $destination, $source);
                $this->setActiveMode();
            }
        }

        return $state;
    }
    public function downloadFile(string $source, string $destination, bool $override=false): bool
    {
        $state = false;

        if (!$this->isConnected()) {
            return false;
        }

        if ($this->type($source) !== MetadataType::FILE->value) {
            return false;
        }

        if (!file_exists($destination) || $override)
        {
            $this->setPassiveMode();
            $state = ftp_get($this->connection, $destination, $source);
            $this->setActiveMode();
        }

        return $state;
    }

    // Folder & File both
    // --

    public function delete(?string $path, bool $recursive=true): bool
    {
        return match($this->infos($path, 'type'))
        {
            MetadataType::FOLDER->value => $this->deleteFolder($path, $recursive),
            MetadataType::FILE->value => $this->deleteFolder($path, $recursive),
            default => false
        };
    }
    public function duplicate(string $source, string $destination): bool
    {
        return match($this->infos($source, 'type'))
        {
            MetadataType::FOLDER->value => $this->duplicateFolder($source, $destination),
            MetadataType::FILE->value => $this->duplicateFile($source, $destination),
            default => false
        };
    }
    public function move(string $source, string $destination): bool
    {
        return match($this->infos($source, 'type'))
        {
            MetadataType::FOLDER->value => $this->moveFolder($source, $destination),
            MetadataType::FILE->value => $this->moveFile($source, $destination),
            default => false
        };
    }
    public function upload(string $source, string $destination, bool $override=false): bool
    {
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

    // Driver File System
    // --

    private function pwd(): string|false
    {
        if (!$this->isConnected())
        {
            return false;
        }

        return ftp_pwd( $this->connection );
    }
    private function cd(string $directory): bool
    {
        if ($directory === "./") 
        {
            return true;
        }

        if ($directory === "../") 
        {
            return ftp_cdup($this->connection);
        }
        
        if ($this->isDir($directory))
        {
            return ftp_chdir($this->connection, $directory);
        }

        return false;
    }
    private function isDir(string $directory): bool
    {
        $current = $this->pwd();

        if (@ftp_chdir($this->connection, $directory)) 
        {
            ftp_chdir($this->connection, $current);
            return true;
        } 

        return false;
    }
    private function ls(?string $directory=null): array
    {
        $infos = [];
        $data = [];

        if ($this->isConnected())
        {
            if (!$directory) $directory = $this->pwd();

            $this->setPassiveMode();
            $data = ftp_rawlist($this->connection, $directory);
            $this->setActiveMode();

            // $infos = $this->parseList($metadata, $directory);
        }

        if ($data) foreach ($data as $metadata)
        {
            // Data Extraction
            // --

            $pattern = '/^([drwx-]+)\s+(\d+)\s+(\S+)\s+(\S+)\s+(\d+)\s+(\w{3}\s+\d{1,2}\s+(?:\d{2}:\d{2}|\d{4}))\s+(.+)$/';

            preg_match($pattern, $metadata, $matches);
            $column_1 = $matches[1]; // Extract file type and permissions
            $column_2 = $matches[2]; // Extract $entry nodes / links
            $column_3 = $matches[3]; // Extract Owner name
            $column_4 = $matches[4]; // Extract Group name
            $column_5 = $matches[5]; // Extract Filesize
            $column_6 = $matches[6]; // Extract Date
            $column_7 = $matches[7]; // Extract Filename

            // Parse Data
            // --

            // Type
            $type = match(substr($column_1, 0, 1)) 
            {
                "-" => "file",
                "d" => "folder",
                "l" => "link",
                "b" => "block",
                "c" => "character",
                "s" => "socket",
                "p" => "pipe",
            };

            // Permissions
            $permissions = $this->parsePermissionsCode([
                'owner' => [
                    'read'  => substr($column_1, 1, 1) === "r",
                    'write' => substr($column_1, 2, 1) === "w",
                    'exec'  => substr($column_1, 3, 1) === "x",
                ],
                'group' => [
                    'read'  => substr($column_1, 4, 1) === "r",
                    'write' => substr($column_1, 5, 1) === "w",
                    'exec'  => substr($column_1, 6, 1) === "x",
                ],
                'anon' => [
                    'read'  => substr($column_1, 7, 1) === "r",
                    'write' => substr($column_1, 8, 1) === "w",
                    'exec'  => substr($column_1, 9, 1) === "x",
                ],
            ]);

            // Owner
            $owner = $column_3;

            // Group
            $group = $column_4;

            // Nodes
            $nodes = $column_2;

            // File date
            $date = (new \DateTime($column_6))->format('Y-m-d\TH:i:s\Z');

            // File size
            $filesize = $column_5;

            // File name and Path
            $filename = $column_7;
            $filepath = "{$directory}/{$filename}";
            $filepath = str_replace("//", "/", $filepath);

            array_push($infos, array_merge(static::METADATA, [
                Metadata::TYPE->value        => $type,
                Metadata::NAME->value        => $filename,
                Metadata::PATH->value        => $filepath,
                Metadata::SIZE->value        => $filesize,
                Metadata::OWNER->value       => $owner,
                Metadata::GROUP->value       => $group,
                Metadata::PERMISSIONS->value => $permissions,
                Metadata::NODES->value       => $nodes,
                Metadata::MODIFIED->value    => $date,
            ]));
        }
        
        return $infos;
    }
    private function type(string $path): string
    {
        if ($this->isDir($path))
        {
            return MetadataType::FOLDER->value;
        }
        
        // Get current directory
        // $current = $this->pwd();


        $infos = $this->infos($path);

        if (!empty($infos))
        {
            return $infos['type'];
        }

        return "unknown";
    }
    private function rmdir(string $directory, bool $recursive=false): bool
    {
        if ($this->isConnected())
        {
            // $current = $this->pwd();

            // Exit false if $directory don't exist
            if (!$this->isDir($directory))
            {
                return false;
            }

            // If directory i not empty
            if ($recursive)
            {
                foreach ($this->ls($directory) as $entry)
                {
                    if ($entry['type'] === MetadataType::FILE->value)
                    {
                        $this->rm($entry['path']);
                    }
                    else if ($entry['type'] === MetadataType::FOLDER->value)
                    {
                        $this->rmdir($entry['path'], $recursive);
                    }
                }

                if (!empty($this->ls($directory)))
                {
                    $this->rmdir($directory, $recursive);
                }
            }

            // If directory is empty -> remove
            if (empty($this->ls($directory))) 
            {
                // print_r(" EMPTY -> REMOVE <br>");
                return ftp_rmdir($this->connection, $directory);
            }
        }

        return false;
    }
    private function rm(string $filename): bool
    {
        if ($this->isConnected())
        {
            return @ftp_delete($this->connection, $filename);
        }

        return false;
    }
    private function mkdir(string $directory, int $permissions=0700): array
    {
        // 0 = success
        // 1 = failed
        // 2 = directory already exists
        // 9 = not executed
        
        if ($this->isConnected())
        {
            $current = $this->pwd();

            // Directory already exists
            if ($this->isDir($directory))
            {
                return [
                    'created' => false,
                    'code' => 2,
                    'message' => "directory already exists"
                ];
            }

            // Is absolute path
            if (preg_match("/^\//", $directory))
            {
                $this->cd("/"); // back to root
                $directory = substr($directory, 1);
            }

            $paths = explode("/", $directory);
            $total = count($paths);
            $released = 0;

            while (!empty($paths))
            {
                $path = array_shift($paths);
                // $lastKey = empty($paths);

                if ($this->isDir($path) || @ftp_mkdir($this->connection, $path))
                {
                    $released++;
                }

                $this->cd($path);
            }

            $isCreated = $total == $released;
            
            if ($isCreated)
            {
                // $this->chmod($permissions, $directory);
                $this->chmod($permissions, $this->pwd());
            }

            // Restore current directory
            $this->cd($current);

            if ($isCreated)
            {
                return [
                    'created' => true,
                    'code' => 0,
                    'message' => "success"
                ];
            }
            else 
            {
                return [
                    'created' => false,
                    'code' => 1,
                    'message' => "failed"
                ];
            }
        }

        return [
            'created' => false,
            'code' => 9,
            'message' => "not executed"
        ];
    }
    private function chmod(string|int $permissions, string $filename): bool
    {
        if ($this->isConnected())
        {
            if (is_string($permissions)) {
                $permissions = octdec($permissions);
            }
            
            return ftp_chmod($this->connection, $permissions, $filename);
        }

        return false;
    }
    private function allocateSpace(int $size): bool
    {
        if ($this->isConnected())
        {
            return ftp_alloc($this->connection, $size);
        }

        return false;
    }

    private function isPassiveMode(): bool
    {
        return $this->isPassive === true;
    }
    private function isActiveMode(): bool
    {
        return $this->isPassive === false;
    }
    private function getMode(): string 
    {
        return $this->isPassive ? 'passive' : 'active';
    }
    private function toggleMode(): static 
    {
        $this->isPassive 
            ? $this->setActiveMode()
            : $this->setPassiveMode()
        ;
        return $this;
    }
    private function setActiveMode(): static
    {
        if ($this->isConnected())
        {
            $this->isPassive = !ftp_pasv($this->connection, false);
        }

        return $this;
    }
    private function setPassiveMode(): static
    {
        if ($this->isConnected())
        {
            $this->isPassive = ftp_pasv($this->connection, true);
        }

        return $this;
    }









    private function parsePermissionsCode($permissions)
    {
        return  "0".$this->parsePermissionsPart($permissions, 'owner').
                $this->parsePermissionsPart($permissions, 'group').
                $this->parsePermissionsPart($permissions, 'anon');
    }
    private function parsePermissionsPart(array $permissions, string $part)
    {
        if (!$permissions[$part]['read'] && !$permissions[$part]['write'] && !$permissions[$part]['exec'])
        {
            $code = 0;
        }
        else if (!$permissions[$part]['read'] && !$permissions[$part]['write'] && $permissions[$part]['exec'])
        {
            $code = 1;
        }
        else if (!$permissions[$part]['read'] && $permissions[$part]['write'] && !$permissions[$part]['exec'])
        {
            $code = 2;
        }
        else if (!$permissions[$part]['read'] && $permissions[$part]['write'] && $permissions[$part]['exec'])
        {
            $code = 3;
        }
        else if ($permissions[$part]['read'] && !$permissions[$part]['write'] && !$permissions[$part]['exec'])
        {
            $code = 4;
        }
        else if ($permissions[$part]['read'] && !$permissions[$part]['write'] && $permissions[$part]['exec'])
        {
            $code = 5;
        }
        else if ($permissions[$part]['read'] && $permissions[$part]['write'] && !$permissions[$part]['exec'])
        {
            $code = 6;
        }
        else if ($permissions[$part]['read'] && $permissions[$part]['write'] && $permissions[$part]['exec'])
        {
            $code = 7;
        }

        return $code;
    }
    private function getRecursiveFolderEntries(string $source, string $destination, array $entries=[]): array
    {
        $entries = array_merge($entries, [$source => [
            MetadataType::FOLDER->value,
            $source,
            $destination
        ]]);

        foreach ($this->browse($source) as $entry)
        {
            $entrySource       = preg_match("/\/$/", $source) ? $source : $source."/";
            $entrySource      .= $entry['name'];
            $entryDestination  = preg_match("/\/$/", $destination) ? $destination : $destination."/";
            $entryDestination .= $entry['name'];

            $entries = array_merge($entries, [$entrySource => [
                $entry['type'],
                $entrySource,
                $entryDestination
            ]]);

            if ($entry['type'] === MetadataType::FOLDER->value)
            {
                $entries = $this->getRecursiveFolderEntries($entrySource, $entryDestination, $entries);
            }
        }

        return $entries;
    }
}