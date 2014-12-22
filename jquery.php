<?php

include 'connection.php';
set_time_limit(0);
ini_set('default_socket_timeout',100);
session_start();


/*----------Instagram API Keys (Constants)-------*/

//ID of your client (app)
define("clientID","7b31b72433ac41ac91c6fe9915c03ac5"); 
//Secret password of your client
define("clientSecret","28fb8d3719434e6098567cb3c7495988");
//URL to direct the user too once logged in successfully 
define("redirectURI","http://localhost:8081/GitHub/JQuery/jquery.php");
//Folder used to hold the pictures
define("imageDirectory", "Pics/");

//Function used to hold the curl permissions and config (for later use) to connect to Instagram
function connectToInstagram($url)
{
	//Init starts the curl session, allowing the transfer of data from the url in the parameter
	$ch = curl_init();

	curl_setopt_array($ch,
	array(
		//Sets the array of where to talk to
		CURLOPT_URL => $url,
		//Return the result of 'true' if the transfer was successful
		CURLOPT_RETURNTRANSFER => TRUE,
		//Used to check the handshake between Instagram and your website. (Should be usually set to TRUE but for demo purpoes set to FALSE)
		CURLOPT_SSL_VERIFYPEER => FALSE,
		// Means to not verify a host (just a Instagram default)
		CURLOPT_SSL_VERIFYHOST => 2
	)); //Ends the Array

	//Stores the result from executing the curl session
	$result = curl_exec($ch);
	//Closes the curl session
	curl_close($ch);
	//Returns the result of the curl session
	return $result;

};//Ends the connectToInstagram function

function getMaxID($access_token,$userID) 
{
	$url = 'https://api.instagram.com/v1/users/'.$userID.'/media/recent/?access_token='.$access_token;
	$InstagramInfo = connectToInstagram($url);
	$results = json_decode($InstagramInfo, true);
	$maxIDArray = (string)$results['pagination']['next_max_id'];
	//echo $maxIDArray;
	return (string) $maxIDArray;
	
}

function getComment($access_token,$mediaID,$userID)
{
	$flag = TRUE;
	
	//Variable used to display the current amount of comments on a photo
	$i = 1;
	//VARIABLE USED TO DISPLAY THE CURRENT PICTURE NUMBER ON
	$j = 1;
	//GETS THE CURRENT UNIX TIME FROM THE SERVER (DEFUNCT-ONLY USED FOR A HARD CODE TIME SET BELOW)
	$time = time ();
	//HARD-CODED TIME, WHICH CAME FROM THE CALLING OF THE TIME FUNCTION ABOVE. PARTICULAR CHOSEN VALUE
	$time = '1418854190';
	//Instagram endpoint used to get the comments and like status of a photo
	$url = 'https://api.instagram.com/v1/users/'.$userID.'/media/recent/?access_token='.$access_token.'&min_timestamp='.$time;
	//Function is called to iniate the connection with the Instagram API, and returns the data to $InstagramInfo
	$InstagramInfo = connectToInstagram($url);
	//Decodes the JSON array stored in Instagram Info and stores it into the $results variable
	$results =json_decode($InstagramInfo,true);
	//print_r($results);
	$code = $results['meta']['code'];

	
	//Iterates through the $userText array and assigns the values to $newText within the loop
	foreach($results['data'] as $newText)
	{
		//ASSIGNS THE PICTURE ID FROM THE API
		$pictureID = $newText['id'];
		echo "The picture ID is: ", $pictureID, "</br>";
	    echo "Showing comments from picture #", $j,"</br>";
	    //CONCATENATES THE COMMENTS TOGETHER INTO A STRING DELIMINATED BY +=
	    $commentCat = "+/";
	    //VALUE USED TO SHOW THE COMMENT NUMBER BEING DISPLAYED. 
	    $i = 1;
	    //ASSIGNS THE COMMENT RETRIVED FROM THE API
	    $userComment = $newText['comments'];
	    foreach($userComment['data'] as $newText2)
	     {
	  	    $comment = $newText2['text'];
	  	    $commentCat	.= $comment . "+/";
	  	    $user = $newText2['from']['username'];
	  	    echo "Comment number ", $i, " says: ", $comment, " and it is from: ",$user,"</br>";
	  	    //UPDATES THE SQL DATABASE WITH THE CONCATENATED STRING OF COMMENTS
	  	    mysql_query("UPDATE Dsp_data SET DSP_Comment = '$commentCat' WHERE DSP_PicID = $j ") or die (mysql_error());
	  	    //mysql_query("UPDATE Dsp_data SET DSP_Comment = CONCAT(DSP_comment,'$comment') WHERE DSP_PicID = $j ") or die (mysql_error());
	        $i = $i +1;
	     }
	   //EXECUTES IF NO COMMENTS WERE LEFT ON THE PICTURE
	   if ($i == 1)
	   {
	   	$nullValue = "No Comments";
	   	//UPDATE SQL DATABASE WITH THE $NULLVALUE
	   	mysql_query("UPDATE Dsp_data SET DSP_Comment =  '$nullValue' WHERE DSP_PicID = $j ") or die (mysql_error());
	  	echo "No comments were made on this photo</br>";
	   }

	  $j += 1;
    }//ENDS FOR EACH
   
}





