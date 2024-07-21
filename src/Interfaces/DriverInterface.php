<?php 
namespace OSW3\CloudManager\Interfaces;

interface DriverInterface 
{
    // DSN
    // --

    /**
     * Return the DSN URL
     *
     * @return string|null
     */
    public function getUrl(): ?string ;

    /**
     * Return the DSN Driver type
     *
     * @return string|null
     */
    public function getDriver(): ?string ;

    /**
     * Return the DSN User
     *
     * @return string|null
     */
    public function getUser(): ?string ;

    /**
     * Return the DSN Pass
     *
     * @return string|null
     */
    public function getPass(): ?string ;

    /**
     * Return the DSN Host
     *
     * @return string|null
     */
    public function getHost(): ?string ;

    /**
     * Return the DSN Port
     *
     * @return string|null
     */
    public function getPort(): ?string ;

    /**
     * Return the DSN Auth mode
     *
     * @return string|null
     */
    public function getAuth(): ?string ;

    /**
     * Return the Token of the DSN
     *
     * @return string|null
     */
    public function getToken(): ?string ;


    // Driver Statement
    // --
    
    /**
     * Proceed to connection to the driver
     *
     * @return static
     */
    public function connect(): bool;

    /**
     * Proceed to disconnection of the driver
     *
     * @return static
     */
    public function disconnect(): bool;

    /**
     * return true when the driver is connected
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
     * Navigate to a location (like cd)
     *
     * @param string $directory
     * @return boolean
     */
    public function navigateTo(string $directory): bool;


    // Entry infos
    // --

    /**
     * Retrieve file or directory infos
     *
     * @param string|null $path
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
    
    // Folder API
    // --
    
    /**
     * Retrieve a directory content
     *
     * @param string|null $directory if is null, get the current directory
     * @return array
     */
    public function browse(?string $directory=null): array;

    /**
     * Create a directory with permission and navigate to this directory
     *
     * @param string $directory
     * @param integer $permission
     * @param boolean $navigateTo
     * @return boolean
     */
    public function createFolder(string $directory, int $permission=0700, bool $navigateTo=true): bool;

    /**
     * Delete a directory
     *
     * @param string|null $directory
     * @param boolean $recursive
     * @return boolean
     */
    public function deleteFolder(?string $directory, bool $recursive=true): bool;

    /**
     * Duplicate a directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function duplicateFolder(string $source, string $destination): bool;

    /**
     * Move a directory
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function moveFolder(string $source, string $destination): bool;
    
    /**
     * Send directory from local to remote FTP
     *
     * @param string $source
     * @param string $destination
     * @param boolean $override
     * @return boolean
     */
    public function uploadFolder(string $source, string $destination, bool $override=false): bool;
    
    /**
     * Get directory from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function downloadFolder(string $source, string $destination, bool $override=false): bool;

    // File API
    // --

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
    public function uploadFile(string $source, string $destination, bool $override=false): bool;

    /**
     * Get file from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function downloadFile(string $source, string $destination, bool $override=false): bool;

    // Folder & File both
    // --

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
    public function upload(string $source, string $destination, bool $override=false): bool;

    /**
     * Get file or directory from remote FTP to local
     *
     * @param string $source
     * @param string $destination
     * @return boolean
     */
    public function download(string $source, string $destination, bool $override=false): bool;
}