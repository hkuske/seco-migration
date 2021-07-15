<?php
//////////////////////////////////////////////////////////
// When there are files with non-ANSI chars 
// the function must run in UNIX
// Windows is not able to upload non-ANSI files
// This function searchs the already existing notes
// and restarts the upload of the files
//////////////////////////////////////////////////////////

$country = "";
if (substr($argv[1],0,8) == 'country='){
	$country=substr($argv[1],8);
}
if ($_REQUEST['country'] != ""){
	$country = $_REQUEST['country'];
}

if ($country == ""){
	echo "USAGE: \n<br>
		  http://server/path/notes_full_reload.php?country=xx (from a browser) \n<br> 
		  php -f notes_full_reload.php country=xx (from a command line) \n<br>
		  ";
	die();
}

//new_way, call function with php -f note_full_a.php&country=xx
$in_file = $country."_notes_dump.csv";

// Directory where the BLOBKEY named files reside which are renamed to ID:
$dir = ".\\data\\";
$dir = "./data/";
$in_file = $dir.$in_file;

$username = "jim";
$password = "jim";
$migrator = "1"; 

$username = "datam";
$password = "bgG14Cz5ZJQd26p";
$migrator = "967d9858-549c-11ea-8670-0622da69c7ea"; //ZTEST + PROD

$base_url = "http://localhost/demo930ent/rest/v10";
$base_url = "https://secotoolsab-prod.sugaropencloud.eu/rest/v10";  //prod
$base_url = "https://secotoolsab-test.sugaropencloud.eu/rest/v10";  //test

$platform = "attachments";
$platform = "migration";


ini_set('max_execution_time', 0);
$script_start = time();
$time_start = time();					 

//////////////////////////////////////////////////////////
//Login - POST /oauth2/token
//////////////////////////////////////////////////////////

$login_url = $base_url . "/oauth2/token";
$logout_url = $base_url . "/oauth2/logout";										   

$oauth2_token_arguments = array(
    "grant_type" => "password",
    //client id/secret you created in Admin > OAuth Keys
    "client_id" => "sugar",
    "client_secret" => "",
    "username" => $username,
    "password" => $password,
    "platform" => $platform
);

$oauth2_token_response = call($login_url, '', 'POST', $oauth2_token_arguments);
print_r($oauth2_token_response);
echo "<hr>";

if ($oauth2_token_response->access_token == "") die("No Login");

$time_max = $oauth2_token_response->expires_in - 60;

//////////////////////////////////////////////////////////
//READ CSV file and send Notes
//////////////////////////////////////////////////////////

$row = 0;
if (($handle = fopen($in_file, "r")) !== FALSE) {
//  while (($data = fgetcsv($handle, 1000, ';', '"')) !== FALSE) {
    while (($datas = fgets($handle)) !== FALSE) {
		
		$max_lines = 30;
		$anz_tab = substr_count($datas,"\t");
		while (($anz_tab < 11)&&($max_lines>0)) {
			$datas .= fgets($handle);
			$anz_tab = substr_count($datas,"\t");
			$max_lines--;
		}
		
		echo "<hr>";
		echo bin2hex($datas);
		echo "<hr>";
		
		$data = explode("\t",$datas);

		if ((time()-$time_start)>$time_max) {
            call($logout_url, '', 'POST', $oauth2_token_arguments);
			$oauth2_token_response = call($login_url, '', 'POST', $oauth2_token_arguments);
			print_r($oauth2_token_response);
			echo "<hr>";		
            $time_start = time();
		}
		
		$row++;

//EXIT			
		if ($row > 4) break;	// STOP for TEST
//		if ($row > 12001) break;	// STOP for TEST
//		if ($row < 11719) continue;
//EXIT			

		$num = count($data);
        $DEBUG = "$row|$num|";
        for ($c=0; $c < $num; $c++) {
            $DEBUG .= $data[$c] . "|";
        }
		$DEBUG .= "|</br>\n";		

//		continue;
// INPUT FILE:
// 0-"BLOBKEY";1-"Filesize";2-"Filename";3-"Extension";4-"CustomerNumber";
// 5-"Description";6-"PREVCUSTNUM";7-"SalesRep";8-"Subject";9-"Subsidiary";
// 10-"SECO_Salesman";11-"CompanyName"
// "ZSVK-783E68";"0";"";"";"";"ADAMCIC MARKO s.p. - Company";"ILIK-73YBDQ-001";"";"ADAMCIC MARKO s.p. - Company";"";"";""
//  REIR-5TVDEA|75264|Angebot 232 VA Traisen 03.12.03.doc|doc|||VOEST-ALPINE GIESZEREI TRAISEN - Herr Johann Muck|ERKH-4WENRP-001||Angebot 232 Capto aus Vorführpool|||||	
		
/*
    8       "name": "test",
    5       "description": "description",
            "file_mime_type": "",
    3       "file_ext": "",
            "file_source": "",
    1       "file_size": 0,
    2       "filename": "",
            "portal_flag": false,
            "embed_flag": false,
            "following": false,
            "my_favorite": false,
    10      "seco_salesman": "salesman",
            "infolink_flag": false,
    7       "il_salesrep": "",
    6       "il_prevcustnum": "",
            "il_customernumber": "",
    0       "il_blobkey": "",
*/

// NO Header
//		if ($row == 1) continue;	 
		
		//////////////////////////////////////////////////////////   			
		//Search note record - GET /<module>/
		//////////////////////////////////////////////////////////   			
	
		$note_id = "";
		$url = $base_url . '/Notes';
		
		$note_search = array(
			"filter" => array(
				array(
					"il_blobkey" => $data[0]					   
				)
			),
			"max_num" => 1,
			"offset" => 0,
			"fields" => "id",
		);
		$DEBUG .= "## SEARCH NOTE: ".print_r($note_search,true)." ##</br>\n";
	
		$note_respsearch = call($url, $oauth2_token_response->access_token, 'GET', $note_search);
		$DEBUG .= "## SEARCH RESULT: ".print_r($note_respsearch,true)." ##</br>\n";
		
		if (count($note_respsearch->records) == 0) {
			$DEBUG .= "NOTE NOT FOUND ". $data[0] ." ##</br>\n";
			continue;  // Note does not exist
		}	

        $note_id = $note_respsearch->records[0]->id;
		$prev_cust = $data[6];
		
		if ($note_id != "") {
			
			//////////////////////////////////////////////////////////   			
			// NOTE record is createed
			// file must be uploaded 
			// Alternative 2 : upload the file by REST
			//////////////////////////////////////////////////////////   			
			
			
			// send the file via REST
			//////////////////////////////////////////////////////////   			
			//Upload note file - POST /Notes/<id>/file/filename
			//////////////////////////////////////////////////////////   			
			
			if ($data[1] != 0) {
				$url = $base_url . "/Notes/".$note_id."/file/filename";
				
				if ((version_compare(PHP_VERSION, '5.5') >= 0)) {
					$filedata = new CURLFile(realpath($dir.$data[0]),"",$data[2]);
				} else {
					$filedata = '@'.realpath($dir.$data[0]);
				}
				$file_arguments = array(
					"format" => "sugar-html-json",
					"delete_if_fails" => true,
					"oauth_token" => $oauth2_token_response->access_token,
					'filename' => $filedata,
				);
				$DEBUG .= "## UPLOAD FILE: ".$note_id. "#" .print_r($file_arguments,true) . "<br>\n";
				$file_response = call($url, $oauth2_token_response->access_token, 'POST', $file_arguments, false,false,true);
				$DEBUG .= "## UPLOAD RESPONSE: ".print_r($file_response,true) . "<br>\n";
				$DEBUG .= "<hr>";	
				if ( empty($file_response) ) {
					$DEBUG .= "EMPTY UPLOAD RESPONSE<hr>";
					break;
				}
			}		
		}	
        echo $DEBUG; $DEBUG="";
			
    }
    fclose($handle);
}

