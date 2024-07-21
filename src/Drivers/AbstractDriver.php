<?php 
namespace OSW3\CloudManager\Drivers;

use OSW3\CloudManager\Enum\Metadata;
use OSW3\CloudManager\Enum\MetadataType;
use OSW3\CloudManager\Services\DsnService;

abstract class AbstractDriver 
{
    const METADATA = [
        Metadata::ID->value          => null,
        Metadata::TYPE->value        => null,
        Metadata::NAME->value        => null,
        Metadata::PATH->value        => null,
        Metadata::SIZE->value        => null,
        Metadata::OWNER->value       => null,
        Metadata::GROUP->value       => null,
        Metadata::NODES->value       => null,
        Metadata::MODIFIED->value    => null,
        Metadata::AUDIENCE->value    => null,
        Metadata::LEVEL->value       => null,
        Metadata::PERMISSIONS->value => null,
        // Metadata::ISLOCK->value         => false,
        // Metadata::NOACCESS->value       => false,
        // Metadata::READONLY->value       => false,
        // Metadata::ISDOWNLOADABLE->value => true,
    ];

    protected $connection = false;

    private string $tempDirectory = "./";

    public function __construct(protected DsnService $dsn)
    {
        $this->dsn = $dsn;
    }

    // DSN
    // --

    public function getUrl(): ?string 
    {
        return $this->dsn->get();
    }
    public function getDriver(): ?string 
    {
        return $this->dsn->getDriver();
    }
    public function getUser(): ?string 
    {
        return $this->dsn->getUser();
    }
    public function getPass(): ?string 
    {
        return $this->dsn->getPass();
    }
    public function getHost(): ?string 
    {
        return $this->dsn->getHost();
    }
    public function getPort(): ?string 
    {
        return $this->dsn->getPort();
    }
    public function getAuth(): ?string 
    {
        return $this->dsn->getAuth();
    }
    public function getToken(): ?string 
    {
        return $this->dsn->getToken();
    }

    // Driver Statement
    // --

    public function isConnected(): bool 
    {
        return !!$this->connection;
    }

    // Pointer location & navigation
    // --


    // Entry infos
    // --








    // Local Directories Settings
    // --

    public function setTempDirectory(string $directory): static
    {
        if (preg_match("/\/$/", $directory))
        {
            $directory = substr($directory, 0, -1);
        }
     
        $directory.= "/";

        $this->createTempDirectory($directory);
        $this->tempDirectory = $directory;
        
        return $this;
    }
    public function getTempDirectory(): string 
    {
        return $this->tempDirectory;
    }
    public function getLocalTempFilename(): string 
    {
        return $this->getTempDirectory() . uniqid();
    }
    private function createTempDirectory(string $directory): static
    {
        if (!is_dir($directory))
        {
            mkdir($directory, 0777, true);
        }

        return $this;
    }

    protected function scandir(string $directory, array $entries=[]): array
    {
        if (preg_match("/\/$/", $directory))
        {
            $directory = substr($directory, 0, -1);
        }

        $scandir = scandir($directory);
        array_shift($scandir);
        array_shift($scandir);

        foreach ($scandir as $key => $entry)
        {
            $entry = "{$directory}/{$entry}";

            $filetype = filetype($entry);

            $type = match ($filetype) {
                'dir' => MetadataType::FOLDER->value,
                'file' => MetadataType::FILE->value,
                default => $filetype
            };

            $entries = array_merge($entries, [$entry => [
                'type' => $type,
                'path' => $entry,
            ]]);

            if ($type === MetadataType::FOLDER->value)
            {
                $entries = $this->scandir($entry, $entries);
            }
        }

        return $entries;
    }

    protected function fetch(string $url, string $type='GET', array|string $data=[], array $headers=[], bool $raw=false)
    {
        $headers = array_merge([
            "Content-Type" => "application/json"
        ], $headers);

        foreach ($headers as $key => $value) if ($value !== null)
        {
            $headers[] = "{$key}: {$value}";
            unset($headers[$key]);
        }
    
        $ch = curl_init($url);
    
        if ($type === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        }
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    

        if (!empty($data)) {
            $data = is_array($data) ? json_encode($data) : $data;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    
        $response = curl_exec($ch);

        curl_close($ch);
        
        return $raw ? $response : json_decode($response);
    }
    protected function fetchPost(string $url, array|string $data=[], array $headers=[])
    {
        return $this->fetch($url, 'POST', $data, $headers);
    }
}