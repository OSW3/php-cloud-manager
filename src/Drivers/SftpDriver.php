<?php 
namespace OSW3\CloudManager\Drivers;

use OSW3\CloudManager\Drivers\FtpDriver;

class SftpDriver extends FtpDriver
{
    public function connect(): static
    {
        if (!$this->isConnected())
        {
            $this->connection = ftp_ssl_connect($this->getHost(), $this->getPort());
        }

        return $this;
    }
}