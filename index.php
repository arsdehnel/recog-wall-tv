<?php
	// get our parameters for user in the form but also in the curl requests
	$params = array("client_id" => array_key_exists('client_id', $_POST) ? $_POST['client_id'] : '01d64c87b96d4c18a1cb22fadcd41335',
		  			"client_secret" => array_key_exists('client_secret', $_POST) ? $_POST['client_secret'] : 'bed6c1f720154c8b90fdcec2d2a94537',
		  			"hostname" => array_key_exists('hostname', $_POST) ? $_POST['hostname'] : '',
		  			"resource" => array_key_exists('resource', $_POST) ? $_POST['resource'] : '',
		  			"querystring" => array_key_exists('querystring', $_POST) ? $_POST['querystring'] : '',
					"grant_type" => "client_credentials");

	// construct this once here so we can use it a couple times later
	$endpoint = $params['hostname'].$params['resource'];
?>

<html>
<head>
	<style type="text/css">
		.form-row {
			font-size: 1.2rem;
			display: flex;
		}
		.form-row label {
			flex: 1 0 200px;
		}
		input {
			flex: 3 0 600px;
			font-size: 1.2rem;
		}
		.json-renderer {
		    width: 90%;
		    margin:  0 auto;
		}
	</style>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.js"></script>
	<script src="jquery.json-viewer.js"></script>
	<link rel="stylesheet" href="jquery.json-viewer.css"/>
</head>
<body>
	<h1>OAuth 2 Tester</h1>
	<h2>Parameters</h2>
	<form action="/" method="post">
		<div class="form-row">
			<label for="hostname">Hostname:</label>
			<input type="text" name="hostname" value="<?php echo $params['hostname'];?>">
		</div>
		<div class="form-row">
			<label for="resource">Resource Path:</label>
			<input type="text" name="resource" value="<?php echo $params['resource'];?>">
		</div>
		<div class="form-row">
			<label for="client_id">Client ID:</label>
			<input type="text" name="client_id" value="<?php echo $params['client_id'];?>">
		</div>
		<div class="form-row">
			<label for="client_secret">Client Secret:</label>
			<input type="text" name="client_secret" value="<?php echo $params['client_secret'];?>">
		</div>
		<div class="form-row">
			<label for="querystring">Querystring:</label>
			<input type="text" name="querystring" value="<?php echo $params['querystring'];?>">
		</div>
		<button type="submit">Submit</button>
	</form>
	<?php 
	
	$postData = "";
	foreach($params as $k => $v)
	{
	   $postData .= $k . '='.urlencode($v).'&';
	}
	$postData = rtrim($postData, '&');

	$curl = curl_init($endpoint."/oauth2/token");
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_HEADER,'Content-Type: application/x-www-form-urlencoded');
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($status != 200) {
	  throw new Exception("Error: call to URL $endpoint failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl) . "\n");
	}
	curl_close($curl);

	echo "<hr>";
	echo "<h2>Auth token response...</h2>";
	echo "<textarea id='auth-json-response' style='display: none'>";
	print_r($json_response);
	echo "</textarea>";
	echo '<pre id="auth-json-renderer" class="json-renderer"></pre>';
	$response_obj = json_decode($json_response);
	echo '<hr>';

    $headers = array( 
        "Authorization: Bearer " . $response_obj->access_token
    ); 

	$curl = curl_init($endpoint.$params['querystring']);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	// evaluate for success response
	if ($status != 200) {
		throw new Exception("Error: call to URL $endpoint failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl) . "\n");
	}
	curl_close($curl);

	echo "<h2>Making API request...</h2>";
	echo "<textarea id='api-json-response' style='display: none'>";
	print_r($json_response);
	echo "</textarea>";
	echo '<pre id="api-json-renderer" class="json-renderer"></pre>';

?>
<script>
var authdata = JSON.parse($('#auth-json-response').val());
$('#auth-json-renderer').jsonViewer(authdata);

var apidata = JSON.parse($('#api-json-response').val());
$('#api-json-renderer').jsonViewer(apidata,{collapsed: true});
</script>
</body>	
</html>