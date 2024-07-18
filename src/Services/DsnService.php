<?php 
namespace OSW3\CloudManager\Services;

use OSW3\CloudManager\Helper\Driver;
use OSW3\CloudManager\Helper\Port;
use OSW3\UrlManager\Parser;

class DsnService
{
    private ?string $dsn = null;
    private ?string $driver = null;
    private ?string $user = null;
    private ?string $pass = null;
    private ?string $host = null;
    private ?string $port = null;

    public function __construct(?string $dsn=null)
    {
        if ($dsn)
        {
            $this->set($dsn);
        }
    }

    public function set(string $dsn): static
    {
        $this->dsn = $dsn;
        $this->parse();

        return $this;
    }
    public function get(bool $asArray=false): null|string|array
    {
        $dsn = "";

        if ($this->getDriver())
        {
            $dsn.= "{$this->getDriver()}://";
        }

        $dsn.= $this->getUser();

        if ($this->getPass())
        {
            $dsn.= ":{$this->getPass()}";
        }

        if ($this->getHost())
        {
            $dsn.= "@{$this->getHost()}";
        }

        if ($this->getPort())
        {
            $dsn.= ":{$this->getPort()}";
        }

        if ($asArray)
        {
            $dsn = [
                'string' => $dsn,
                'driver' => $this->getDriver(),
                'user' => $this->getUser(),
                'pass' => $this->getPass(),
                'host' => $this->getHost(),
                'port' => $this->getPort(),
            ];
        }
        return $dsn;
    }

    public function setDriver(string $driver): static
    {
        $this->driver = $driver;

        return $this;
    }
    public function getDriver(): ?string 
    {
        return $this->driver;
    }

    public function setUser(string $user): static
    {
        $this->user = $user;

        return $this;
    }
    public function getUser(): ?string 
    {
        return $this->user;
    }

    public function setPass(string $pass): static
    {
        $this->pass = $pass;

        return $this;
    }
    public function getPass(): ?string 
    {
        return $this->pass;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }
    public function getHost(): ?string 
    {
        return $this->host;
    }

    public function setPort(string $port): static
    {
        $this->port = $port;

        return $this;
    }
    public function getPort(): ?string 
    {
        return $this->port ? $this->port : match($this->driver) {
            Driver::FTP  => Port::FTP,
            Driver::FTPS => Port::FTPS,
            Driver::SFTP => Port::SFTP,
            Driver::SSH  => Port::SSH,
            default      => null,
        };
    }


    private function parse()
    {
        $parser = new Parser($this->dsn);
        $this->setDriver( $parser->fetch('scheme') );
        $this->setUser( $parser->fetch('username') );
        $this->setPass( $parser->fetch('password') );
        $this->setHost( $parser->fetch('hostname') );
        $this->setPort( $parser->fetch('port') );
    }
}