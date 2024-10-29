<?php
/*
Plugin Name: Bad Behavior Log Reader
Version: 1.7
Plugin URI: http://www.misthaven.org.uk/blog/projects/bblogreader/
Description: Basic log viewer for Bad Behavior in WordPress
Author: David McFarlane.
Author URI: http://www.misthaven.org.uk/blog/
*/

/*

Various bits Copyright 2007,2008 David McFarlane.
Distributed under the terms of the GNU General Public License.

Wordpress options stored - dm_bblr_numrows (Number of rows to show),dm_bblr_showblocksonly (Show only blocks, not warnings), dm_bblr_showdashlink


	Changelog : 

V1.0 	Initial Release
v1.1	Moved main plugin page from Options into Management in menu structure
	Added link from activity box.
v1.3    Show block logs only option
	Allow log entries in local time (thanks to Craig's comment)
v1.4	Only show dashboard entry for admins.
v1.5	Fix to file location for dashboard (probably needs removing, dashboard doesn't work in 2.7)
	Brought list of responses up to date.
v1.6	Code cleanup. fix issues with files in plugin subdirectory. fix php notices. Fix bits to work in 2.7 .
v1.7   Use register_activation etc.

-----------------------
----- Older notes -----
-----------------------

URL for the version that was adapted - http://jonathanmurray.com/wordpress/2006/07/08/wordpress-plugins/#more-893
 
---------------------------
----- End older notes -----
---------------------------
 */

define('BBLR_VERSION', '1.7');

// For compatibility with WP 2.0

if (!function_exists('wp_die')) {
	function wp_die($msg) {
		die($msg);
	}
}

function bblr_keyInfo($key) {
$keyInfo_data = array(
	'00000000' => 'Permitted',
       	'136673cd' => 'Your Internet Protocol address is listed on a blacklist of addresses involved in malicious or illegal activity.',
	'17566707' => 'An invalid request was received from your browser. This may be caused by a malfunctioning proxy server or browser privacy software.',
	'17f4e8c8' => 'You do not have permission to access this server.',
	'21f11d3f' => 'An invalid request was received. You claimed to be a mobile Web device, but you do not actually appear to be a mobile Web device.',
	'2b021b1f' => 'You do not have permission to access this server. Before trying again, run anti-virus and anti-spyware software and remove any viruses and spyware from your computer.',	
	'2b90f772' => 'You do not have permission to access this server. If you are using the Opera browser, then Opera must appear in your user agent.',
	'35ea7ffa' => 'You do not have permission to access this server. Check your browser\'s language and locale settings.', 	
	'408d7e72' => 'You do not have permission to access this server. Before trying again, run anti-virus and anti-spyware software and remove any viruses and spyware from your computer.',
	'41feed15' => 'An invalid request was received. This may be caused by a malfunctioning proxy server. Bypass the proxy server and connect directly, or contact your proxy server administrator.',
	'45b35e30' => 'An invalid request was received from your browser. This may be caused by a malfunctioning proxy server or browser privacy software.',
	'57796684' => 'You do not have permission to access this server. Before trying again, run anti-virus and anti-spyware software and remove any viruses and spyware from your computer.',
	'582ec5e4' => 'An invalid request was received. If you are using a proxy server, bypass the proxy server or contact your proxy server administrator. This may also be caused by a bug in the Opera web browser.',
        '6c502ff1' => 'You do not have permission to access this server.',
'69920ee5' => 'An invalid request was received from your browser. This may be caused by a malfunctioning proxy server or browser privacy software.',
'799165c2' => 'You do not have permission to access this server.',
'7a06532b' => 'An invalid request was received from your browser. This may be caused by a malfunctioning proxy server or browser privacy software.',
'7ad04a8a' => 'The automated program you are using is not permitted to access this server. Please use a different program or a standard Web browser.',
'7d12528e' => 'You do not have permission to access this server.',
'939a6fbb' => 'The proxy server you are using is not permitted to access this server. Please bypass the proxy server, or contact your proxy server administrator.',
'9c9e4979' => 'The proxy server you are using is not permitted to access this server. Please bypass the proxy server, or contact your proxy server administrator.',
'a0105122' => 'Expectation failed. Please retry your request.',
'a1084bad' => 'You do not have permission to access this server.',
'a52f0448' => 'An invalid request was received. This may be caused by a malfunctioning proxy server or browser privacy software. If you are using a proxy server, bypass the proxy server or contact your proxy server administrator.',
'b40c8ddc' => 'You do not have permission to access this server. Before trying again, close your browser, run anti-virus and anti-spyware software and remove any viruses and spyware from your computer.',
'b7830251' => 'Your proxy server sent an invalid request. Please contact the proxy server administrator to have this problem fixed.',
'b9cc1d86' => 'The proxy server you are using is not permitted to access this server. Please bypass the proxy server, or contact your proxy server administrator.',
	'cd361abb' => 'You do not have permission to access this server. Data may not be posted from offsite forms.',
'c1fa729b' => 'You do not have permission to access this server. Before trying again, run anti-virus and anti-spyware software and remove any viruses and spyware from your computer.',
'd60b87c7' => 'You do not have permission to access this server. Before trying again, please remove any viruses or spyware from your computer.',
'dfd9b1ad' => 'You do not have permission to access this server.',
'e4de0453' => 'An invalid request was received. You claimed to be a major search engine, but you do not appear to actually be a major search engine.',
'e87553e1' => 'You do not have permission to access this server.',
'f0dcb3fd' => 'You do not have permission to access this server. Before trying again, run anti-virus and anti-spyware software and remove any viruses and spyware from your computer.',
'f1182195' => 'An invalid request was received. You claimed to be a major search engine, but you do not appear to actually be a major search engine.',
'f9f2b8b9' => 'You do not have permission to access this server. This may be caused by a malfunctioning proxy server or browser privacy software.',
);

if (array_key_exists($key, $keyInfo_data)) return $keyInfo_data[$key];
return array('00000000');
}


