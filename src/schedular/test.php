<?php
$url = 'http://localhost/nnn/schedular/index.php';
//Initialise the cURL var
$ch = curl_init();

//Get the response from cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//Set the Url
curl_setopt($ch, CURLOPT_URL, $url);
$data = file_get_contents('data/testdata');
//Create a POST array with the file in it
$postData = array(
    'GAN' => $data,
	'PROJECT' => 'myproject',
	'DEBUG' => '1',
);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

// Execute the request
$response = curl_exec($ch);

echo $response;

?>