<?php 
namespace OSW3\CloudManager\Drivers;

abstract class AbstractDriver 
{
    private array $dsn;
    protected $connection = false;
    protected $credential = false;

    private string $localDirectory = "./";
    private string $localTempDirectory = "./";

    public function __construct(array $dsn)
    {
        $this->dsn = $dsn;
    }

    // DSN
    // --

    public function getDsn(): ?string 
    {
        return $this->dsn['string'];
    }
    public function getDriver(): ?string 
    {
        return $this->dsn['driver'];
    }
    public function getUser(): ?string 
    {
        return $this->dsn['user'];
    }
    public function getPass(): ?string 
    {
        return $this->dsn['pass'];
    }
    public function getHost(): ?string 
    {
        return $this->dsn['host'];
    }
    public function getPort(): ?string 
    {
        return $this->dsn['port'];
    }


    // Local Directories Settings
    // --

    public function setLocalDirectory(string $directory): static
    {
        if (preg_match("/\/$/", $directory))
        {
            $directory = substr($directory, 0, -1);
        }
     
        $directory.= "/";

        $this->createLocalTempDirectory($directory);
        $this->localDirectory = $directory;

        return $this;
    }
    public function getLocalDirectory(): string 
    {
        return $this->localDirectory;
    }
    public function setLocalTempDirectory(string $directory): static
    {
        if (preg_match("/\/$/", $directory))
        {
            $directory = substr($directory, 0, -1);
        }
     
        $directory.= "/";

        $this->createLocalTempDirectory($directory);
        $this->localTempDirectory = $directory;
        
        return $this;
    }
    public function getLocalTempDirectory(): string 
    {
        return $this->localTempDirectory;
    }
    public function getLocalTempFilename(): string 
    {
        return $this->getLocalTempDirectory() . uniqid();
    }
    private function createLocalTempDirectory(string $directory): static
    {
        if (!is_dir($directory))
        {
            mkdir($directory, 0777, true);
        }

        return $this;
    }


    // Statement
    // --

    public function getConnection() 
    {
        return $this->connection;
    }
    public function isConnected(): bool 
    {
        return !!$this->getConnection();
    }


    // Authentication
    // --

    public function hasCredential(): bool
    {
        return $this->credential;
    }


    // File System
    // --

    protected function parseList(array $data, ?string $dir=null): array
    {
        $output = [];

        foreach ($data as $infos)
        {

            // Data Extraction
            // --

            // Extract file type and permissions
            preg_match("/^[-ld]([-|r|w|x]{3}){3}/", $infos, $permissions);
            $column_1 = $permissions[0];
            $infos    = trim(preg_replace("/$column_1/", "", $infos));

            // Extract $entry nodes / links
            $column_2 = intval(substr($infos, 0, 1));
            $infos    = trim(substr($infos, 1));

            // Extract Owner name
            preg_match("/^\d+/", $infos, $results);
            $column_3 = $results[0];
            $infos    = trim(substr($infos, strlen($column_3)));

            // Extract Group name
            preg_match("/^\d+/", $infos, $results);
            $column_4 = $results[0];
            $infos    = trim(substr($infos, strlen($column_4)));

            // Extract Filesize
            preg_match("/^\d+/", $infos, $results);
            $column_5 = intval($results[0]);
            $infos    = trim(substr($infos, strlen($column_5)));

            // Extract Date
            preg_match("/^(Jan|Feb|Mar|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d{1,2}\s+(\d{4}|\d{2}:\d{2})/", $infos, $results);
            $column_6 = $results[0];
            $infos    = trim(substr($infos, strlen($column_6)));

            // Extract Filename
            $column_7 = $infos;


            // Parse Data
            // --

            // Type
            $type = match(substr($column_1, 0, 1)) 
            {
                "-" => "file",
                "d" => "directory",
                "l" => "link",
                "b" => "block",
                "c" => "character",
                "s" => "socket",
                "p" => "pipe",
            };
            $isFile      = $type === "file";
            $isDirectory = $type === "directory";
            $isLink      = $type === "link";
            $isBlock     = $type === "block";
            $isCharacter = $type === "character";
            $isSocket    = $type === "socket";
            $isPipe      = $type === "pipe";

            // Permissions
            $permissions = [
                'string'  => $column_1,
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
            ];
            $permissions['code'] = $this->getPermissionsCode($permissions);

            // Owner
            $owner = $column_3;

            // Group
            $group = $column_4;

            // Nodes
            $nodes = $column_2;

            // File date
            $date = new \DateTime($column_6);

            // File size
            $filesize = $column_5;

            // File name and Path
            $filename = $column_7;
            $filepath = "{$dir}/{$filename}";
            $filepath = str_replace("//", "/", $filepath);

            array_push($output, [
                'type'        => $type,
                'isFile'      => $isFile,
                'isDirectory' => $isDirectory,
                'isLink'      => $isLink,
                'isBlock'     => $isBlock,
                'isCharacter' => $isCharacter,
                'isSocket'    => $isSocket,
                'isPipe'      => $isPipe,
                'permissions' => $permissions,
                'owner'       => $owner,
                'group'       => $group,
                'nodes'       => $nodes,
                'date'        => $date,
                'filesize'    => $filesize,
                'filename'    => $filename,
                'filepath'    => $filepath,
            ]);
        }
        
        return $output;
    }
    private function getPermissionsCode($permissions)
    {
        return  "0".$this->getPermissionsPart($permissions, 'owner').
                $this->getPermissionsPart($permissions, 'group').
                $this->getPermissionsPart($permissions, 'anon');
    }
    private function getPermissionsPart(array $permissions, string $part)
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
}