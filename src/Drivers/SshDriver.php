<?php 
namespace OSW3\CloudManager\Drivers;

use OSW3\CloudManager\Drivers\AbstractDriver;
use OSW3\CloudManager\Interfaces\DriverInterface;

class SshDriver extends AbstractDriver implements DriverInterface
{
    public function connect()
    {
        echo "SSH CONNECT";
    }

    public function disconnect()
    {
        
    }
}