//Grabs the 'code' from the URL once redirected after user log in
if($_GET['code'])
{
	//If user has already logged in, display the code below
	$code=$_GET['code'];

	//Authorization to access user's account
	$url = "https://api.instagram.com/oauth/access_token";

	//Required Instagram Permissions
	$access_token_settings = array
	    (
		'client_id' => clientID,
		'client_secret' => clientSecret,
		'grant_type' => 'authorization_code', 
		'redirect_uri' => redirectURI,
		'code' => $code,
		'access_token' =>'access_token'
		); //Ends the Array

	/*Curl allows for data tranfer from one website to another*/
	//Init starts the curl session, allowing the transfer of data from the url in the parameter
	$curl = curl_init($url);
	//Posts to the instagram API (required by their specific API)
	curl_setopt($curl, CURLOPT_POST, true);
	//Allows us to set the fields of the token once posted via the access_token_settings variable above
	curl_setopt($curl, CURLOPT_POSTFIELDS, $access_token_settings);
	//When set to 1, all the results are returned as a string (rather than echoing everything out, thus making it easier to work with)
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	//Used to check the handshake between Instagram and your website. (Should be usually set to TRUE but for demo purpoes set to FALSE)
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//Executes the start of the curl session with the session in it's parameter
	$result = curl_exec($curl);
	//Ends the curl session, and free up resources
	curl_close($curl);

	//Decodes the data recieved from curl session, and makes it readable by turning them into php variables (index's into key-value pairs)
	$results =json_decode($result,true);
	//Retrieves the access token from the results array
	$access_token = $results['access_token'];
	//echo "The access token is ",$access_token;
	//Assigns the User's ID from the USER array
	$userID = $results['user']['id'];
	//Assigns the User's Handle from the USER array
	$userHandle = $results['user']['username'];
	//Assigns the User's Legal Name from the USER array
	$userFullName = $results['user']['full_name'];
	//Assigns the User's Picturefrom the USER array
	$userPicture = $results['user']['profile_picture'];
	//prints the user's ID to the screen
	echo $userID,"</br>";
	//prints the user's Handle to the screen
	echo $userHandle,"</br>";
	//prints the user's Full Name to the screen
	echo $userFullName,"</br>";
	//prints the user's Picture to the screen
	echo('<img src=" '. $userPicture .' "/><br/>');
	getComment($access_token,getMaxID($access_token,$userID),$userID);
	//getMaxID($access_token,$userID);

	
 }

else
 //If the user is not logged in, Display the code below
{ ?>


<doctype html>
<html>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"> </script>
<body>
                    <!-- LOGIN LINK AND SCOPES -->
    <a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo clientID; ?>&redirect_uri=<?php echo redirectURI; ?>&response_type=code&scope=likes+comments+relationships"> Login </a>
</body>



</html>


<?php

}

?>