$script_runtime = time()-$script_start;
$DEBUG .= "TIME needed: ".$script_runtime."<br>\n";
echo $DEBUG; $DEBUG="";


////////////////////////////////////////////////////////////////////
// END OF MAIN
////////////////////////////////////////////////////////////////////


/**
 * Generic function to make cURL request.
 * @param $url - The URL route to use.
 * @param string $oauthtoken - The oauth token.
 * @param string $type - GET, POST, PUT, DELETE. Defaults to GET.
 * @param array $arguments - Endpoint arguments.
 * @param array $encodeData - Whether or not to JSON encode the data.
 * @param array $returnHeaders - Whether or not to return the headers.
 * @param array $filenHeader - Whether or not to upload a file
 * @return mixed
 */
function call(
    $url,
    $oauthtoken='',
    $type='GET',
    $arguments=array(),
    $encodeData=true,
    $returnHeaders=false,
	$fileHeader=false
)
{
    $type = strtoupper($type);

    if ($type == 'GET')
    {
        $url .= "?" . http_build_query($arguments);
    }

    $curl_request = curl_init($url);

    if ($type == 'POST')
    {
        curl_setopt($curl_request, CURLOPT_POST, 1);
    }
    elseif ($type == 'PUT')
    {
        curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "PUT");
    }
    elseif ($type == 'DELETE')
    {
        curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "DELETE");
    }

    curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($curl_request, CURLOPT_HEADER, $returnHeaders);
    curl_setopt($curl_request, CURLOPT_SSL_VERIFYHOST, 0);  // wichtig
    curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);  // wichtig
    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($curl_request, CURLOPT_ENCODING ,"UTF-8"); // Dateinamen mit Umlaut

    if (!empty($oauthtoken)) 
    {
		if ($fileHeader) {
			curl_setopt($curl_request, CURLOPT_HTTPHEADER, array(
				"oauth-token: {$oauthtoken}"));
		} else {
            curl_setopt($curl_request, CURLOPT_HTTPHEADER, array(
				"oauth-token: {$oauthtoken}",
				"Content-Type: application/json"));
		}		
    }
    else
    {
        curl_setopt($curl_request, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json"));
    }

    if (!empty($arguments) && $type !== 'GET')
    {
        if ($encodeData)
        {
            //encode the arguments as JSON
            $arguments = json_encode($arguments);
        }
        curl_setopt($curl_request, CURLOPT_POSTFIELDS, $arguments);
    }

    $result = curl_exec($curl_request);
	
    if ($returnHeaders)
    {
        //set headers from response
        list($headers, $content) = explode("\r\n\r\n", $result ,2);
        foreach (explode("\r\n",$headers) as $header)
        {
            header($header);
        }

        //return the nonheader data
        return trim($content);
    }

    curl_close($curl_request);

    //decode the response from JSON
    $response = json_decode($result);

    return $response;
}
?>