function bblr_display(){
	// Shows the actual log rows.
	// Edited v1.0 DM for presentation and paging.

	global $wpdb, $table_prefix;
	$dm_bblr_show_num = bblr_check_rows();
	$whereclause = bblr_get_whereclause();

	// Grab the number of rows in the table;
	$dm_bblr_count_rows = $wpdb->get_var("SELECT COUNT(*) FROM ".$table_prefix."bad_behavior ".$whereclause.";");
	if ($dm_bblr_count_rows > 0) { 
		// Any rows to process

		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			// Possibly need to move to next page of results
			$dm_bblr_pageno = attribute_escape($_POST['pagenotoview']);
			$dm_bblr_offset = 0;
			if (is_numeric($dm_bblr_pageno)) {
				if ($dm_bblr_pageno > 1) {
					$dm_bblr_offset = ($dm_bblr_show_num * ($dm_bblr_pageno - 1));
				} else {$dm_bblr_pageno = 1; }
			}
		} else {
			// Must be viewing first page as no button pressed.
			// echo "MBVFP";
			$dm_bblr_offset = 0;
			$dm_bblr_pageno = 1;
		}


		if ($log = $wpdb->get_results('SELECT * FROM '.$table_prefix.'bad_behavior '.$whereclause.' ORDER BY id DESC LIMIT '.$dm_bblr_offset.','.$dm_bblr_show_num.';')): $alternate = '';
//		echo 'SELECT * FROM '.$table_prefix.'bad_behavior ORDER BY id DESC LIMIT '.$dm_bblr_offset.','.$dm_bblr_show_num.';';

		echo "<div class=\"wrap\"><h2>Displaying BB Log Rows</h2>";
		foreach ($log as $entry) : 
			$alternate = ($alternate == 'Bisque') ? 'Aquamarine' : 'Bisque'; 
			echo "<div style=\"background : $alternate;\">";
			echo "<span style=\"font-weight : bold;\">Client IP:</span>&nbsp;{$entry->ip}&nbsp;";
			echo "<span style=\"font-weight : bold;\">Date:</span>&nbsp;".strftime("%Y-%m-%d %H:%M:%S", strtotime($entry->date." GMT")). "&nbsp;"; 
			echo "<br /><span style=\"font-weight : bold;\">Request URI:</span>&nbsp;";
			echo $entry->request_uri;
			echo "<br /><span style=\"font-weight : bold;\">Headers:</span>&nbsp;";
			echo $entry->http_headers;
			if (!empty($entry->user_agent)) {
				echo "<br /><span style=\"font-weight : bold;\">User-Agent:</span>&nbsp;";
				echo $entry->user_agent;
			}
			echo "<br /><span style=\"font-weight : bold;\">Request Result (key):</span>&nbsp;";
			echo bblr_keyInfo($entry->key);
			echo '</div>';
			echo '<br />';

		endforeach;
		else:
			echo '<p>There are no entries in the log matching the range specified.</p>';
		endif;
		echo '<br /><hr />';
		echo '<p>Currently showing a maximum of '.$dm_bblr_show_num.' records per page - ';
		echo 'records '.($dm_bblr_offset + 1).' to ';  
		// We add values here because humans do not like to think of record number 0
		echo ($dm_bblr_offset+$dm_bblr_show_num > $dm_bblr_count_rows ) ?  $dm_bblr_count_rows : $dm_bblr_offset+$dm_bblr_show_num; 
		//echo $dm_bblr_offset+$dm_bblr_show_num;
		echo " of a total of ".$dm_bblr_count_rows." records</p><hr />";
		// paginate buttons if needed
		if ($dm_bblr_offset+$dm_bblr_show_num < $dm_bblr_count_rows || $dm_bblr_pageno > 1) {
			// Show buttons
			echo "<div style=\"text-align : center;\">";
			if ($dm_bblr_pageno > 1) {
?>
				<form action="" method="POST" id="bblr-move-prev-results">
					<input type="hidden" name="pagenotoview" value="<?php echo $dm_bblr_pageno - 1;?>" />
					<input type="submit" name="Submit" value="&laquo;Previous Page" />
				</form>
<?php			}
			if ($dm_bblr_offset+$dm_bblr_show_num < $dm_bblr_count_rows) {
				// show next button
?>
				<form action="" method="POST" id="bblr-move-next-results">
					<input type="hidden" name="pagenotoview" value="<?php echo $dm_bblr_pageno + 1;?>" />
					<input type="submit" name="Submit" value="Next Page &raquo;" />
				</form>
<?php
			} 
			echo "</div>";
		}
		echo "</div>";
	} else {
		// no rows
		echo "<div class=\"wrap\"><h2>Displaying BB Log Rows</h2>";
		echo "<p>There are currently no rows in the BB Log table.</p>";
		echo "</div>";
	}

 
} //end function bblr_display


