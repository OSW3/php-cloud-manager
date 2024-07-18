<?php 
namespace OSW3\CloudManager\Drivers;

use OSW3\CloudManager\Interfaces\DriverInterface;

class DropboxDriver extends AbstractDriver implements DriverInterface
{

    public function connect(): static
    {
        return $this;
    }

    public function disconnect(): static
    {
        return $this;
    }

    public function authenticate(): static
    {
        return $this;
    }

    // Pointer location & navigation

    /**
     * Return the current location (like PWD)
     *
     * @return string|null
     */
    public function location(): ?string
    {
        return "";
    }

    /**
     * Navigate to a location (like cd)
     *
     * @param string $directory
     * @return boolean
     */
    public function navigateTo(string $directory): bool
    {
        return false;
    }

    // File types

    /**
     * True if $path is a directory type
     *
     * @param string $path
     * @return boolean
     */
    public function isDirectory(string $path): bool
    {
        return false;
    }

    /**
     * True if ^filename is a file type
     *
     * @param string $filename
     * @return boolean
     */
    public function isFile(string $path): bool
    {
        return false;
    }

    /**
     * True if ^path is a link type
     *
     * @param string $path
     * @return boolean
     */
    public function isLink(string $path): bool
    {
        return false;
    }

    /**
     * True if ^path is a block type
     *
     * @param string $path
     * @return boolean
     */
    public function isBlock(string $path): bool
    {
        return false;
    }

    /**
     * True if ^path is a character type
     *
     * @param string $path
     * @return boolean
     */
    public function isCharacter(string $path): bool
    {
        return false;
    }

    /**
     * True if ^path is a socket type
     *
     * @param string $path
     * @return boolean
     */
    public function isSocket(string $path): bool
    {
        return false;
    }

    /**
     * True if ^path is a pipe type
     *
     * @param string $path
     * @return boolean
     */
    public function isPipe(string $path): bool
    {
        return false;
    }

    // File infos

    /**
     * Retrieve file or directory infos
     *
     * @param string|null $path
     * @param string|null $part
     * @return array|string
     */
    public function infos(?string $path=null, ?string $part=null): array|string
    {
        return [];
    }

    /**
     * Permissions getter and setter
     *
     * @param string $path
     * @param integer|null $code
     * @return string|boolean
     */
    public function permissions(string $path, ?int $code=null): string|bool
    {
        return false;
    }
    

    // Directory
    
    /**
     * Retrieve a directory content
     *
     * @param string|null $directory if is null, get the current directory
     * @return array
     */
    public function directoryList(?string $directory=null): array
    {
        return [];
    }

    /**
     * Create a directory with permission and navigate to this directory
     *
     * @param string $directory
     * @param integer $permission
     * @param boolean $navigateTo
     * @return boolean
     */
    public function createDirectory(string $directory, int $permission=0700, bool $navigateTo=true): bool
    {
        return false;
    }

    /**
     * Delete a directory
     *
     * @param string|null $directory
     * @param boolean $recursive
     * @return boolean
     */
    public function deleteDirectory(?string $directory, bool $recursive=true): bool
    {
        return false;
    }

    /**
     * Duplo-icate a directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicateDirectory(string $source, string $destination): bool
    {
        return false;
    }

    /**
     * Move a directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function moveDirectory(string $source, string $destination): bool
    {
        return false;
    }
    
    /**
     * Send directory from local to remote FTP
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function sendDirectory(string $source, string $destination, bool $override=false): bool
    {
        return false;
    }
    
    /**
     * Get directory from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function getDirectory(string $source, string $destination, bool $override=false): bool
    {
        return false;
    }

    // File

    /**
     * Delete a file
     *
     * @param string|null $filename
     * @return boolean
     */
    public function deleteFile(?string $filename): bool
    {
        return false;
    }

    /**
     * Create file directly on the FTP
     *
     * @param string $filename
     * @param string $content
     * @param boolean $binary
     * @return boolean
     */
    public function createFile(string $filename, string $content="", bool $binary=false): bool
    {
        return false;
    }
    
    /**
     * Duplicate a file
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicateFile(string $source, string $destination): bool
    {
        return false;
    }

    /**
     * Move a file
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function moveFile(string $source, string $destination): bool
    {
        return false;
    }
    
    /**
     * Send file from local to remote FTP
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function sendFile(string $source, string $destination, bool $override=false): bool
    {
        return false;
    }

    /**
     * Get file from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function getFile(string $source, string $destination, bool $override=false): bool
    {
        return false;
    }

    // Directory & File both

    /**
     * Delete a file or directory
     *
     * @param string|null $path
     * @param boolean $recursive
     * @return boolean
     */
    public function delete(?string $path, bool $recursive=true): bool
    {
        return false;
    }

    /**
     * Duplicate a file or directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicate(string $source, string $destination): bool
    {
        return false;
    }

    /**
     * Move a file or directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function move(string $source, string $destination): bool
    {
        return false;
    }
    
    /**
     * Send file or directory from local to remoter FTP
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function send(string $source, string $destination, bool $override=false): bool
    {
        return false;
    }

    /**
     * Get file or directory from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function get(string $source, string $destination, bool $override=false): bool
    {
        return false;
    }
}