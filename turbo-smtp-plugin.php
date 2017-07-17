<?php
/**
* Plugin Name: turboSMTP
* Plugin URI: http://www.serversmtp.com/en/smtp-wordpress-configure
* Description: Easily send emails from your WordPress blog using turboSMTP's services
* Author: dueclic
* Author URI: https://www.dueclic.com
* Version: 1.8
* Text Domain: turbosmtp
* Domain Path: /languages/
* License: GPL v3
 */

__('Easily send emails from your WordPress blog using turboSMTP\'s services', 'turbosmtp');

if(function_exists('xdebug_disable')) { xdebug_disable(); }

require_once( "class/TurboApiClient.php" );
require_once( "class/TurboApiStats.php" );
require_once("class/TSAjaxListTable.php");
require_once( "ts_post.php" );

add_action( 'wp_ajax_nopriv_get_stats_chart', 'get_stats_chart' );
add_action( 'wp_ajax_get_stats_chart', 'get_stats_chart' );

add_action( 'wp_ajax_nopriv_get_opensclicks', 'get_opensclicks' );
add_action( 'wp_ajax_get_opensclicks', 'get_opensclicks' );

add_action('phpmailer_init','TSPHPMailer');



function ts_load_plugin_textdomain() {

	$domain = 'turbosmtp';

	/*$currentLocale = get_locale();
	if(!empty($currentLocale)){
		$moFile = dirname(__FILE__) . "/languages/".$domain."-" . $currentLocale . ".mo";
		if(@file_exists($moFile) && is_readable($moFile)) { load_textdomain($domain,$moFile); }
	}*/

	load_plugin_textdomain( $domain , false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


}

add_action( 'plugins_loaded', 'ts_load_plugin_textdomain' );


add_action( 'init', 'post_admin_tsconfig' );

register_activation_hook( __FILE__ , 'TSactivate' );
register_deactivation_hook(__FILE__ , 'TSdisactivate' );

$ts_options      = get_option( "ts_auth_options" );
$ts_send_options = get_option( "ts_send_options" );

function ts_validapi() {
	global $ts_options;
	return isset($ts_options['op_ts_validapi']) && $ts_options['op_ts_validapi'];
}



function TSPHPMailer($phpmailer) {
	global $ts_send_options;
	global $ts_options;
	if( !is_email($ts_send_options["from"]) || empty($ts_send_options["host"]) ){
		return;
	}
	$phpmailer->Mailer = "smtp";
	$phpmailer->From = $ts_send_options["from"];
	$phpmailer->FromName = $ts_send_options["fromname"];
	$phpmailer->Sender = $phpmailer->From; //Return-Path
	$phpmailer->AddReplyTo($phpmailer->From,$phpmailer->FromName); //Reply-To
	$phpmailer->Host = $ts_send_options["host"];
	$phpmailer->SMTPSecure = $ts_send_options["smtpsecure"];
	$phpmailer->Port = $ts_send_options["port"];
	$phpmailer->SMTPAuth = ($ts_send_options["smtpauth"]=="yes") ? TRUE : FALSE;
	if($phpmailer->SMTPAuth){
		$phpmailer->Username = $ts_options["op_ts_email"];
		$phpmailer->Password = $ts_options["op_ts_password"];
	}
}

function TSactivate(){
	$ts_send_options = array();
	$ts_send_options["from"] = "";
	$ts_send_options["fromname"] = "";
	$ts_send_options["host"] = "pro.turbo-smtp.com";
	$ts_send_options["smtpsecure"] = "ssl";
	$ts_send_options["port"] = "465";
	$ts_send_options["smtpauth"] = "yes";
	$ts_send_options["deactivate"] = "";

	/*
	 *
	 * For recover old plugin data
	 *
	 */

	if (get_option("turboSMTP_options") !== FALSE) {

        $ts_old_send_options = get_option("turboSMTP_options");
        $ts_old_auth_options = array(
            'op_ts_email' => $ts_old_send_options['username'],
            'op_ts_password' => $ts_old_send_options['password']
        );

        unset($ts_old_send_options['username']);
        unset($ts_old_send_options['password']);
        unset($ts_old_send_options['deactivate']);

        /**
         *
         * Test turboSMTP connection
         *
         */

        $api = new TurboApiStats($ts_old_auth_options['op_ts_email'], $ts_old_auth_options['op_ts_password']);

        if ($api->isValid()) {
            $ts_auth_options['op_ts_email'] = $ts_old_auth_options['op_ts_email'];
            $ts_auth_options['op_ts_password'] = $ts_old_auth_options['op_ts_password'];
            $ts_auth_options['op_ts_validapi'] = true;
            update_option("ts_auth_options", $ts_auth_options);
        }

        add_option("ts_send_options", $ts_old_send_options);

    }

    else
        add_option("ts_send_options",$ts_send_options);

    delete_option("turboSMTP_options");
}

function TSdisactivate() {
	delete_option("ts_auth_options");
	delete_option("ts_send_options");
}


function _ajax_fetch_ts_history_callback() {

    $wp_list_table = new TS_Ajax_List_Table( $_REQUEST['begin'], $_REQUEST['end'], $_REQUEST['filter'] );
    $wp_list_table->ajax_response();
}

add_action( 'wp_ajax__ajax_fetch_ts_history', '_ajax_fetch_ts_history_callback' );

function ts_footer_admin_text()
{
	return __('This plugin is powered by', 'turbosmtp').' <a href="https://www.dueclic.com/" target="_blank">dueclic</a>. <a class="social-foot" href="https://www.facebook.com/dueclic/"><span class="dashicons dashicons-facebook bg-fb"></span></a>';
}

function ts_footer_version()
{
	return "";
}

function TSChange_Copyright()
{
	add_filter('admin_footer_text', 'ts_footer_admin_text', 11);
	add_filter('update_footer', 'ts_footer_version', 11);
}

add_action("ts_change_footer_copyright", "TSChange_Copyright");

if (is_admin())
	require("turbo-admin-sections.php");
