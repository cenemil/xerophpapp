<?php
    ini_set('display_errors', 'On');
	require __DIR__ . '/vendor/autoload.php';

	require_once(__DIR__ . '/storage.php');
	require_once(__DIR__ . '/example.php');

	require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

	$user_id = get_current_user_id();

	$user_nonce = wp_create_nonce( 'user_nonce' );

	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();
	$clientId = getenv('CLIENT_ID');
	$clientSecret = getenv('CLIENT_SECRET');
	$redirectUri = getenv('REDIRECT_URI');

	// Storage Classe uses sessions for storing token > extend to your DB of choice
	$storage = new StorageClass();

	// ALL methods are demonstrated using this class
	$ex = new ExampleClass();

	$xeroTenantId = (string)$storage->getSession()['tenant_id'];

	// Check if Access Token is expired
	// if so - refresh token
	if ($storage->getHasExpired()) {
		$provider = new \League\OAuth2\Client\Provider\GenericProvider([
			'clientId'                => $clientId,   
			'clientSecret'            => $clientSecret,
			'redirectUri'             => $redirectUri,
			'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
			'urlAccessToken'          => 'https://identity.xero.com/connect/token',
			'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation'
		]);

		$newAccessToken = $provider->getAccessToken('refresh_token', [
			'refresh_token' => $storage->getRefreshToken()
		]);
		// Save my token, expiration and refresh token
		// Save my token, expiration and refresh token
		$storage->setToken(
			$newAccessToken->getToken(),
			$newAccessToken->getExpires(), 
			$xeroTenantId,
			$newAccessToken->getRefreshToken(),
			$newAccessToken->getValues()["id_token"]
		);
	}

	$xero_user_data = array(
		'refresh_token' => $storage->getRefreshToken(),
		'access_token' => $storage->getAccessToken()
	);

	update_user_meta( $user_id, 'xero_user_data', $xero_user_data );

	$config = XeroAPI\XeroPHP\Configuration::getDefaultConfiguration()->setAccessToken( (string)$storage->getSession()['token'] );		  
	$accountingApi = new XeroAPI\XeroPHP\Api\AccountingApi(
	    new GuzzleHttp\Client(),
	    $config
	);

	$assetApi = new XeroAPI\XeroPHP\Api\AssetApi(
	    new GuzzleHttp\Client(),
	    $config
	);

	$identityApi = new XeroAPI\XeroPHP\Api\IdentityApi(
	    new GuzzleHttp\Client(),
	    $config
	);

	$projectApi = new XeroAPI\XeroPHP\Api\ProjectApi(
	    new GuzzleHttp\Client(),
	    $config
	);

	$payrollAuApi = new XeroAPI\XeroPHP\Api\PayrollAuApi(
	    new GuzzleHttp\Client(),
	    $config
	);

	$financeApi = new XeroAPI\XeroPHP\Api\FinanceApi(
	    new GuzzleHttp\Client(),
	    $config
	);

	if (isset($_POST["endpoint"]) ) {
		$endpoint = htmlspecialchars($_POST["endpoint"]);
	} else {
		$endpoint = "Accounts";
	}

	if (isset($_POST["action"]) ) {
		$action = htmlspecialchars($_POST["action"]);
	} else {
		$action = "none";
	}

	// Parse the example.php file to find matching endpoint/method combination
	// and display the code that was just executed on the screen.
	$file = file_get_contents('./example.php', true);

	$parsed = get_string_between($file, '//[' . $endpoint . ':' . $action . ']', '//[/' . $endpoint . ':' . $action . ']');
	$parsed = str_replace(["\r\n", "\r", "\n"], "<br/>", $parsed);

	function get_string_between($string, $start, $end){
	    $string = ' ' . $string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return '';
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return substr($string, $ini, $len);
	}
?>
<html>
<head>
	<title>xero-php-oauth2-app</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.min.js"  crossorigin="anonymous"></script>
	<script src="xero-sdk-ui/xero.js" crossorigin="anonymous"></script>
	<script type="text/javascript">
		setInterval( function(){
			window.close();
		}, 3000 );
   	</script>
   	<style type="text/css">
   		.authentication_notice{padding: 30vh 30px;text-align: center;}
   	</style>
</head>
<body>
	<div class="authentication_notice">
		<p>Authentication successful! This window will auto close and you will get back to the Add listing form.</p>
	</div>
</body>
</html>