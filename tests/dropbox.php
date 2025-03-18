<?php

use OSW3\CloudManager\Client;
use OSW3\CloudManager\Enum\Metadata;

require __DIR__ . "/../vendor/autoload.php";

if (file_exists(__DIR__."/config.php")) include __DIR__."/config.php";

// 1 create Dropbox App 
// -> https://www.dropbox.com/developers/apps?_tk=pilot_lp&_ad=topbar4&_camp=myapps

if (!isset($dropbox_app_key)) $dropbox_app_key = "";
if (!isset($dropbox_app_secret)) $dropbox_app_secret = "";
if (!isset($dropbox_access_token)) $dropbox_access_token = "";





print_r("\n\n");
print_r("-- OSW3 CLOUD MANAGER -- DROPBOX DRIVER ------------------------------ \n\n");


// Generate DSN
// ----------------------------------

// $dsn = "dropbox:basic://{$dropbox_app_key}:{$dropbox_app_secret}";
$dsn = "dropbox:token://{$dropbox_access_token}";



// Instance
// ----------------------------------

// Instance + Connect
$client = new Client($dsn);
// $client = new Client($dsn, true);

// Instance + Post Connect
// $client = new Client($dsn, false);
// $client->connect();



// Disconnect
// ----------------------------------

// $client->disconnect();



// DSN
// ----------------------------------

// print_r("DSN Driver : {$client->dsn()->getDriver()} \n");
// print_r("DSN Host   : {$client->dsn()->getHost()} \n");
// print_r("DSN User   : {$client->dsn()->getUser()} \n");
// print_r("DSN Pass   : {$client->dsn()->getPass()} \n");
// print_r("DSN Port   : {$client->dsn()->getPort()} \n");
// print_r("DSN Auth   : {$client->dsn()->getAuth()} \n");
// print_r("DSN Token  : {$client->dsn()->getToken()} \n");
// print_r("DSN String : {$client->dsn()->get()} \n\n");



// Connected
// ----------------------------------

$isConnected = $client->isConnected();
print_r("Is Connected : ". ($isConnected ? "yes" : "no") ."\n\n");



// Navigation
// ----------------------------------

print_r("Location : {$client->location()} \n\n");

// print_r("Navigate to '/www/my-dir-1' : {$client->navigateTo("www/my-dir-1")} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Back to 'parent' : {$client->parent()} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Navigate to 'my-dir-2' : {$client->navigateTo("my-dir-2")} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Back to 'root' : {$client->root()} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Navigate to 'www/my-dir-1' : {$client->navigateTo("www/my-dir-1")} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Navigate to '/my-dir-2' : {$client->navigateTo("/my-dir-2")} \n\n");
// print_r("Location : {$client->location()} \n\n");



// Browse Folder
// ----------------------------------

print_r("Navigate to www : {$client->navigateTo("www")} \n\n");
// print_r("Location : {$client->location()} \n\n");
// print_r($client->browse());



// Create Folder
// ----------------------------------

// print_r("Navigate to '/www/my-dir-1' : {$client->navigateTo("www/my-dir-1")} \n\n");
// print_r("Create Directory (my-dir-3) : {$client->createFolder("my-dir-3")} \n\n");
// print_r("Location : {$client->location()} \n\n");


// Delete Folder
// ----------------------------------

// print_r("Delete (/www/my-dir-1/my-dir-3) : ". ($client->delete("/www/my-dir-1/my-dir-3") ? "yes" : "no") ."\n\n");
// print_r("Delete (./../my-dir-3) : ". ($client->delete("./../my-dir-3") ? "yes" : "no") ."\n\n");

// print_r("Create Directory (my-dir-3) : {$client->createFolder("my-dir-3/a")} \n\n");
// print_r("Create Directory (my-dir-3) : {$client->createFolder("my-dir-3/b")} \n\n");
// print_r("Create Directory (my-dir-3) : {$client->createFolder("my-dir-3/c")} \n\n");



// Delete File
// ----------------------------------
// print_r("Delete (/www/my-dir-1/test.txt) : ". ($client->delete("/www/my-dir-1/test.txt") ? "yes" : "no") ."\n\n");



