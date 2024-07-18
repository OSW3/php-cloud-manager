<?php 
namespace OSW3\CloudManager\Drivers;

use OSW3\CloudManager\Drivers\AbstractDriver;
use OSW3\CloudManager\Interfaces\DriverInterface;

class FtpDriver extends AbstractDriver implements DriverInterface
{
    private bool $isPassive = false;

    // Driver
    // --

    /**
     * Proceed to driver connection
     *
     * @return static
     */
    public function connect(): static
    {
        if (!$this->isConnected())
        {
            $this->connection = @ftp_connect($this->getHost(), $this->getPort());
            $this->setActiveMode();
        }

        return $this;
    }

    /**
     * Proceed to driver disconnection
     *
     * @return static
     */
    public function disconnect(): static
    {
        if ($this->isConnected())
        {
            $this->connection = !ftp_close( $this->connection );

            if (!$this->connection)
            {
                $this->credential = false;
            }
        }

        return $this;
    }

    /**
     * User authentication
     *
     * @return static
     */
    public function authenticate(): static 
    {
        if ($this->connection)
        {
            $this->credential = @ftp_login(
                $this->connection, 
                $this->getUser(), 
                $this->getPass()
            );
        }

        if (!$this->credential)
        {
            $this->disconnect();
        }

        return $this;    
    }


    // Client File System
    // --

    // Pointer location & navigation

    public function location(): ?string 
    {
        return $this->pwd();
    }
    public function navigateTo(string $directory): bool 
    {
        return $this->cd($directory);
    }

    // File types

    public function isDirectory(string $path) :bool 
    {
        return $this->type($path) === 'directory';
    }
    public function isFile(string $path) :bool 
    {
        return $this->type($path) === 'file';
    }
    public function isLink(string $path) :bool 
    {
        return $this->type($path) === 'link';
    }
    public function isBlock(string $path) :bool 
    {
        return $this->type($path) === 'block';
    }
    public function isCharacter(string $path) :bool 
    {
        return $this->type($path) === 'character';
    }
    public function isSocket(string $path) :bool 
    {
        return $this->type($path) === 'socket';
    }
    public function isPipe(string $path) :bool 
    {
        return $this->type($path) === 'pipe';
    }

    // File infos

    public function infos(?string $path=null, ?string $part=null): array|string
    {
        if (!$this->isConnected() || !$this->hasCredential()) {
            return [];
        }

        if (!$path) $path = $this->pwd();

        // Get path parent directory
        $parent = explode("/", $path);
        array_pop($parent);
        $parent = implode("/", $parent);

        if (empty($parent))
        {
            $parent = "/";
        }

        // Get parent directory contents
        $contents = $this->ls($parent);
        
        // find path
        foreach ($contents as $entry)
        {
            if ($entry['filepath'] === $path)
            {
                switch ($part)
                {
                    case 'permissions':
                        return $entry['permissions']['code'];
                    break;
                    case 'size':
                        return $entry['filesize'];
                    break;
                    case 'owner':
                        return $entry['owner'];
                    break;
                    case 'group':
                        return $entry['group'];
                    break;
                    case 'nodes':
                        return $entry['nodes'];
                    break;
                    case 'type':
                        return $entry['type'];
                    break;

                    default: return $entry;
                }
            }
        }

        return [];
    }
    public function permissions(string $path, ?int $code = null): string|bool
    {
        if ($code === null)
        {
            return $this->infos($path, 'permissions');
        }

        return $this->chmod($code, $path);
    }

    // Directory

