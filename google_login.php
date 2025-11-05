<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('125257450980-l5dr88lnrr5jbdt9jcrhbj6lbllrh9i8.apps.googleusercontent.com'); // Replace this
$client->setClientSecret('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'); // Replace this
$client->setRedirectUri('http://localhost:8080/ACS_Code/google_callback.php');
$client->addScope('email');
$client->addScope('profile');

$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
