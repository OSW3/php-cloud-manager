<?php

namespace OSW3\CloudManager;

use OSW3\CloudManager\Enum\Metadata;
use OSW3\CloudManager\Drivers\FtpDriver;
use OSW3\CloudManager\Enum\MetadataType;
use OSW3\CloudManager\Services\DsnService;
use OSW3\CloudManager\Helper\DriverSupport;
use OSW3\CloudManager\Drivers\DropboxDriver;
use OSW3\CloudManager\Interfaces\ClientInterface;

class Client implements ClientInterface
{
    private DropboxDriver|FtpDriver $driver;
    private DsnService $dsnService;
    private ?string $_directory = null;
    private ?string $_filename = null;

    public function __construct(string $dsn, bool $connect=true)
    {
        $this->dsnService = new DsnService($dsn);
        
        if ($connect) $this->connect();
    }
    
    // DSN Settings
    // --
    
    public function dsn(): DsnService
    {
        return $this->dsnService;
    }

    // Driver Statement
    // --

    public function connect(): bool
    {
        $auth    = $this->dsnService->getAuth();
        $driver  = $this->dsnService->getDriver();
        $driver .= $auth ? ":{$auth}" : null;

        $driverClass = match ($driver) {
            DriverSupport::DROPBOX_BASIC => DropboxDriver::class,
            DriverSupport::DROPBOX_TOKEN => DropboxDriver::class,
            DriverSupport::FTP           => FtpDriver::class,
        };

        $this->driver = new $driverClass($this->dsnService);
        $this->driver->connect();

        return $this->isConnected();
    }
    public function disconnect(): bool
    {
        return $this->driver->disconnect();
    }
    public function isConnected(): bool 
    {
        if ($this->driver !== null)
        {
            return $this->driver->isConnected();
        }

        return false;
    }

    // Pointer location & navigation
    // --

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

    // Entry infos
    // --

    public function infos(?string $path=null, ?string $part=null): array|string|null
    {
        return $this->driver->infos($path, $part);
    }
    public function permissions(string $path, null|string|int $mode=null): string|bool|null
    {
        return $this->driver->permissions($path, $mode);
    }
    public function setPermission(string $path, null|string|int $mode=null): bool
    {
        return $this->driver->permissions($path, $mode);
    }
    public function getPermission(string $path): string
    {
        return $this->driver->permissions($path);
    }
    public function isFolder(string $path): bool 
    {
        return $this->infos($path, Metadata::TYPE->value) === MetadataType::FOLDER->value;
    }
    public function isDirectory(string $path): bool 
    {
        return $this->isFolder($path);
    }
    public function isFile(string $path): bool 
    {
        return $this->infos($path, Metadata::TYPE->value) === MetadataType::FILE->value;
    }
    public function isLink(string $path): bool 
    {
        return $this->infos($path, Metadata::TYPE->value) === MetadataType::LINK->value;
    }
    public function isBlock(string $path): bool 
    {
        return $this->infos($path, Metadata::TYPE->value) === MetadataType::BLOCK->value;
    }
    public function isCharacter(string $path): bool 
    {
        return $this->infos($path, Metadata::TYPE->value) === MetadataType::CHARACTER->value;
    }
    public function isSocket(string $path): bool 
    {
        return $this->infos($path, Metadata::TYPE->value) === MetadataType::SOCKET->value;
    }
    public function isPipe(string $path): bool 
    {
        return $this->infos($path, Metadata::TYPE->value) === MetadataType::PIPE->value;
    }

    // Folder API
    // --

    public function browse(?string $directory=null): array
    {
        return $this->driver->browse($directory);
    }
    public function createFolder(string $directory, int $permissions=0700, bool $navigateTo = true): bool 
    {
        return $this->driver->createFolder($directory, $permissions, $navigateTo);
    }
    public function deleteFolder(?string $directory, bool $recursive=true): bool
    {
        return $this->driver->deleteFolder($directory, $recursive);
    }
    public function duplicateFolder(string $source, string $destination): bool
    {
        return $this->driver->duplicateFolder($source, $destination);
    }
    public function copyFolder(string $source, string $destination): bool
    {
        return $this->duplicateFolder($source, $destination);
    }
    public function moveFolder(string $source, string $destination): bool
    {
        return $this->driver->moveFolder($source, $destination);
    }
    public function uploadFolder(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->uploadFolder($source, $destination, $override);
    }
    public function downloadFolder(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->downloadFolder($source, $destination, $override);
    }

    // File API
    // --

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
    public function moveFile(string $source, string $destination): bool
    {
        return $this->driver->moveFile($source, $destination);
    }
    public function uploadFile(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->uploadFile($source, $destination, $override);
    }
    public function downloadFile(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->downloadFile($source, $destination, $override);
    }

    // Folder & File both
    // --

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
    public function upload(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->upload($source, $destination, $override);
    }
    public function download(string $source, string $destination, bool $override=false): bool
    {
        return $this->driver->download($source, $destination, $override);
    }









    // Local Directories Settings
    // --

    public function setTempDirectory(string $directory): static
    {
        $this->driver->setTempDirectory($directory);
        
        return $this;
    }
    public function getTempDirectory(): string
    {
        return $this->driver->getTempDirectory();
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
            'list' => $this->driver->browse($this->_directory),
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
}