    public function directoryList(?string $directory=null): array 
    {
        return $this->ls($directory);
    }
    public function createDirectory(string $directory, int $permissions=0700, bool $navigateTo = true): bool
    {
        $proceed = $this->mkdir($directory, $permissions);

        if ($proceed['code'] === 0 && $navigateTo)
        {
            $this->cd($directory);
        }

        return $proceed['code'] === 0;
    }
    public function deleteDirectory(?string $directory, bool $recursive=true): bool
    {
        return $this->rmdir($directory, $recursive);
    }
    public function duplicateDirectory(string $source, string $destination): bool
    {
        $state = false;
        $checker = [];

        if (!$this->isConnected() || !$this->hasCredential()) {
            return false;
        }

        if (!$this->isDir($source))
        {
            return false;
        }

        $this->createDirectory($destination); // true / false

        foreach ($this->directoryList($source) as $entry)
        {
            $entrySource       = preg_match("/\/$/", $source) ? $source : $source."/";
            $entrySource      .= $entry['filename'];
            $entryDestination  = preg_match("/\/$/", $destination) ? $destination : $destination."/";
            $entryDestination .= $entry['filename'];

            array_push($checker, $entryDestination);

            if ($entry['type'] === 'directory')
            {
                $this->duplicateDirectory($entrySource, $entryDestination);
            }
            else if ($entry['type'] === 'file')
            {
                $this->duplicateFile($entrySource, $entryDestination); // true / false
            }
        }

        foreach ($checker as $entry)
        {
            $state = !!$this->infos($entry);

            if (!$state) break;
        }

        return $state;
    }
    public function moveDirectory(string $source, string $destination): bool
    {
        $state = false;

        if ($this->isConnected() && $this->hasCredential() && $this->isDir($source))
        {
            if ($this->duplicateDirectory($source, $destination))
            {
                $state = $this->deleteDirectory($source);
            }
        }

        return $state;
    }
    public function sendDirectory(string $source, string $destination, bool $override=false): bool
    {
        $state = false;
        $checker = [];

        if ($this->isConnected() && $this->hasCredential() && is_dir($source))
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
                        $this->createDirectory($entryDestination); // true / false
                        $this->sendDirectory($entrySource, $entryDestination);
                    }
                    else if (is_file($entrySource))
                    {
                        $this->sendFile($entrySource, $entryDestination, $override);
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
    public function getDirectory(string $source, string $destination, bool $override=false): bool
    {
        $state = false;
        $checker = [];

        if (!$this->isConnected() || !$this->hasCredential()) {
            return false;
        }

        if (!$this->isDir($source)) {
            return false;
        }

        if (!is_dir($destination) || $override)
        {
            foreach ($this->directoryList($source) as $entry)
            {
                $entrySource       = preg_match("/\/$/", $source) ? $source : $source."/";
                $entrySource      .= $entry['filename'];
                $entryDestination  = preg_match("/\/$/", $destination) ? $destination : $destination."/";
                $entryDestination .= $entry['filename'];
                $entryPermissions =  0700; //$entry['permissions']['codes'];

                array_push($checker, $entryDestination);

                if ($entry['type'] === 'directory')
                {
                    if (!is_dir($entryDestination))
                    {
                        mkdir($entryDestination, $entryPermissions, true);
                    }
                    $this->getDirectory($entrySource, $entryDestination, $override);
                }
                else if ($entry['type'] === 'file')
                {
                    $this->getFile($entrySource, $entryDestination, $override); // true / false
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

    // File

    public function createFile(string $filename, string $content="", bool $binary=false): bool 
    {
        $state = false;

        if (!$this->isConnected() || !$this->hasCredential()) {
            return false;
        }

        if ($this->isFile($filename)){
            return false;
        }


        $mode = $binary ? FTP_BINARY : FTP_ASCII;

        // Create parent directory if don't exists
        $d = explode("/", $filename);
             array_pop($d);
        $d = implode("/", $d);

        $this->createDirectory($d);

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

        if ($this->isConnected() && $this->hasCredential())
        {
            if ($this->isFile($source) && !$this->isFile($destination))
            {
                // Create parent directory if don't exists
                $destinationParentDirectory = explode("/", $destination);
                     array_pop($destinationParentDirectory);
                $destinationParentDirectory = implode("/", $destinationParentDirectory);
    
                $this->createDirectory($destinationParentDirectory);
                $this->setPassiveMode();

                $temp_file = $this->getLocalTempFilename();

                if (@ftp_get($this->connection, $temp_file, $source, FTP_BINARY))
                {
                    $state = @ftp_put($this->connection, $destination, $temp_file, FTP_BINARY);
                    unlink($temp_file);
                }

                $this->setActiveMode();
            }
        }

        return $state;
    }
    public function moveFile(string $source, string $destination): bool
    {
        $state = false;

        if ($this->isConnected() && $this->hasCredential() && $this->isFile($source))
        {
            if ($this->duplicateFile($source, $destination))
            {
                $state = $this->deleteFile($source);
            }
        }

        return $state;
    }
    public function sendFile(string $source, string $destination, bool $override=false): bool
    {
        $state = false;

        if ($this->isConnected() && $this->hasCredential() && is_file($source))
        {
            if (!$this->isFile($destination) || $override)
            {
                $this->setPassiveMode();
                $state = ftp_put($this->connection, $destination, $source);
                $this->setActiveMode();
            }
        }

        return $state;
    }
    public function getFile(string $source, string $destination, bool $override=false): bool
    {
        $state = false;

        if (!$this->isConnected() || !$this->hasCredential()) {
            return false;
        }

        if (!$this->isFile($source)) {
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

    // Directory & File both

    public function delete(?string $path, bool $recursive=true): bool
    {
        return $this->isDir($path) 
            ? $this->deleteDirectory($path, $recursive)
            : $this->deleteFile($path)
        ;
    }
    public function duplicate(string $source, string $destination): bool
    {
        return match($this->infos($source, 'type'))
        {
            'directory' => $this->duplicateDirectory($source, $destination),
            'file' => $this->duplicateFile($source, $destination),
            default => false
        };
    }
    public function move(string $source, string $destination): bool
    {
        return match($this->infos($source, 'type'))
        {
            'directory' => $this->moveDirectory($source, $destination),
            'file' => $this->moveFile($source, $destination),
            default => false
        };
    }
    public function send(string $source, string $destination, bool $override=false): bool
    {
        if (is_dir($source))
        {
            return $this->sendDirectory($source, $destination, $override);
        }
        else if (is_file($source))
        {
            return $this->sendFile($source, $destination, $override);
        }

        return false;
    }
    public function get(string $source, string $destination, bool $override=false): bool
    {
        if (preg_match("/\/$/", $source)) 
        {
            $source = substr($source, 0, -1);
        }

        return match($this->infos($source, 'type'))
        {
            'directory' => $this->getDirectory($source, $destination, $override),
            'file' => $this->getFile($source, $destination, $override),
            default => false
        };
    }


    // Driver File System
    // --

    private function pwd(): ?string 
    {
        $pwd = null;

        if ($this->isConnected() && $this->hasCredential())
        {
            $pwd = ftp_pwd( $this->connection );
        }

        return $pwd;
    }
    private function cd(string $directory): bool
    {
        switch ($directory)
        {
            case './':
            break;

            case '../':
                $cdir = explode("/", $this->pwd());
                unset($cdir[(count($cdir)-1)]);
                // $this->cdir = implode("/", $cdir);

                return ftp_cdup($this->connection);
            break;

            default:
                if ($this->isDir($directory))
                {
                    // $this->cdir = $path;
                    return ftp_chdir($this->connection, $directory);
                }
        }

        return false;
    }
    private function ls(?string $directory=null, $raw=false): array
    {
        $infos = [];

        if ($this->isConnected() && $this->hasCredential())
        {
            if (!$directory) $directory = $this->pwd();

            $this->setPassiveMode();
            $ls = ftp_rawlist($this->connection, $directory);
            $this->setActiveMode();

            $infos = $raw ? $ls : $this->parseList($ls, $directory);
        }

        return $infos;
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
    private function type(string $path): string
    {
        if ($this->isDir($path))
        {
            return "directory";
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
        if ($this->isConnected() && $this->hasCredential())
        {
            $current = $this->pwd();

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
                    if ($entry['isFile'])
                    {
                        $this->rm($entry['filepath']);
                    }
                    else if ($entry['isDirectory'])
                    {
                        $this->rmdir($entry['filepath'], $recursive);
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
        if ($this->isConnected() && $this->hasCredential())
        {
            print_r($this->isDir($filename));
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
        
        if ($this->isConnected() && $this->hasCredential())
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
        if ($this->isConnected() && $this->hasCredential())
        {
            return ftp_chmod($this->connection, $permissions, $filename);
        }

        return false;
    }
    private function allocateSpace(int $size): bool
    {
        if ($this->isConnected() && $this->hasCredential())
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
        $this->isPassive = !ftp_pasv($this->connection, false);

        return $this;
    }
    private function setPassiveMode(): static
    {
        $this->isPassive = ftp_pasv($this->connection, true);

        return $this;
    }
}