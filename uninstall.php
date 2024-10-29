<?
if( !defined( ‘ABSPATH’) && !defined(’WP_UNINSTALL_PLUGIN’) )
exit();

delete_option('dm_bblr_numrows');
delete_option('dm_bblr_showblocksonly');
delete_option('dm_bblr_showdashlink');
?>
