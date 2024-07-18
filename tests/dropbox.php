<?php

use OSW3\CloudManager\Client;

require __DIR__ . "/../vendor/autoload.php";


print_r("\n\n");
print_r("CLOUD MANAGER - METHOD 2 ------------------------------------------------------ ");
print_r("\n\n");

$ftp_server = "host";
$ftp_user = "user";
$ftp_pass = "pass";


// Method 2 - Client with provide DSN after 
// ----------------------------------

// Instance
// -- 

$client = new Client();
$client->dsn()->setDriver("ftp");
$client->dsn()->setHost($ftp_server);
$client->dsn()->setUser($ftp_user);
$client->dsn()->setPass($ftp_pass);


// DSN
// --

$hasDsn = $client->hasDsn();
print_r("Has DSN : ". ($hasDsn ? "yes" : "no") ."\n\n");

// Show DSN Parts
if ($hasDsn)
{
    print_r("DSN Driver : {$client->dsn()->getDriver()} \n\n");
    print_r("DSN Host : {$client->dsn()->getHost()} \n\n");
    print_r("DSN User : {$client->dsn()->getUser()} \n\n");
    print_r("DSN Pass : {$client->dsn()->getPass()} \n\n");
    print_r("DSN Port : {$client->dsn()->getPort()} \n\n");
    print_r("DSN String : {$client->dsn()->get()} \n\n");
}



// Connected & Authenticated
// --

// Connect + Authenticate
// $client->connect();

// Connect + NO Authenticate
// $client->connect(false);
// $client->authenticate();
// // $client->disconnect();

// $isConnected = $client->isConnected();
// print_r("Is Connected : ". ($isConnected ? "yes" : "no") ."\n\n");

// $hasCredential = $client->hasCredential();
// print_r("Has credential : ". ($hasCredential ? "yes" : "no") ."\n\n");




// Local directories settings
// --

// Set the local temp dir
// $client->setLocalTempDirectory("./../temp/ftp/temp-test/");

// Set the local root dir
// $client->setLocalDirectory("./../temp/ftp/temp-test/");






// Navigation
// --

// print_r("Location : {$client->location()} \n\n");

// print_r("Navigate to /www/my-dir-1 : {$client->navigateTo("www/my-dir-1")} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Back to root : {$client->root()} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Navigate to www/my-dir-1 : {$client->navigateTo("www/my-dir-1")} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Navigate to my-dir-2 : {$client->navigateTo("my-dir-2")} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Back to parent : {$client->parent()} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Navigate to /my-dir-2 : {$client->navigateTo("/my-dir-2")} \n\n");
// print_r("Location : {$client->location()} \n\n");



// Directory Content
// --

// print_r("Navigate to www : {$client->navigateTo("www")} \n\n");
// print_r("Location : {$client->location()} \n\n");
// print_r($client->getContent());



// File Type
// --

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



// Delete Directory
// --

// print_r("Delete directory (/www/my-dir-1/my-dir-2/my-dir-3) : ". ($client->deleteDirectory("www/my-dir-1/my-dir-2/my-dir-3") ? "yes" : "no") ."\n\n");
// print_r("Delete (/www/my-dir-1/my-dir-2/my-dir-3) : ". ($client->delete("www/my-dir-1/my-dir-2/my-dir-3") ? "yes" : "no") ."\n\n");
// print_r("Delete (/www/my-dir-1/my-dir-2/file.jpg) : ". ($client->delete("www/my-dir-1/my-dir-2/file.jpg") ? "yes" : "no") ."\n\n");
// print_r("Delete File (/www/my-dir-1/my-dir-2/file.jpg) : ". ($client->deleteFile("www/my-dir-1/my-dir-2/file.jpg") ? "yes" : "no") ."\n\n");
// print_r("Delete File (/www/my-dir-1/my-dir-2) : ". ($client->deleteFile("www/my-dir-1/my-dir-2") ? "yes" : "no") ."\n\n");

// print_r("Navigate to (/www/my-dir-1/my-dir-2) : {$client->navigateTo("/www/my-dir-1/my-dir-2")} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Create Directory (my-dir-3) : {$client->createDirectory("my-dir-3")} \n\n");
// print_r("Location : {$client->location()} \n\n");

// print_r("Create Directory (my-dir-4) : {$client->createDirectory(directory: "my-dir-4", navigateTo: false)} \n\n");
// print_r("Location : {$client->location()} \n\n");



// File Infos
// --

// print_r("Navigate to www : {$client->navigateTo("www")} \n\n");
// print_r("Location : {$client->location()} \n\n");
// print_r($client->infos());
// print_r($client->infos("/www/test.txt"));
// print_r("\n\n");
// print_r("Permissions (/www/test.txt) : {$client->infos("/www/test.txt", 'permissions')}\n\n");
// print_r("Size : {$client->infos("/www/test.txt", 'size')}\n\n");
// print_r("Nodes : {$client->infos("/www/test.txt", 'nodes')}\n\n");
// print_r("Owner : {$client->infos("/www/test.txt", 'owner')}\n\n");
// print_r("Group : {$client->infos("/www/test.txt", 'group')}\n\n");
// print_r("Type : {$client->infos("/www/test.txt", 'type')}\n\n");
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
// print_r($client->directoryList("/www"));


// print_r($client->file("/www/test.txt")->permissions);
// print_r($client->file("/www/test.txt")->size);
// print_r($client->file("/www/test.txt")->owner);
// print_r($client->file("/www/test.txt")->group);
// print_r($client->file("/www/test.txt")->infos);


// $file = "/www/my-dir-1-99/test.txt";
// print_r("Delete File ({$file}) : ". ($client->deleteFile($file) ? "yes" : "no") ."\n\n");
// print_r($client->createFile($file, "test"));
// print_r($client->copyFile("/www/no_media.jpg", "/www/my-dir-3/no_media.jpg"));
// print_r($client->copy("/www/no_media.jpg", "/www/my-dir-3/no_media.jpg"));
// print_r($client->delete("/www/my-dir-3"));
// print_r($client->copy("/www/plop", "/www/my-dir-3/plop"));

// print_r("Delete (/www/my-dir-3) : ". ($client->deleteDirectory("www/my-dir-3", true) ? "yes" : "no") ."\n\n");




// $client->createDirectory("/www/my-dir-1");
// $client->createDirectory("/www/my-dir-2");
// $client->duplicateFile("/unicorn.jpg", "/www/my-dir-1/unicorn.jpg");

// print_r("move File : ". ($client->move("/www/my-dir-1/unicorn.jpg",  "/www/my-dir-2/unicorn.jpg") ? "yes" : "no") ."\n\n");
// print_r("move directory : ". ($client->move("/www/my-dir-2",  "/www/my-dir-1/my-dir-2") ? "yes" : "no") ."\n\n");
// print_r("Move directory : ". ($client->move("/www/my-dir-3",  "/www/my-dir-1") ? "yes" : "no") ."\n\n");
// print_r("Rename directory : ". ($client->rename("/www/my-dir-1-a",  "/www/my-dir-1") ? "yes" : "no") ."\n\n");


// $client->send( __DIR__."/data.json", "/www/my-dir-1/data-copy.json", false );
// $client->send( __DIR__."/../temp", "/www/my-dir-1/" );
$client->get( "/www/my-dir-1/", "/Users/arnaud/Downloads/my-dir-1/", true );
// $client->get( "/www/my-dir-1/unicorn.jpg", "/Users/arnaud/Downloads/UNICORN.jpg" );

print_r("\n\n");