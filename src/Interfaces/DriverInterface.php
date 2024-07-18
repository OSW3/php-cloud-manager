<?php 
namespace OSW3\CloudManager\Interfaces;

interface DriverInterface 
{
    public function connect(): static;
    public function disconnect(): static;
    public function authenticate(): static;

    // Pointer location & navigation

    /**
     * Return the current location (like PWD)
     *
     * @return string|null
     */
    public function location(): ?string;

    /**
     * Navigate to a location (like cd)
     *
     * @param string $directory
     * @return boolean
     */
    public function navigateTo(string $directory): bool;

    // File types

    /**
     * True if $path is a directory type
     *
     * @param string $path
     * @return boolean
     */
    public function isDirectory(string $path): bool;

    /**
     * True if ^filename is a file type
     *
     * @param string $filename
     * @return boolean
     */
    public function isFile(string $path): bool;

    /**
     * True if ^path is a link type
     *
     * @param string $path
     * @return boolean
     */
    public function isLink(string $path): bool;

    /**
     * True if ^path is a block type
     *
     * @param string $path
     * @return boolean
     */
    public function isBlock(string $path): bool;

    /**
     * True if ^path is a character type
     *
     * @param string $path
     * @return boolean
     */
    public function isCharacter(string $path): bool;

    /**
     * True if ^path is a socket type
     *
     * @param string $path
     * @return boolean
     */
    public function isSocket(string $path): bool;

    /**
     * True if ^path is a pipe type
     *
     * @param string $path
     * @return boolean
     */
    public function isPipe(string $path): bool;

    // File infos

    /**
     * Retrieve file or directory infos
     *
     * @param string|null $path
     * @param string|null $part
     * @return array|string
     */
    public function infos(?string $path=null, ?string $part=null): array|string;

    /**
     * Permissions getter and setter
     *
     * @param string $path
     * @param integer|null $code
     * @return string|boolean
     */
    public function permissions(string $path, ?int $code=null): string|bool;
    

    // Directory
    
    /**
     * Retrieve a directory content
     *
     * @param string|null $directory if is null, get the current directory
     * @return array
     */
    public function directoryList(?string $directory=null): array;

    /**
     * Create a directory with permission and navigate to this directory
     *
     * @param string $directory
     * @param integer $permission
     * @param boolean $navigateTo
     * @return boolean
     */
    public function createDirectory(string $directory, int $permission=0700, bool $navigateTo=true): bool;

    /**
     * Delete a directory
     *
     * @param string|null $directory
     * @param boolean $recursive
     * @return boolean
     */
    public function deleteDirectory(?string $directory, bool $recursive=true): bool;

    /**
     * Duplo-icate a directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicateDirectory(string $source, string $destination): bool;

    /**
     * Move a directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function moveDirectory(string $source, string $destination): bool;
    
    /**
     * Send directory from local to remote FTP
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function sendDirectory(string $source, string $destination, bool $override=false): bool;
    
    /**
     * Get directory from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function getDirectory(string $source, string $destination, bool $override=false): bool;

    // File

    /**
     * Delete a file
     *
     * @param string|null $filename
     * @return boolean
     */
    public function deleteFile(?string $filename): bool;

    /**
     * Create file directly on the FTP
     *
     * @param string $filename
     * @param string $content
     * @param boolean $binary
     * @return boolean
     */
    public function createFile(string $filename, string $content="", bool $binary=false): bool;
    
    /**
     * Duplicate a file
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicateFile(string $source, string $destination): bool;

    /**
     * Move a file
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function moveFile(string $source, string $destination): bool;
    
    /**
     * Send file from local to remote FTP
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function sendFile(string $source, string $destination, bool $override=false): bool;

    /**
     * Get file from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function getFile(string $source, string $destination, bool $override=false): bool;

    // Directory & File both

    /**
     * Delete a file or directory
     *
     * @param string|null $path
     * @param boolean $recursive
     * @return boolean
     */
    public function delete(?string $path, bool $recursive=true): bool;

    /**
     * Duplicate a file or directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicate(string $source, string $destination): bool;

    /**
     * Move a file or directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function move(string $source, string $destination): bool;
    
    /**
     * Send file or directory from local to remoter FTP
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function send(string $source, string $destination, bool $override=false): bool;

    /**
     * Get file or directory from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function get(string $source, string $destination, bool $override=false): bool;
}