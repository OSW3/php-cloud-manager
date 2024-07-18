<?php 
namespace OSW3\CloudManager\Drivers;

use OSW3\CloudManager\Drivers\AbstractDriver;
use OSW3\CloudManager\Interfaces\DriverInterface;

class FtpsDriver extends AbstractDriver implements DriverInterface
{
    public function connect()
    {
        echo "FTPS CONNECT";
    }

    public function disconnect()
    {
        
    }
}