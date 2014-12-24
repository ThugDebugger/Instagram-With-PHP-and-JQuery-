<?php

include 'connection.php';

$i = 0;
while ($i < 10000)
{
	mysql_query("INSERT INTO Dsp_data (`DSP_CommentAmt`) VALUES(1)") or die (mysql_error());
	$i++;
}






?>