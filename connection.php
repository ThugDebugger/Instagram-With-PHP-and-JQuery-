<?php
	


   //File used for connecting to a database
   
   //Location of the DB
   $dbhost = 'localhost';
   //User name of the DB
   $dbuser = 'root';
   //Password of the DB
   $dbpass ='';
   //Name of the DB
   $db = 'dsp_instagram';

    // Connection to the database SERVER. Parameters include: Host, User, and Password
   $conn = mysql_connect($dbhost,$dbuser,$dbpass);

   //connection to the ACTUAL database. Selects the particular database passed in the parameter
   mysql_select_db($db);




?>