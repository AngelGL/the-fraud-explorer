<?php

/*
 * The Fraud Explorer 
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for agent setup
 */

session_start();
include "inc/global-vars.php";

if(empty($_SESSION['connected']))
{
 	header ("Location: ".$serverURL);
 	exit;
}

error_reporting(0);
include "inc/open-db-connection.php";

function filter($variable)
{
	return addcslashes(mysql_real_escape_string($variable),',-<>"');
}

$agent_enc=filter($_GET['agent']);
$agent_dec=base64_decode(base64_decode($agent_enc));

if (isset($_GET['alias'])) 
{
	$alias=filter($_POST['alias']);
	mysql_query(sprintf("UPDATE t_agents SET name='%s' WHERE agent='%s'",$alias,$agent_dec));
}

if (isset($_GET['owner'])) 
{
        $owner=filter($_POST['owner']);
        mysql_query(sprintf("UPDATE t_agents SET owner='%s' WHERE agent='%s'",$owner,$agent_dec));
}

if (isset($_GET['gender']))
{
        $gender=filter($_POST['gender']);
        mysql_query(sprintf("UPDATE t_agents SET gender='%s' WHERE agent='%s'",$gender,$agent_dec));
}

header ("location:  mainMenu");

include "inc/close-db-connection.php";

?>

</body>
</html>