// The rest is new for version 0.4 and above

function dm_bblr_install(){
	add_option('dm_bblr_numrows','20','Number of rows to return on each page in BB log viewer','no');
	add_option('dm_bblr_showblocksonly','0','If this is 1 then only show blocked rows not suspicious rows','no');
	add_option('dm_bblr_showdashlink','0','If this is 1 a dashboard link to BBLR will appear','no');
}

function dm_bblr_uninstall(){
	delete_option('dm_bblr_numrows');
	delete_option('dm_bblr_showblocksonly');
	delete_option('dm_bblr_showdashlink');
}

function bblr_check_showblocks(){
// get the last setting for show blocked rows only, or default to 0 (show everything)
	$dm_bblr_showblocksonly = get_option('dm_bblr_showblocksonly');
	if (empty($dm_bblr_showblocksonly)) {
		$dm_bblr_showblocksonly = 0;
		update_option('dm_bblr_showblocksonly','0');
	}
	return $dm_bblr_showblocksonly;
}
function bblr_set_showblocks($dm_bblr_sb_howmany){
	if (is_numeric($dm_bblr_sb_howmany) && $dm_bblr_sb_howmany == 1 ) {
		update_option('dm_bblr_showblocksonly',1);
	} else {
		update_option('dm_bblr_showblocksonly','0');
	}
	return $dm_bblr_sb_howmany;
}

function bblr_check_showdashlink(){
// get the last setting for show dashboard link
	$dm_bblr_showdashlink = get_option('dm_bblr_showdashlink');
	if (empty($dm_bblr_showdashlink)) {
		$dm_bblr_shodashlink = 0;
		update_option('dm_bblr_showdashlink','0');
	}
	return $dm_bblr_showdashlink;
}
function bblr_set_showdashlink($dm_bblr_sd_howmany){
	if (is_numeric($dm_bblr_sd_howmany) && $dm_bblr_sd_howmany == 1 ) {
		update_option('dm_bblr_showdashlink',1);
	} else {
		update_option('dm_bblr_showdashlink','0');
	}
	return $dm_bblr_sd_howmany;
}

function bblr_get_whereclause(){
// Depending on show blocks value, generate a where clause
	$wherecl = get_option('dm_bblr_showblocksonly');
	if (empty($wherecl)) {$wherecl = " ";} elseif ($wherecl == '1') {$wherecl = ' WHERE `key` != "00000000" ';} else {$wherecl = " ";}
	return $wherecl;
}

