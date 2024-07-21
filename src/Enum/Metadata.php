<?php 
namespace OSW3\CloudManager\Enum;

use OSW3\CloudManager\Trait\EnumTrait;

enum Metadata: string 
{
    use EnumTrait;

    case ID          = "id";
    case TYPE        = "type";
    case NAME        = "name";
    case PATH        = "path";
    case SIZE        = "size";
    case OWNER       = "owner";
    case GROUP       = "group";
    case NODES       = "nodes";
    case MODIFIED    = "modified";
    case AUDIENCE    = "audience";
    case LEVEL       = "level";
    case PERMISSIONS = "permissions";
    // const ISLOCK         = "isLock";
    // const NOACCESS       = "noAccess";
    // const READONLY       = "readonly";
    // const ISDOWNLOADABLE = "isDownloadable";

}