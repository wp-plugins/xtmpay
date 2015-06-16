<?php 
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	exit();

$currentFile = __FILE__;
$currentFolder = dirname($currentFile);
require_once $currentFolder . '/inc/tmpay_globals.class.php';

global $wpdb;
$tableTMpay = $wpdb->prefix . TMPAY::TABLE_SETTING;
$tableTruemoney = $wpdb->prefix . TMPAY::TABLE_TRUEMONEY;

$wpdb->query( "DROP TABLE $tableTMpay" );
$wpdb->query( "DROP TABLE $tableTruemoney" );


?>