function bblr_check_rows(){
// get the last set number of rows to return per page, or default to 20
	$dm_bblr_numrows = get_option('dm_bblr_numrows');
	if (empty($dm_bblr_numrows)) {
		$dm_bblr_numrows = 20;
		update_option('dm_bblr_numrows','20');
	}
	return $dm_bblr_numrows;
}
function bblr_set_rows($dm_bblr_howmany){
	if (is_numeric($dm_bblr_howmany)) {
		if ($dm_bblr_howmany > 0) {
			update_option('dm_bblr_numrows',$dm_bblr_howmany);
		} else {
			update_option('dm_bblr_numrows','20');
			$dm_bblr_howmany = 20;
		}
	} else {
		update_option('dm_bblr_numrows','20');
		$dm_bblr_howmany = 20;
	}
	return $dm_bblr_howmany;
}

	/* add  menus */

	function bblr_add_config_page()
	{
		//		add_submenu_page('options-general.php',__('BB Log Reader Options'),__('BBLR options'),8,'BBLR_confmanager',bblr_config_page);
		if (function_exists('add_options_page'))
			add_options_page('BBLR Options','BBLR Options',8, __FILE__ ,'bblr_config_page');
		
	}

	function bblr_add_menu(){
		add_management_page('BB Log Reader', 'BB Log Reader', 8, __FILE__, 'bblr_display');
	}


	/* ====== config_page ====== */
	
	/*
	 * Loads in the configuration page.
	 */
	
	function bblr_config_page()
	{

		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$dm_bblr_numrows = bblr_set_rows(attribute_escape($_POST['dm_bblr_numrows']));
			if (!empty($_POST['dm_bblr_showblocksonly'])) {
				$dm_bblr_showblocksonly = bblr_set_showblocks(attribute_escape($_POST['dm_bblr_showblocksonly']));
			} else {
				$dm_bblr_showblocksonly = bblr_set_showblocks(0);
			}
			
			if (!empty($_POST['dm_bblr_showdashlink'])) {
				$dm_bblr_showdashlink = bblr_set_showdashlink(attribute_escape($_POST['dm_bblr_showdashlink']));
			} else {
				$dm_bblr_showdashlink = bblr_set_showdashlink(0);
			}
			echo '<div id="bblr-config-saved" class="updated fade-ffff00"">';
			echo '<p><strong>';
			_e('Options saved.');
			echo '</strong></p></div>';
		} else {
			$dm_bblr_numrows = bblr_check_rows();
			$dm_bblr_showblocksonly = bblr_check_showblocks();
			$dm_bblr_showdashlink = bblr_check_showdashlink();
		}
 
		?>
		<div class="wrap">
			<h2>Bad Behavio(u)r log reader</h2>
			<h3>Log Display settings</h3>

			<form action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST" id="bblr-display-rows-conf">
			<?php wp_nonce_field('update-options'); ?>

			<fieldset class="options">
			<legend style="padding : 0px;">
				<h3>Show how many rows per page:</h3>
			</legend>
			<label for="numperpage" style="margin-left : 9px;">Show this many log rows:</label>
			<input id="numperpage" name="dm_bblr_numrows" size="4" value="<?php echo get_option('dm_bblr_numrows');?>" />
			<legend style="padding : 0px;">
				<h3>Show blocked entries only (ignore warnings)</h3>
			</legend>
			<label for="showblock" style="margin-left : 9px;">Tick to only view log entries showing blocks:</label>
			<input id="showblock" type="checkbox" name="dm_bblr_showblocksonly" <?php checked('1', get_option('dm_bblr_showblocksonly')); ?> value="1" />

			<legend style="padding : 0px;">
				<h3>Show log reader link on Dashboard</h3>
			</legend>
			<label for="showlink" style="margin-left : 9px;">Tick to show BBLR link on the Dashboard:</label>
			<input id="showlink" type="checkbox" name="dm_bblr_showdashlink" <?php checked('1', get_option('dm_bblr_showdashlink')); ?> value="1" />

			</fieldset>
			<input type="hidden" name="page_options" value="dm_bblr_numrows, dm_bblr_showblocksonly, dm_bblr_showdashlink" />
			<p class="submit"><input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" /></p>

		<p style="text-align:center;">
			BB Log Reader Version <?php echo BBLR_VERSION; ?> - 
			Copyright 2007, 2008 <a href="http://www.misthaven.org.uk/blog/">David McFarlane</a>
			-
			<a href="http://www.misthaven.org.uk/blog/projects/bblogreader">Help and FAQ</a>
		</p>
	</form>
</div>
<?php

	}

function dm_bblr_activitybox() {
	global $bblr_plugin_loc;
	if (get_option('dm_bblr_showdashlink') == 1) {
		if (current_user_can('manage_options')) {
			echo '<br /><h3>'.__('Bad Behaviour Log Reader').'</h3>';
			if (function_exists('register_uninstall_hook')) {
				echo '<p><a href="'.clean_url("tools.php?page=".$bblr_plugin_loc).'" title="View Bad Behaviour Logs">View the bad behaviour logs</a>.</p>';
			} else {
				echo '<p><a href="'.clean_url("edit.php?page=".$bblr_plugin_loc).'" title="View Bad Behaviour Logs">View the bad behaviour logs</a>.</p>';
			}
		}
	}
}


// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
    define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );

// Guess the location

$bblr_plugin_url = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
if (stripos($bblr_plugin_url,'bad-behavior-log-reader') === false) {
  $bblr_plugin_loc = 'bblr.php';
} else {
  $bblr_plugin_loc = 'bad-behavior-log-reader/bblr.php';
}



add_action('activity_box_end', 'dm_bblr_activitybox');

add_action('admin_menu', 'bblr_add_menu');
add_action('admin_menu', 'bblr_add_config_page');

register_activation_hook(  __FILE__ ,'dm_bblr_install');
register_deactivation_hook( __FILE__ ,'dm_bblr_uninstall');
?>
