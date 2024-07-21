<?php 
namespace OSW3\CloudManager\Interfaces;

use OSW3\CloudManager\Services\DsnService;

interface ClientInterface
{
    // DSN
    // --

    /**
     * DSN Service Bridge
     *
     * @return DsnService
     */
    public function dsn(): DsnService;


    // Driver Statement
    // --

    /**
     * Proceed to driver connection and return the connection state
     *
     * @return boolean
     */
    public function connect(): bool;

    /**
     * Proceed to disconnection and return the connection status
     *
     * @return boolean
     */
    public function disconnect(): bool;

    /**
     * return `true` when the driver is connected
     *
     * @return boolean
     */
    public function isConnected(): bool;

    
    // Pointer location & navigation
    // --

    /**
     * Return the current location (like PWD)
     *
     * @return string|null
     */
    public function location(): ?string;

    /**
     * Set the pointer to a specific folder.
     *
     * @param string $folder
     * @return boolean
     */
    public function navigateTo(string $folder): bool;

    /**
     * Set the pointer to a parent folder.
     *
     * @return boolean
     */
    public function parent(): bool;

    /**
     * Set the pointer to the root of the driver.
     *
     * @return boolean
     */
    public function root(): bool;

    // Entry infos
    // --

    /**
     * Retrieve the entry infos
     * 
     * Return an array if $part is null
     *
     * @param string|null $path The path of directory or file, return current directory if null
     * @param string|null $part
     * @return array|string
     */
    public function infos(?string $path=null, ?string $part=null): array|string|null;
    
    /**
     * Permissions getter and setter
     *
     * @param string $path
     * @param integer|null $code
     * @return string|boolean
     */
    public function permissions(string $path, null|string|int $mode=null): string|bool|null;

    /**
     * Set permission mode
     *
     * @param string $path
     * @param integer $code
     * @return boolean
     */
    public function setPermission(string $path, null|string|int $mode=null): bool;

    /**
     * Read permissions
     *
     * @param string $path
     * @return string
     */
    public function getPermission(string $path): string;

    /**
     * True if $path is a folder type
     *
     * @param string $path
     * @return boolean
     */
    public function isFolder(string $path): bool;

    /**
     * An alias of isFolder
     *
     * @param string $path
     * @return boolean
     */
    public function isDirectory(string $path): bool;

    /**
     * True if $path is a file type
     *
     * @param string $path
     * @return boolean
     */
    public function isFile(string $path): bool;

    /**
     * True if $path is a link type
     *
     * @param string $path
     * @return boolean
     */
    public function isLink(string $path): bool;

    /**
     * True if $path is a block type
     *
     * @param string $path
     * @return boolean
     */
    public function isBlock(string $path): bool;

    /**
     * True if $path is a character type
     *
     * @param string $path
     * @return boolean
     */
    public function isCharacter(string $path): bool;

    /**
     * True if $path is a socket type
     *
     * @param string $path
     * @return boolean
     */
    public function isSocket(string $path): bool;

    /**
     * True if $path is a pipe type
     *
     * @param string $path
     * @return boolean
     */
    public function isPipe(string $path): bool;

    // Folder API
    // --

    /**
     * Brose and get folder content
     *
     * @param string|null $folder
     * @return array
     */
    public function browse(?string $folder=null): array;

    /**
     * Create a new folder
     *
     * @param string $folder
     * @param integer $permissions
     * @param boolean $navigateTo
     * @return boolean
     */
    public function createFolder(string $folder, int $permissions=0700, bool $navigateTo = true): bool ;
    
    /**
     * Delete a folder
     *
     * @param string|null $folder
     * @param boolean $recursive
     * @return boolean
     */
    public function deleteFolder(?string $folder, bool $recursive=true): bool;
    
    /**
     * Duplicate a folder
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicateFolder(string $source, string $destination): bool;
    
    /**
     * Alias for duplicateFolder
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function copyFolder(string $source, string $destination): bool;
    
    /**
     * Move a folder (cut + paste)
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function moveFolder(string $source, string $destination): bool;
    
    /**
     * Upload an entire folder to a driver
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function uploadFolder(string $source, string $destination, bool $override=false): bool;
    
    /**
     * Download an entire folder from a driver
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function downloadFolder(string $source, string $destination, bool $override=false): bool;
    
    // File API
    // --
    
    /**
     * Create a new file with content
     *
     * @param string $filename
     * @param string $content
     * @param boolean $binary
     * @return boolean
     */
    public function createFile(string $filename, string $content="", bool $binary=false): bool;
    
    /**
     * Delete a file
     *
     * @param string|null $filename
     * @return boolean
     */
    public function deleteFile(?string $filename): bool;
    
    /**
     * Duplicate a file
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicateFile(string $source, string $destination): bool;
    
    /**
     * Alias for duplicateFIle
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function copyFile(string $source, string $destination): bool;

    /**
     * Move a file
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function moveFile(string $source, string $destination): bool;
    
    /**
     * Upload a file to the driver
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function uploadFile(string $source, string $destination, bool $override=false): bool;
    
    /**
     * Download a file from a driver
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function downloadFile(string $source, string $destination, bool $override=false): bool;
    
    // Folder & File both
    // --

    /**
     * Delete folder or file
     *
     * @param string|null $path
     * @param boolean $recursive
     * @return boolean
     */
    public function delete(?string $path, bool $recursive=true): bool;
    
    /**
     * Duplicate folder or file (copy + paste)
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicate(string $source, string $destination): bool;
    
    /**
     * Alias for duplicate
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function copy(string $source, string $destination): bool;
    
    /**
     * Move a folder or file (cut + paste)
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function move(string $source, string $destination): bool;
    
    /**
     * Alias for move
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function rename(string $source, string $destination): bool;
    
    /**
     * Upload to a driver
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function upload(string $source, string $destination, bool $override=false): bool;
    
    /**
     * Download from a driver
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function download(string $source, string $destination, bool $override=false): bool;
}