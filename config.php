<?php 
/**
 * This configuration file is used by the Server Side application for Configuration information.
 * One of the first things a server side module will do is to require this file. The client side
 * of the application will use the file config.json to store its configuration information.
 *---------------------------------------------------------------------------------------------------
 * Programmer		Date 			Notes
 * Frank Ilagan		03/19/2014		Added comments
 */
$appconfig = array();

$appconfig['filename'] = "klaviyo_upload_%s_%s.csv";

//---------------------------------------------------------------------------------------------
//------------------------------------------ SFTP ---------------------------------------------
//---------------------------------------------------------------------------------------------
$sftp = [];
$sftp['host'] = '2.4.4.9';
$sftp['port']  = '';
$sftp['username']  = 'morlib';
$sftp['pw'] = 'morlib';

$appconfig['sftp'] = $sftp;

?>