// Duplicate
// ----------------------------------

// print_r("Duplicate Folder (/www/my-dir-1) : ". ($client->duplicate("/www/my-dir-1", "/www/my-dir-9") ? "yes" : "no") ."\n\n");
// print_r("Duplicate File (/www/my-dir-1/test.txt) : ". ($client->duplicate("/www/my-dir-1/test.txt", "/www/my-dir-1/azertyuio.txt") ? "yes" : "no") ."\n\n");
// print_r("Duplicate File (/www/my-dir-1/test.txt) : ". ($client->duplicate("/www/my-dir-1/test.txt", "/www/my-dir-9/test.txt") ? "yes" : "no") ."\n\n");


// Move
// ----------------------------------

// print_r("Move Folder (/www/my-dir-1) : ". ($client->move("/www/my-dir-1", "/www/my-dir-111") ? "yes" : "no") ."\n\n");
// print_r("Move File (/www/my-dir-9/test.txt) : ". ($client->move("/www/my-dir-111/test.txt", "/www/my-dir-1/test.txt") ? "yes" : "no") ."\n\n");


// Upload
// ----------------------------------
$client->upload( __DIR__."/data/data.json", "/www/my-dir-1/data-copy-2.json", false );
// $client->upload( __DIR__."/data/dir", "/www/my-dir-1/dir-copy/" );


// Download
// ----------------------------------
// var_dump($client->download( "/www/my-dir-1/", "/Users/arnaud/Downloads/my-dir-1/", true ));
// $client->downloadFile( "/www/my-dir-1/dir-copy/images/unicorn.jpg", "/Users/arnaud/Downloads/UNICORN.jpg" );


// Get Link
// ----------------------------------
print_r("Link File (/www/my-dir-1/data-copy-2.txt) : ". $client->link("/www/my-dir-1/data-copy-2.json" ) ."\n\n");




// Infos
// ----------------------------------

// print_r("Navigate to '/www/my-dir-1' : {$client->navigateTo("www/my-dir-1")} \n\n");
// print_r("Location : {$client->location()} \n\n");
// print_r($client->infos());

// print_r("Infos (type) (/www/my-dir-1/test.txt) : {$client->infos("/www/my-dir-1/test.txt", 'type')}\n\n");
// print_r("Infos (type) (/www/my-dir-1) : {$client->infos("/www/my-dir-1", 'type')}\n\n");

// print_r($client->infos("/www/test.txt"));
// print_r("Permissions (get) (/www/test.txt) : {$client->permissions("/www/test.txt")}\n\n");
// print_r("Permissions (set) (/www/test.txt) : {$client->permissions("/www/test.txt", 'public')}\n\n");
// print_r("Permissions (set) (/www/test.txt) : {$client->permissions("/www/test.txt", 'private')}\n\n");
// TODO: print_r("Permissions (set) (/www/test.txt) : {$client->permissions("/www/test.txt", 'team_only')}\n\n");
// TODO: print_r("Permissions (set) (/www/test.txt) : {$client->permissions("/www/test.txt", 'password')}\n\n");
// TODO: print_r("Permissions (set) (/www/test.txt) : {$client->permissions("/www/test.txt", 'shared_link')}\n\n");
// print_r("Permissions (get) (/www/test.txt) : {$client->permissions("/www/test.txt")}\n\n");
// print_r($client->infos("/www/test.txt"));


// print_r("Type '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::TYPE->value)}\n\n");
// print_r("Type '/www/my-dir-1' : {$client->infos("/www/my-dir-1", Metadata::TYPE->value)}\n\n");


