<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('yourclientID'); // Replace this
$client->setClientSecret('yourclientsecret'); // Replace this
$client->setRedirectUri('http://localhost:8080/ACS_Code/google_callback.php');
$client->addScope('email');
$client->addScope('profile');

$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;

