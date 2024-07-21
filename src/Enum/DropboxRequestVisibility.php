<?php 
namespace OSW3\CloudManager\Enum;

use OSW3\CloudManager\Trait\EnumTrait;

enum DropboxRequestVisibility: string 
{
    use EnumTrait;

    case PRIVATE     = "private";
    case PUBLIC      = "public";
    case TEAM_ONLY   = "team_only";
    case PASSWORD    = "password";
    case SHARED_LINK = "shared_link";
}