// print_r("Id '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::ID->value)}\n\n");
// print_r("Name '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::NAME->value)}\n\n");
// print_r("Name '/www/my-dir-1' : {$client->infos("/www/my-dir-1", Metadata::NAME->value)}\n\n");
// print_r("Type '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::TYPE->value)}\n\n");
// print_r("Type '/www/my-dir-1' : {$client->infos("/www/my-dir-1", Metadata::TYPE->value)}\n\n");
// print_r("Path '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::PATH->value)}\n\n");
// print_r("Size '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::SIZE->value)}\n\n");
// print_r("Owner '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::OWNER->value)}\n\n");
// print_r("Group '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::GROUP->value)}\n\n");
// print_r("Nodes '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::NODES->value)}\n\n");
// print_r("Modified '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::MODIFIED->value)}\n\n");
// print_r("Audience '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::AUDIENCE->value)}\n\n");
// print_r("Level '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::LEVEL->value)}\n\n");
// print_r("Permissions '/www/test.txt' : {$client->infos("/www/test.txt", Metadata::PERMISSIONS->value)}\n\n");


// print_r("Is Directory (www) : ". ($client->isDirectory("www") ? "yes" : "no") . " \n\n");
// print_r("Is Directory (/www) : ". ($client->isDirectory("/www") ? "yes" : "no") . " \n\n");
// print_r("Is Directory (www/test) : ". ($client->isDirectory("www/test") ? "yes" : "no") . " \n\n");
// print_r("Is Directory (/www/test) : ". ($client->isDirectory("/www/test") ? "yes" : "no") . " \n\n");
// print_r("Is Directory (www/test.txt) : ". ($client->isDirectory("www/test.txt") ? "yes" : "no") . " \n\n");
// print_r("Is Directory (/www/test.txt) : ". ($client->isDirectory("/www/test.txt") ? "yes" : "no") . " \n\n");

// print_r("Is File (www) : ". ($client->isFile("www") ? "yes" : "no") . " \n\n");
// print_r("Is File (/www) : ". ($client->isFile("/www") ? "yes" : "no") . " \n\n");
// print_r("Is File (www/test) : ". ($client->isFile("www/test") ? "yes" : "no") . " \n\n");
// print_r("Is File (/www/test) : ". ($client->isFile("/www/test") ? "yes" : "no") . " \n\n");
// print_r("Is File (www/test.txt) : ". ($client->isFile("www/test.txt") ? "yes" : "no") . " \n\n");
// print_r("Is File (/www/test.txt) : ". ($client->isFile("/www/test.txt") ? "yes" : "no") . " \n\n");












// print_r($client->infos("/www/test.txt"));
// print_r("\n\n");
// print_r("Permissions (/www/test.txt) : {$client->infos("/www/test.txt", 'permissions')}\n\n");
// print_r("Size : {$client->infos("/www/test.txt", 'size')}\n\n");
// print_r("Nodes : {$client->infos("/www/test.txt", 'nodes')}\n\n");
// print_r("Owner : {$client->infos("/www/test.txt", 'owner')}\n\n");
// print_r("Group : {$client->infos("/www/test.txt", 'group')}\n\n");
// print_r("Permissions (get) (/www/test.txt) : {$client->permissions("/www/test.txt")}\n\n");
// print_r("Permissions (set) (/www/test.txt) : {$client->permissions("/www/test.txt", 0700)}\n\n");
// print_r("Permissions (get) (/www/test.txt) : {$client->permissions("/www/test.txt")}\n\n");


// print_r("Create File (/www/test.txt) : {$client->createFile("/www/test.txt")}\n\n");
// print_r("Create File (/www/plop.txt) : {$client->createFile("/www/plop.txt")}\n\n");



// Magic Methods
// --

// print_r($client->directory("/www")->list);
// print_r($client->directory("/www")->permissions);
// print_r($client->directory("/www")->nodes);
// print_r($client->directory("/www")->owner);
// print_r($client->directory("/www")->group);
// print_r($client->directory("/www")->infos);
// print_r($client->browse("/www"));


// print_r($client->file("/www/test.txt")->permissions);
// print_r($client->file("/www/test.txt")->size);
// print_r($client->file("/www/test.txt")->owner);
// print_r($client->file("/www/test.txt")->group);
// print_r($client->file("/www/test.txt")->infos);












// Local directories settings
// --

// Set the local temp dir
// $client->setLocalTempDirectory("./../temp/ftp/temp-test/");

// Set the local root dir
// $client->setLocalDirectory("./../temp/ftp/temp-test/");






print_r("\n\n");