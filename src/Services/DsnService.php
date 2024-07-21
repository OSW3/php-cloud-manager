<?php 
namespace OSW3\CloudManager\Services;

use OSW3\UrlManager\Parser;
use OSW3\CloudManager\Client;
use OSW3\CloudManager\Helper\Port;
use OSW3\CloudManager\Helper\DriverSupport;

class DsnService
{
    private ?string $driver = null;
    private ?string $auth   = null;
    private ?string $user   = null;
    private ?string $pass   = null;
    private ?string $token  = null;
    private ?string $host   = null;
    private ?string $port   = null;

    public function __construct(private string $dsn)
    {
        $parser = new Parser($dsn);

        $dsn_scheme = $parser->fetch('scheme');
        $scheme     = explode(":", $dsn_scheme);
        $dsn_user   = $parser->fetch('username');
        $dsn_pass   = $parser->fetch('password');
        $dsn_host   = $parser->fetch('hostname');
        $dsn_port   = $parser->fetch('port');
        
        
        // Driver
        // --

        $this->driver = array_shift($scheme);


        // Auth Method
        // --

        $this->auth = array_shift($scheme);


        // User
        // --

        if (in_array($dsn_scheme, [
            DriverSupport::FTP,
        ])) $this->user = $dsn_user;
        
        else if (in_array($dsn_scheme, [
            DriverSupport::DROPBOX_BASIC,
        ])) $this->user = $dsn_host;


        // Pass
        // --

        if (in_array($dsn_scheme, [
            DriverSupport::FTP,
        ])) $this->pass = $dsn_pass;

        else if (in_array($dsn_scheme, [
            DriverSupport::DROPBOX_BASIC,
        ])) $this->pass = $dsn_port;


        // Token
        // --

        if (in_array($dsn_scheme, [
            DriverSupport::DROPBOX_TOKEN,
        ])) $this->token = $dsn_host;


        // Host
        // --

        if (in_array($dsn_scheme, [
            DriverSupport::DROPBOX_BASIC,
            DriverSupport::DROPBOX_TOKEN,
        ])) $dsn_host = null;

        $this->host = $dsn_host;


        // Port
        // --

        if (in_array($dsn_scheme, [
            DriverSupport::DROPBOX_BASIC,
        ])) $dsn_port = null;

        $this->port = $dsn_port ? $dsn_port : match($this->driver) {
            DriverSupport::FTP => Port::FTP,
            default            => null,
        };
    }

    public function get(): null|string
    {
        $params = [];
        $params = $this->auth ? array_merge($params, ["auth" => $this->auth]) : $params;

        $dsn = "";
        $dsn .= $this->driver ? "{$this->driver}://": null;
        if ($this->auth === 'token')
        {
            $dsn .= $this->token;
        }
        else {
            $dsn .= $this->user;
            $dsn .= $this->pass ? ":{$this->pass}": null;
            $dsn .= $this->host ? "@{$this->host}": null;
            $dsn .= $this->port ? $this->port : null;
        }
        $dsn .= !empty($params) ? "?".http_build_query($params) : null;
        
        return $dsn;
    }

    public function getDriver(): ?string 
    {
        return $this->driver;
    }
    public function getAuth(): ?string 
    {
        return $this->auth;
    }
    public function getUser(): ?string 
    {
        return $this->user;
    }
    public function getPass(): ?string 
    {
        return $this->pass;
    }
    public function getToken(): ?string 
    {
        return $this->token;
    }
    public function getHost(): ?string 
    {
        return $this->host;
    }
    public function getPort(): ?string 
    {
        return $this->port;
    }
}