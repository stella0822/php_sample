<?php
/*+********************************************************************************
  * The contents of this file are subject to the vtiger CRM Public License Version 1.0
  * ("License"); You may not use this file except in compliance with the License
  * The Original Code is:  vtiger CRM Open Source
  * The Initial Developer of the Original Code is vtiger.
  * Portions created by vtiger are Copyright (C) vtiger.
  * All Rights Reserved.
  *********************************************************************************/

//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;
$current_module_strings = return_module_language($current_language, 'Users');

define("IN_LOGIN", true);

include_once('vtlib/Vtiger/Language.php');
	
// Retrieve username and password from the session if possible.
if(isset($_SESSION["login_user_name"]))
{
	if (isset($_REQUEST['default_user_name']))
		$login_user_name = trim(vtlib_purify($_REQUEST['default_user_name']), '"\'');
	else
		$login_user_name =  trim(vtlib_purify($_REQUEST['login_user_name']), '"\'');
}
else
{
	if (isset($_REQUEST['default_user_name']))
	{
		$login_user_name = trim(vtlib_purify($_REQUEST['default_user_name']), '"\'');
	}
	elseif (isset($_REQUEST['ck_login_id_vtiger'])) {
		$login_user_name = getUserName($_REQUEST['ck_login_id_vtiger']);
	}
	else
	{
		$login_user_name = $default_user_name;
	}
	$_session['login_user_name'] = $login_user_name;
}

$current_module_strings['VLD_ERROR'] = base64_decode('UGxlYXNlIHJlcGxhY2UgdGhlIFN1Z2FyQ1JNIGxvZ29zLg==');

// Retrieve username and password from the session if possible.
if(isset($_SESSION["login_password"]))
{
	$login_password = trim(vtlib_purify($_REQUEST['login_password']), '"\'');
}
else
{
	$login_password = $default_password;
	$_session['login_password'] = $login_password;
}

if(isset($_SESSION["login_error"]))
{
	$login_error = $_SESSION['login_error'];
}
if(isset($_SESSION["login_code_error"]))

{

	$login_code_error = $_SESSION['login_code_error'];

}








if(isset($_COOKIE['LOGIN'])){
	$tmpArr = explode('*',$_COOKIE['LOGIN']);
	$count = $tmpArr[1];
	if($tmpArr[0] == $_SESSION['login_user_name']){
		if($count == 4){
			setcookie("LOGIN",$_SESSION['login_user_name'] . '*5');
			echo "<script>alert('Continuous wrongly input more than 5 times. The account will be locked for an hour.')</script>";
			
			//mail1
			require_once('modules/Emails/mail.php');
			$notificationname="UserLocked";
			$notificationsubject="The account of '".$_SESSION['login_user_name']."' is locked";
			$notificationbody="Dear Administrator,
			<br><br>The account of '".$_SESSION['login_user_name']."' is locked at ".date('Y-m-d H:i:s').", the ip address is ".$_SERVER['REMOTE_ADDR'].".
			<br><br>Thanks.";

			send_mail('Users','shizq@sibotech.net',$HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID,$notificationsubject,$notificationbody);
		

			
			global $adb;
			$query = 'Insert into vtiger_user_loginlocked (name, locktime, ip) values (?,?,?)';
			$params = array($_SESSION['login_user_name'], date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR']);
			$result = $adb->pquery($query, $params);
			$query="select locked_num from vtiger_users where user_name='".$_SESSION['login_user_name']."'";
			$result = $adb->query($query);
			if(is_array($result)) {foreach($result as $row){$templocknum=$row['locked_num'];}}
			$result=$templocknum+1;
			
			
			
			//mail2
			if($result>=10){
				require_once('modules/Emails/mail.php');
				$notificationname="UserClosed";

				$notificationsubject="The account of '".$_SESSION['login_user_name']."' is closed";

				$notificationbody="Dear Administrator,

		        <br><br>The account of '".$_SESSION['login_user_name']."' is closed, the ip address is  ".$_SERVER['REMOTE_ADDR'].".

		        <br><br>Thanks.";
				send_mail('Users','shizq@sibotech.net',$HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID,$notificationsubject,$notificationbody);

			}
			
			
			
			
			
			$query = "update vtiger_users set locked_num=".$result." where user_name='".$_SESSION['login_user_name']."'";
			//echo "<script>alert('555".$query."')</script>";
			$result = $adb->query($query);
			
		}else if($count<4){
			$count++;
			setcookie("LOGIN",$_SESSION['login_user_name'] . '*' . $count);
		}

	}else{
		setcookie("LOGIN",$_SESSION['login_user_name'] . '*1');
	}
}else{
	setcookie("LOGIN",$_SESSION['login_user_name'] . '*1');
}
//echo "<script>alert('444".$_COOKIE['LOGIN']."')</script>";







require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once("include/utils/utils.php");
require_once 'vtigerversion.php';

global $currentModule, $moduleList, $adb, $vtiger_current_version;
$image_path="include/images/";

$app_strings = return_application_language('en_us');

$smarty=new vtigerCRM_Smarty;
$smarty->assign("APP", $app_strings);

if(isset($app_strings['LBL_CHARSET'])) {
	$smarty->assign("LBL_CHARSET", $app_strings['LBL_CHARSET']);
} else {
	$smarty->assign("LBL_CHARSET", $default_charset);
}

$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("PRINT_URL", "phprint.php?jt=".session_id().$GLOBALS['request_string']);
$smarty->assign("VTIGER_VERSION", $vtiger_current_version);


$sql="select * from vtiger_organizationdetails";
$result = $adb->pquery($sql, array());
//Handle for allowed organation logo/logoname likes UTF-8 Character
$companyDetails = array();
$companyDetails['name'] = $adb->query_result($result,0,'organizationname');
$companyDetails['website'] = $adb->query_result($result,0,'website');
$companyDetails['logo'] = decode_html($adb->query_result($result,0,'logoname'));
$smarty->assign("COMPANY_DETAILS",$companyDetails);

if(isset($login_error) && $login_error != "") {
	$smarty->assign("LOGIN_ERROR", $login_error);
}
if(isset($login_code_error) && $login_code_error != "") {

	$smarty->assign("LOGIN_CODE_ERROR", $login_code_error);

}

$smarty->display('Login.tpl');

?>