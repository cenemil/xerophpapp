<?php
ini_set('display_errors', 'On');
require( __DIR__ . '/vendor/autoload.php' );
require( __DIR__ . '/storage.php' );

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$clientId = getenv('CLIENT_ID');
$clientSecret = getenv('CLIENT_SECRET');
$redirectUri = getenv('REDIRECT_URI_SIGNUP');

session_destroy();

// Storage Classe uses sessions for storing access token > extend to your DB of choice
$storage = new StorageClass();

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
	'clientId'                => $clientId,   
	'clientSecret'            => $clientSecret,
	'redirectUri'             => $redirectUri,
	'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
	'urlAccessToken'          => 'https://identity.xero.com/connect/token',
	'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
]);

$options = [
	'scope' => ['openid']
];

// Fetch the authorization URL from the provider; this returns the urlAuthorize option and generates and applies any necessary parameters (e.g. state).
$authorizationUrl = $provider->getAuthorizationUrl( $options );

// Unset oauth2state data
//unset( $_SESSION['oauth2state'] );

// Get the state generated for you and store it to the session.
$_SESSION['oauth2state'] = $provider->getState();

// Redirect the user to the authorization URL.
header( 'Location: ' . $authorizationUrl );
exit();
?>
<html>
	<head>
		<title>Xero Signup</title>
	</head>
	<body>
		Opps! Problem redirecting .....
	</body>
</html>
