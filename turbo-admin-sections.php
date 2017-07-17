<?php

	add_action("admin_menu", "TurboSMTP_Menu");

	function TurboSMTP_Menu() {

		add_menu_page(__("turboSMTP integration and configuration", "turbosmtp"), "turboSMTP", "manage_options", "ts-dash", "TSConfiguration_Page", plugins_url('icons/ts_icon.png',__FILE__ ), 80);

		add_submenu_page("ts-dash", __("turboSMTP integration and configuration", "turbosmtp"), __("Configuration", "turbosmtp"), "manage_options", "ts-dash", "TSConfiguration_Page");

        if (ts_validapi()) {

	        add_submenu_page("ts-dash", __("Report", "turbosmtp"), __("Report", "turbosmtp"), "manage_options", "ts-stats", "TSStats");
	        add_submenu_page("ts-dash", __("Logout", "turbosmtp"), __("Logout", "turbosmtp"), "manage_options", "ts-logout", "TSLogout");

        }

	}

	function TSConfiguration_Page() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You don\'t have the right permission to view this page!', 'turbosmtp' ) );
		}

		$ts_nonce = wp_create_nonce('ts_nonce');
		global $ts_options;
		global $ts_send_options;

		require("turbo-config-section.php");

		wp_enqueue_style('ts-style-css', plugins_url('css/turbo-admin.css', __FILE__));
		do_action('ts_change_footer_copyright');

	}

	function TSStats() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You don\'t have the right permission to view this page!', 'turbosmtp'  ) );
		}

		require("turbo-stats-section.php");

		wp_enqueue_style('ts-style-css', plugins_url('css/turbo-admin.css', __FILE__));
		wp_enqueue_style('ts-jqueryui-css', plugins_url('css/jquery-ui.css', __FILE__));
        wp_enqueue_style('ts-drange-css', plugins_url('css/daterangepicker.css', __FILE__));
        wp_enqueue_script('ts-jqueryui', plugins_url('js/jquery-ui.js', __FILE__),array('jquery'), false, true);
		wp_enqueue_script('ts-chart', plugins_url('js/Chart.bundle.min.js', __FILE__),array('jquery'), false, true);
        wp_enqueue_script('ts-moment', plugins_url('js/moment.js', __FILE__),array('jquery'), false, true);
        wp_enqueue_script('ts-drange-js', plugins_url('js/daterangepicker.js', __FILE__),array('jquery'), false, true);
        wp_register_script('ts-stat-js', plugins_url('js/ts-stats.js', __FILE__),array('jquery'), false, true);
		wp_localize_script('ts-stat-js', 'ts', array(
		    'chart_ajax_url' => admin_url('admin-ajax.php?action=get_stats_chart'),
            'i18n' => array(
                "queued" => __("Queue", "turbosmtp"),
                "delivered" => __("Delivered", "turbosmtp"),
                "bounce" => __("Bounced", "turbosmtp"),
                "opens" => __("Opened", "turbosmtp"),
                "clicks" => __("Click", "turbosmtp"),
                "unsubscribes" => __("Unsubscribes", "turbosmtp"),
                "spam" => __("Spam", "turbosmtp"),
                "all" => __("Total", "turbosmtp"),
                "no_results" => __("No results to show", "turbosmtp"),
	            "subject" => __("Subject", "turbosmtp"),
	            "description_error" => __("Error description", "turbosmtp"),
	            "drp_preset" => array(
	            	'today' => __("Today", "turbosmtp"),
		            'yesterday' => __("Yesterday", "turbosmtp"),
		            'lastweek' => __("Last week", "turbosmtp"),
		            'lastmonth' => __("Last month", "turbosmtp"),
		            'prevmonth' => __("Previous month", "turbosmtp"),
		            'thisyear' => __("Current year", "turbosmtp"),
		            'prevyear' => __("Last year", "turbosmtp"),
		            'apply' => __("Confirm", "turbosmtp"),
		            'clear' => __("Clear", "turbosmtp"),
		            'cancel' => __("Cancel", "turbosmtp")
	            )
            )
        ));
		wp_enqueue_script('ts-stat-js');
		do_action('ts_change_footer_copyright');

	}

	function TSLogout() {

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You don\'t have the right permission to view this page!', 'turbosmtp' ) );
		}

		$ts_nonce = wp_create_nonce('ts_nonce');

		require("turbo-logout-section.php");

		wp_enqueue_style('ts-style-css', plugins_url('css/turbo-admin.css', __FILE__));
		do_action('ts_change_footer_copyright');

	}

	function get_stats_chart() {

		global $ts_options;


		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			try {

				$api = new TurboApiStats($ts_options["op_ts_email"], $ts_options["op_ts_password"]);

				print json_encode($api->getStats($_POST['start_date'], $_POST['end_date']));

			}

			catch (Exception $e) {
				print $e->getMessage();
			}

		}
		die();

	}
?>