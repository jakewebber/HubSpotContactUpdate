<?php
/*
Created by Jacob Webber https://github.com/jakewebber
Last updated: 3 October 2016

HubSpot contacts API example from http://hubhacker.com author Regan Starr used as template.

This code will update all contacts in a HubSpot contact database in the following ways:
	1) Set to ALL CAPS: First/Last name, job title, company, email_2, address, city.
	2) Format phone numbers as '123-456-7890'.
	3) Trim leading and trailing whitespace from these properties.
	4) Trim excess whitespace between values (greater than a single space).
And then display the formatted contact properties in an HTML table for viewing.
Runtime (in seconds) and contact-count are totaled at the top of the webpage on finish.
*/

$hubspotApiKey = "API_KEY"; // <-- SET YOUR API KEY HERE
ini_set('max_execution_time', 0); // 0 will enable the script to run as long as needed.
$count = 0; // Tracking the total number of contacts modified (total in your database).

/* Setting output HTML table format for viewing */
echo "<style> table, th, td {
		border: 1px solid black;
		border-collapse: collapse;
		}
		table tr:nth-child(even) {
		background-color: #eee;
		}
		table tr:nth-child(odd) {
		background-color:#fff;
		}
	</style>
	<table style=\"width:100%\;font-size:50%\">
	<tr><th>First Name</th><th>Last Name</th><th>Job Title</th><th>Company</th><th>Phone</th><th>Email_2</th><th>Address</th><th>City</th><th>Job Choice</th><th>Quantity</th></tr>";

/* DoWhile gets pages of HubSpot contacts as an array of desired contact properties to format */
do {
	/* A few parameters for API call */
	$parametersArray = array(
		'hapikey' => $hubspotApiKey,
		'count' => '100',
	);

	/* 'vidOffset' sets start for next page of contacts. Set the variable $vidOffset at the end of this loop, which means that this block will not execute for the first 'count' contacts, but will execute every time after that. */
	if(isset($vidOffset)){
		$parametersArray['vidOffset'] = $vidOffset;
	}

	$urlParametersString = http_build_query($parametersArray);

	/* Set url for API call get_contacts
	http://developers.hubspot.com/docs/methods/contacts/get_contacts
	Retrieving: firstname, lastname, jobtitle, company, phone, mobilephone, email, address, city */
	$url = "https://api.hubapi.com/contacts/v1/lists/all/contacts/all?$urlParametersString&property=firstname&property=lastname&property=phone&property=mobilephone&property=email_2&property=jobtitle&property=company&property=address&property=city;

	 /* Retrieving JSON from URL */
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$responseJson = curl_exec($ch);
	curl_close($ch);

	$responseArray = json_decode($responseJson, true); //JSON to array

	/* Initialize blank array to store all contacts. */
	if(!isset($allContactsArray)){
		$allContactsArray = array();
	}

	/* Loop over a page of contacts and get desired properties to modify */
	foreach($responseArray['contacts'] as $contact){
		/* Catching undefined index error. If the property is not in the array, set equal to "" */
		$vid = 			$contact['vid'];
		$firstName = 	(isset($contact['properties']['firstname']['value'])) 	? $contact['properties']['firstname']['value'] : "";
		$lastName = 	(isset($contact['properties']['lastname']['value'])) 	? $contact['properties']['lastname']['value'] : "";
		$jobtitle = 	(isset($contact['properties']['jobtitle']['value'])) 	? $contact['properties']['jobtitle']['value'] : "";
		$company = 		(isset($contact['properties']['company']['value'])) 	? $contact['properties']['company']['value'] : "";
		$phone = 		(isset($contact['properties']['phone']['value'])) 		? $contact['properties']['phone']['value'] : "";
		$mobilePhone = 	(isset($contact['properties']['mobilephone']['value'])) ? $contact['properties']['mobilephone']['value'] : "";
		$email_2 = 		(isset($contact['properties']['email_2']['value'])) 	? $contact['properties']['email_2']['value'] : "";
		$address = 		(isset($contact['properties']['address']['value'])) 	? $contact['properties']['address']['value'] : "";
		$city = 		(isset($contact['properties']['city']['value'])) 		? $contact['properties']['city']['value'] : "";
	
		
		/* Create a contact sub-array from desired properties */
		$singleContactArray = array(
			// Standard contact fields to format
			'vid' => 			$vid,
			'firstName' => 		$firstName,
			'lastName' => 		$lastName,
			'jobTitle' => 		$jobtitle,
			'company' => 		$company,
			'phone' => 			$phone,
			'mobilePhone' => 	$mobilePhone,
			'email_2' => 		$email_2,
			'address' => 		$address, 
			'city' => 			$city
		);

		/* Add the single contact array to $allContactsArray */
		array_push($allContactsArray, $singleContactArray);
		$count++; // Increment contact counter.
	}

	$vidOffset = $responseArray['vid-offset']; // Marks where the contact list left off in progress.

} while ($responseArray['has-more'] == true); //Set false to test on first page of contacts, true to loop all contact pages.

// We've just looked up all of our HubSpot contacts and stored their vid, firstname and lastname inside of $allContactsArray. Next, we're going to loop through every single contact and check if it is Formatted correctly. If it's not, then we're going to fix it and then update it in our HubSpot database with an api call.

