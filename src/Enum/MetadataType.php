<?php 
namespace OSW3\CloudManager\Enum;

use OSW3\CloudManager\Trait\EnumTrait;

enum MetadataType: string 
{
    use EnumTrait;

    case FOLDER    = "folder";
    case FILE      = "file";
    case LINK      = "link";
    case BLOCK     = "block";
    case CHARACTER = "character";
    case SOCKET    = "socket";
    case PIPE      = "pipe";
}