<?php

namespace OSW3\CloudManager;

use OSW3\CloudManager\Drivers\DropboxDriver;
use OSW3\CloudManager\Drivers\FtpDriver;
use OSW3\CloudManager\Helper\Driver;
use OSW3\CloudManager\Services\DsnService;

class Client 
{
    private DsnService $dsnService;
    private $driver = null;
    private ?string $_directory = null;
    private ?string $_filename = null;

    public function __construct(?string $dsn=null, bool $connect=true, bool $authenticate=true)
    {
        $this->dsnService = new DsnService;

        if ($dsn) $this->dsn()->set($dsn);

        // if $dsn is provided
        if ($this->hasDsn())
        {
            if ($connect) $this->connect($authenticate);
        }
    }

    
    // DSN Settings
    // --

    /**
     * DSN Service bridge
     *
     * @return DsnService
     */
    public function dsn(): DsnService
    {
        return $this->dsnService;
    }

    /**
     * is True if a DSN is defined
     *
     * @return boolean
     */
    public function hasDsn(): bool
    {
        return !empty($this->dsnService->get());
    }


    // Local Directories Settings
    // --

    public function setLocalDirectory(string $directory): static
    {
        $this->driver->setLocalDirectory($directory);
        
        return $this;
    }
    public function setLocalTempDirectory(string $directory): static
    {
        $this->driver->setLocalTempDirectory($directory);
        
        return $this;
    }


    // Driver
    // --

    public function connect(bool $authenticate=true)
    {
        $driverClass = match ($this->dsn()->getDriver()) {
            Driver::DROPBOX  => DropboxDriver::class,
            Driver::FTP  => FtpDriver::class,
            // Driver::FTPS => FtpsDriver::class,
            // Driver::SFTP => SftpDriver::class,
            // Driver::SSH  => SshDriver::class,
        };

        $this->driver = new $driverClass($this->dsn()->get(true));
        $this->driver->connect();

        if ($authenticate) $this->authenticate();
    }
    public function disconnect()
    {
        $this->driver->disconnect();
    }
    public function isConnected(): bool 
    {
        if ($this->driver !== null)
        {
            return $this->driver->isConnected();
        }

        return false;
    }
    public function getConnection()
    {
        return $this->driver->getConnection();
    }

    
    // Authentication
    // --

    public function authenticate()
    {
        $this->driver->authenticate();
    }
    public function hasCredential(): bool 
    {
        if ($this->driver !== null)
        {
            return $this->driver->hasCredential();
        }
        
        return false;
    }


    // Magic Methods
    // --

    public function directory(string $directory): static
    {
        $this->_directory = $directory;

        return $this;
    }
    public function file(string $filename): static
    {
        $this->_filename = $filename;

        return $this;
    }
    public function __get(string $property)
    {
        $output = null;

        if ($this->_directory !== null && $this->isDirectory($this->_directory)) $output = match($property) 
        {
            'list' => $this->driver->getContent($this->_directory),
            'permissions' => $this->driver->permissions($this->_directory),
            'nodes' => $this->driver->infos($this->_directory, 'nodes'),
            'owner' => $this->driver->infos($this->_directory, 'owner'),
            'group' => $this->driver->infos($this->_directory, 'group'),
            'infos' => $this->driver->infos($this->_directory),
            default => null
        };

        else if ($this->_filename !== null && $this->isFile($this->_filename)) $output = match($property) 
        {
            'permissions' => $this->driver->permissions($this->_filename),
            'size' => $this->driver->infos($this->_filename, 'size'),
            'owner' => $this->driver->infos($this->_filename, 'owner'),
            'group' => $this->driver->infos($this->_filename, 'group'),
            'infos' => $this->driver->infos($this->_filename),
            default => null
        };

        $this->_directory = null;
        $this->_filename = null;

        return $output;
    }

    // File System
    // --

    // Pointer location & navigation

    public function location(): ?string
    {
        return $this->driver->location();
    }
    public function navigateTo(string $directory): bool
    {
        return $this->driver->navigateTo($directory);
    }
    public function parent(): bool
    {
        return $this->driver->navigateTo("../");
    }
    public function root(): bool
    {
        return $this->driver->navigateTo("/");
    }

    // File types

    public function isDirectory(string $path): bool 
    {
        return $this->driver->isDirectory($path);
    }
    public function isFile(string $path): bool 
    {
        return $this->driver->isFile($path);
    }
    public function isLink(string $path): bool 
    {
        return $this->driver->isLink($path);
    }
    public function isBlock(string $path): bool 
    {
        return $this->driver->isBlock($path);
    }
    public function isCharacter(string $path): bool 
    {
        return $this->driver->isCharacter($path);
    }
    public function isSocket(string $path): bool 
    {
        return $this->driver->isSocket($path);
    }
    public function isPipe(string $path): bool 
    {
        return $this->driver->isPipe($path);
    }

    // File infos

    public function infos(?string $path=null, ?string $part=null): array|string
    {
        return $this->driver->infos($path, $part);
    }
    public function setPermission(string $path, int $code): bool
    {
        return $this->driver->permissions($path, $code);
    }
    public function getPermission(string $path): string
    {
        return $this->driver->permissions($path);
    }
    public function permissions(string $path, ?int $code=null): string|bool
    {
        return $this->driver->permissions($path, $code);
    }

    // Directory

    public function directoryList(?string $directory=null): array
    {
        return $this->driver->directoryList($directory);
    }
    public function createDirectory(string $directory, int $permissions=0700, bool $navigateTo = true): bool 
    {
        return $this->driver->createDirectory($directory, $permissions, $navigateTo);
    }
    public function deleteDirectory(?string $directory, bool $recursive=true): bool
    {
        return $this->driver->deleteDirectory($directory, $recursive);
    }
    public function duplicateDirectory(string $source, string $destination): bool
    {
        return $this->driver->duplicateDirectory($source, $destination);
    }
    public function copyDirectory(string $source, string $destination): bool
    {
        return $this->duplicateDirectory($source, $destination);
    }
    public function sendDirectory(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->sendDirectory($source, $destination, $override);
    }
    public function getDirectory(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->getDirectory($source, $destination, $override);
    }

    // File

    public function createFile(string $filename, string $content="", bool $binary=false): bool
    {
        return $this->driver->createFile($filename, $content, $binary);
    }
    public function deleteFile(?string $filename): bool
    {
        return $this->driver->deleteFile($filename);
    }
    public function duplicateFile(string $source, string $destination): bool
    {
        return $this->driver->duplicateFile($source, $destination);
    }
    public function copyFile(string $source, string $destination): bool
    {
        return $this->duplicateFile($source, $destination);
    }
    public function sendFile(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->sendFile($source, $destination, $override);
    }
    public function getFile(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->getFile($source, $destination, $override);
    }

    // Directory & File both

    public function delete(?string $path, bool $recursive=true): bool
    {
        return $this->driver->delete($path, $recursive);
    }
    public function duplicate(string $source, string $destination): bool
    {
        return $this->driver->duplicate($source, $destination);
    }
    public function copy(string $source, string $destination): bool
    {
        return $this->duplicate($source, $destination);
    }
    public function move(string $source, string $destination): bool
    {
        return $this->driver->move($source, $destination);
    }
    public function rename(string $source, string $destination): bool
    {
        return $this->driver->move($source, $destination);
    }
    public function send(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->send($source, $destination, $override);
    }
    public function get(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->get($source, $destination, $override);
    }
}