/* Loop through all contacts in allContactsArray and format values, then call HubSpot API to push contact changes */
foreach($allContactsArray as $contact){

	/* Capitalizing & trimming: first name, last name, job title, company, email2, address, city */
	$firstNameFormatted = 	trim( preg_replace("/[[:blank:]]+/"," ", strtoupper($contact['firstName']) ) );
	$lastNameFormatted = 	trim( preg_replace("/[[:blank:]]+/"," ", strtoupper($contact['lastName']) ) );
	$jobTitleFormatted = 	trim( preg_replace("/[[:blank:]]+/"," ", strtoupper($contact['jobTitle']) ) );
	$companyFormatted = 	trim( preg_replace("/[[:blank:]]+/"," ", strtoupper($contact['company']) ) );
	$email2Formatted = 		trim( preg_replace("/[[:blank:]]+/"," ", strtoupper($contact['email_2']) ) );
	$addressFormatted = 	trim( preg_replace("/[[:blank:]]+/"," ", strtoupper($contact['address']) ) );
	$cityFormatted = 		trim( preg_replace("/[[:blank:]]+/"," ", strtoupper($contact['city']) ) );
	

	/* Formatting phone and mobilePhone numbers: 'xxx-xxx-xxxx' */
	$phoneFormatted = trim( preg_replace("/[[:blank:]]+/"," ", formatPhone($contact['phone']) ) );
	$mobilePhoneFormatted = trim( preg_replace("/[[:blank:]]+/"," ", formatPhone($contact['mobilePhone']) ) );

	/* Construct array with newly formatted values for this contact */
	$postData = array(
		'properties' => array(
			array(
				'property' => 'firstname',
				'value' => $firstNameFormatted
			),
			array(
				'property' => 'lastname',
				'value' => $lastNameFormatted
			),
			array(
				'property' => 'jobtitle',
				'value' => $jobTitleFormatted
			),
			array(
				'property' => 'company',
				'value' => $companyFormatted
			),
			array(
				'property' => 'email_2',
				'value' => $email2Formatted
			),
			array(
				'property' => 'phone',
				'value' => $phoneFormatted
			),
			array(
				'property' => 'mobilephone',
				'value' => $mobilePhoneFormatted
			),
			array(
				'property' => 'address',
				'value' => $addressFormatted
			),
			array(
				'property' => 'city',
				'value' => $cityFormatted
			)
		)
	);

	$contactId = $contact['vid']; // Get visitor ID for current contact to use in API call update.

	/* Printing HTML table of contact values that will be formatted */
	echo "<tr><td>", $firstNameFormatted, "</td><td>", $lastNameFormatted, "</td><td>", $jobTitleFormatted, "</td><td>", $companyFormatted, "</td><td>",
		$phoneFormatted, "</td><td>", $email2Formatted, "</td><td>", $addressFormatted, "</td><td>", $cityFormatted, "</td><td>"</tr>";

	/* Set the url that we will use in our API call.
	http://developers.hubspot.com/docs/methods/contacts/update_contact */
	$url = "https://api.hubapi.com/contacts/v1/contact/vid/$contactId/profile?hapikey=$hubspotApiKey";

	/* Begin the api call that will update the first and last name of this contact. */
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$response = curl_exec($ch);
	$postStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	/* Catching HTTP Response Code errors */
	if($postStatus == 400){
		echo "Error: HubSpot API call to $url failed.", "<br>", "400 A property doesn't exist, or a property value is invalid.", "<br>";
	}elseif($postStatus == 401){
		echo "Error: HubSpot API call to $url failed.", "<br>", "401 Unauthorized request. Check for expired access token or incorrect API key.", "<br>";
	}elseif($postStatus == 500){
		echo "Error: Hubspot API call to $url failed.", "<br>", "500 Internal Server Error. Something with HubSpot is screwed up and there's probably nothing you can do :(", "<br>";
	}elseif($postStatus == 0){
		echo "Error: Hubspot API call to $url failed.", "<br>", "Error code 0: Could be different things, but check that API calls to this key are coming from one place at a time.", "<br>";
	}elseif($postStatus != 204){
		echo "Error: HubSpot API call to $url failed.", "<br>", "Error not listed in HubSpot API, check error codes. Response: $response Status: $postStatus", "<br>";
	}
	curl_close($ch);
	
}
// End of loop through all contacts.
echo "<h1>{$count} contacts have been formatted successfully.</h1>";
$time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
echo "<h3>Total Processing Time: {$time} seconds.</h3>";


/* ------------------------------------------------------------
Formats a phone number (7, 10, 11 digits long) with hyphens.
Ex: 1234567890 = 123-456-7890 */
function formatPhone($phone) {
	if(!isset($phone{3})) { return ''; } // Assert that some input exists
	$phone = preg_replace("/[^0-9]/", "", $phone); // Remove any non-number chars initially
	$length = strlen($phone); // Get the length of phone number (should be 7, 10, or 11 digits)

	/* Handling number length cases */
	switch($length) {
	case 7: // Phone number is in 7-digit for local calling area
	return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
	break;
	case 10: // Phone number is standard 10 digit with area code
	return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3", $phone);
	break;
	case 11: // Phone number is standard 11 digit with area code and country code
	return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1-$2-$3-$4", $phone);
	break;
	default: // Phone number is not 7, 10, or 11 digits, make no change.
	return $phone;
	break;
	}
}

?>
