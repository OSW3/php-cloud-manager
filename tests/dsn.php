<?php

use OSW3\CloudManager\Services\DsnService;

require __DIR__ . "/../vendor/autoload.php";

// Method 1 - Passing the DSN on instance
// $dsnService = new DsnService("ftp://user:pass@host:21");

// Method 2 - Passing the DSN post instance
// $dsnService = new DsnService();
// $dsnService->setDsn("ftp://user:pass@host:21");

// Method 3 - Passing the DSN post instance by Parts
$dsnService = new DsnService();
$dsnService->setUser("john");
// $dsnService->setPort("433");
$dsnService->setPass("123456");
$dsnService->setHost("osw3.net");
$dsnService->setDriver("ftps");
?>

<h2>DSN as Array</h2>
<pre><?php print_r($dsnService->getDsn()) ?></pre>

<h2>DSN as String</h2>
<pre><?= $dsnService->getDsn(true) ?></pre>
