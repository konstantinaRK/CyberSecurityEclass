<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
include '../../include/baseTheme.php';

$nameTools = $langCleanUp;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);

// Initialise $tool_content
$tool_content = "";

//Show message if exists
if(isset($msg))
{
	switch ($msg){
		case 7: {//CSRF ATTACK
			$message = "Form time expired. Refresh and try again.";
			$urlText = "";
			$type = "caution_small";
			break;
		}
		case 8: {//CSRF ATTACK
			$message = "Whiper, no swiping.";
			$urlText = "";
			$type = "caution_small";
			break;
		}
		default:die("invalid message id");
	}

	$tool_content .=  "<p class=\"$type\">$message<br><a href=\"../../index.php\">$urlText</a></p><br/>";
}

if (isset($_POST['submit'])) {
	if (!isset($_POST['form_token'])){
		// EXPIRED - ASK USER TO RELOAD PAGE
		header("location:" . $_SERVER['PHP_SELF'] . "?msg=7");
		exit();
	}

	if ($_SESSION['token'] == $_POST['form_token']) {
		if (time() >= $_SESSION['token-expire']) {
			// EXPIRED - ASK USER TO RELOAD PAGE
			header("location:" . $_SERVER['PHP_SELF'] . "?msg=7");
			exit();
		}
		else {
			foreach (array('temp' => 2, 'garbage' => 5, 'archive' => 1, 'tmpUnzipping' => 1) as $dir => $days) {
				$tool_content .= sprintf("<p class=kk>$langCleaningUp</p>", $days,
					($days == 1)? $langDaySing: $langDayPlur, $dir);
				cleanup("${webDir}courses/$dir", $days);
			}
		}
	} else {
		// EXPIRED - ASK USER TO RELOAD PAGE
		header("location:" . $_SERVER['PHP_SELF'] . "?msg=8");
		exit();
	}
} else {

	// mine
	// GENERATE THE TOKEN, ADD AN EXPIRY TIMESTAMP
	session_start();
	$length = 32;
	$ses_tok = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length);
	$_SESSION['token'] = $ses_tok;
	// 10 minutes = 60 seconds  = 60
	$_SESSION['token-expire'] = time() + 60;
	// end mine

	$tool_content .= "
    <table width='99%' class='FormData' align='left'>
    <tbody>
    <tr>
      <th width='220'>&nbsp;</th>
      <td>$langCleanupInfo</td>
    </tr>
    <tr>
      <th width='220'>&nbsp;</th>
      <td>
         <form method='post' action='$_SERVER[PHP_SELF]'>
         <input type=\"hidden\" name=\"form_token\" value=\"$ses_tok\"/>
	     <input type='submit' name='submit' value='$langCleanup'>
         </form>
      </td>
    </tr>
	</tbody>
    </table>
    <br />";
}


$tool_content .= "<br /><br /><p align=right><a href=\"index.php\" class=mainpage>$langBackAdmin&nbsp;</a></p>";


/*****************************************************************************
                DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');


// Remove all files under $path older than $max_age days
// Afterwards, remove $path as well if it points to an empty directory
function cleanup($path, $max_age)
{
	$max_age_seconds = $max_age * 60 * 60 * 24;
	$files_left = 0;
	if ($dh = @opendir($path)) {
		while (($file = readdir($dh)) !== false) {
			if ($file != '.' and $file != '..') {
				$filepath = "$path/$file";
				if (is_dir($filepath)) {
					if (cleanup($filepath, $max_age) == 0) {
						rmdir($filepath);
					} else {
						$files_left++;
					}
				} else {
					if (file_older($filepath, $max_age_seconds)) {
						unlink($filepath);
					} else {
						$files_left++;
					}
	        		}
			}
		}
		closedir($dh);
	}
	return $files_left;
}

// Returns true if file pointed to by $path is older than $seconds
function file_older($path, $seconds)
{
	if (filemtime($path) > time() - $seconds) {
		return false;
	} else {
		return true;
	}
}

?>
