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
 * Description: Code for showing the status of a executed command
 */

session_start();
header("Cache-Control: no-store, no-cache, must-revalidate");
include "inc/global-vars.php";
include "inc/cryptography.php";

if(empty($_SESSION['connected']))
{
 	header ("Location: ".$serverURL);
 	exit;
}
error_reporting(0);

function filter($variable)
{
 	return addcslashes(mysql_real_escape_string($variable),',<>');
}

function clear_xml()
{
 	$xml_for_delete = simplexml_load_file('update.xml');
 	foreach ($xml_for_delete->version as $version) $numVersion = (int) $version['num'];
 	$numVersion++;
 	$xmlContent="<?xml version=\"1.0\"?>\r\n<update>\r\n<version num=\"" . $numVersion . "\" />\r\n";
 	$xmlContent = $xmlContent . "</update>";
 	$fp = fopen('update.xml',"w+");
	fputs($fp, $xmlContent);
 	fclose($fp);
}

function clear_xml_updater($id)
{
 	$xml_for_updater = simplexml_load_file('update.xml');
 	foreach ($xml_for_updater->version as $version) $numVersion = (int) $version['num'];
 	$numVersion++;
	$xmlContent ="<?xml version=\"1.0\"?>\r\n<update>\r\n<version num=\"" . $numVersion . "\" />\r\n";
 	$xmlContent = $xmlContent . "<token type=\"". encRijndael("updater") . "\" arg=\"\" id=\"".$id."\" agt=\"". encRijndael("none") ."\"/> \r\n";
 	$xmlContent = $xmlContent . "</update>";
 	$fp = fopen('update.xml',"w+");
 	fputs($fp, $xmlContent);
 	fclose($fp);
}

/* List of available and permitted commands */

$cmds_srv = array("uninstall","update", "module", "killprocess");

$seconds_to_complete=1200;
if (!isset($_SESSION['seconds_waiting'])) $_SESSION['seconds_waiting']=0;
$agent = $_GET['agent'];
$agent_dec = base64_decode(base64_decode($_GET['agent']));

$_SESSION['new_command']=$_SESSION['id_command'];
if ($_SESSION['new_command'] != $_SESSION['waiting_command'])
{
 	$_SESSION['seconds_waiting']=0;
}

if($_SESSION['id_command'] == 0 || !isset($_SESSION['id_command']) || $_SESSION['NRF'] == 1)
{
 	if ($_SESSION['NRF'] == 1) 
 	{
  		echo "<b>WARNING:</b> The command &lt;".$_SESSION['NRF_CMD']."&gt; was not recognized, please try again!";
 	}
 	else
 	{
  		echo "<b>STATUS:</b> Ready, enter a *valid* command to execute ...";
  		unset($_SESSION['seconds_waiting']);
 	}
}
else
{
 	$id=$_SESSION['id_command'];
	
 	if($id>0)
	{
  		include "inc/open-db-connection.php";

  		$xml=simplexml_load_file('update.xml');
  		$type = decRijndael($xml->token[0]['type']);
  		$result_a=mysql_query("SELECT finished, response FROM t_" .$agent_dec. " WHERE id_uniq_command=" . $id);
  		$data = mysql_fetch_array($result_a);

		if ($agent_dec == "all") echo "<b>STATUS:</b> command sent to all online agents! with id ".$id.". Check each reply.";
  		else if(empty($data['finished']) && !empty($type)) 
  		{
   			if (!empty($type) && !in_array($type,$cmds_srv))
   			{
    				$nrf_cmd=$type;
    				$_SESSION['NRF_CMD']=(string)$nrf_cmd;
    				echo "<b>WARNING:</b> The command &lt;".$_SESSION['NRF_CMD']."&gt; was not recognized, please try again!";	    
    				$_SESSION['NRF']=1;
    				unset($_SESSION['id_command']);    
   			}
   			else if (!empty($type) && ($_SESSION['seconds_waiting'] < $seconds_to_complete)) 
   			{
    				echo "<b>STATUS:</b> Wait for &lt;".$type ."&gt; with id ".$id." on ".$agent_dec." to complete  ..."; 
    				$_SESSION['seconds_waiting']++;
    				$_SESSION['waiting_command']=$_SESSION['id_command'];
   			}
   			else if (!empty($type))
   			{
    				echo "<b>STATUS:</b> Failed to execute command with id ".$id." on ".$agent_dec.". Please try again.";
    				sleep(5);
    				unset($_SESSION['id_command']);
    				unset($_SESSION['seconds_waiting']);
    				clear_xml();
   			}
  		}
  		else if (!empty($type) && (in_array($type,$cmds_srv)))
  		{
   			echo "<b>STATUS:</b> Reply from agent!, &lt;".$type."&gt; with id ".$id." on " .$agent_dec. " : ".$data['response'];
   			if ($type == "update") clear_xml_updater($_SESSION['id_command']);
   			unset($_SESSION['seconds_waiting']);
  		}
  		include "inc/close-db-connection.php";
 	}
}

?>
