<?php

function post_admin_tsconfig() {

	$ts_nonce = wp_create_nonce('ts_nonce');
	global $ts_options;
	global $ts_send_options;

	if(isset($_POST['ts_mail_update']) && isset($_POST['ts_nonce_update'])){

		if(!wp_verify_nonce(trim($_POST['ts_nonce_update']),'ts_nonce'))
			add_action("admin_notices", function(){
				?>
				<div class="notice notice-error is-dismissible">
					<p><strong><?php _e( 'Error', 'turbosmtp' ); ?></strong>: <?php _e( 'This request is not allowed, Please try again!', 'turbosmtp' ); ?></p>
				</div>
				<?php
			});

		else {

			$op_ts_email    = trim( $_POST['op_ts_email'] );
			$op_ts_password = trim( $_POST['op_ts_password'] );

			$api = new TurboApiStats( $op_ts_email, $op_ts_password );

			$ts_options = array();

			$ts_options['op_ts_email']    = "";
			$ts_options['op_ts_password'] = "";
			$ts_options['op_ts_validapi'] = false;

			if ( ! $api->isValid() ) {
				add_action( "admin_notices", function () use ($api) {
					?>
					<div class="notice notice-error is-dismissible">
						<p>
							<?php echo $api->lastError; ?>
						</p>
					</div>
					<?php
				} );
			} else {

				$ts_options['op_ts_email']    = $op_ts_email;
				$ts_options['op_ts_password'] = $op_ts_password;
				$ts_options['op_ts_validapi'] = true;
				$ts_send_options['from'] = $op_ts_email;

			}

			update_option( "ts_auth_options", $ts_options );
			update_option( "ts_send_options", $ts_send_options );
		}


    }

	if(isset($_POST['ts_mail_con']) && isset($_POST['ts_nonce_update'])){

		if(!wp_verify_nonce(trim($_POST['ts_nonce_update']),'ts_nonce')) {
			add_action( "admin_notices", function () {
				?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <strong><?php _e( 'Error', 'turbosmtp' ); ?></strong>: <?php _e( 'This request is not allowed, Please try again!', 'turbosmtp' ); ?>
                    </p>
                </div>
				<?php
			} );
		}
		else {

			$api = new TurboApiStats( $ts_options['op_ts_email'], $ts_options['op_ts_password'] );

			if ( ! $api->isValid() ) {
				add_action( "admin_notices", function () use ($api) {
					?>
                    <div class="notice notice-error is-dismissible">
                        <p>
							<?php echo $api->lastError; ?>
                        </p>
                    </div>
					<?php
				} );
			} else {

				if ( ! is_email( $_POST["ts_auth_email"] ) ) {
					add_action( "admin_notices", function () {
						?>
                        <div class="notice notice-error is-dismissible">
                            <p>
								<?php _e("Sender email address is invalid", "turbosmtp"); ?>
                            </p>
                        </div>
						<?php
					} );
				} else if ( empty( $_POST["ts_auth_email_from"] ) ) {
					add_action( "admin_notices", function () {
						?>
                        <div class="notice notice-error is-dismissible">
                            <p>
								<?php _e("Sender name must be not empty", "turbosmtp"); ?>
                            </p>
                        </div>
						<?php
					} );
				} else {

				    $port = trim( $_POST['ts_smtp_mailport'] );

				    if ($port == "")
                        $port = 25;

					$ts_send_options               = array();
					$ts_send_options["from"]       = trim( $_POST['ts_auth_email'] );
					$ts_send_options["fromname"]   = trim( $_POST['ts_auth_email_from'] );
					$ts_send_options["host"]       = "pro.turbo-smtp.com";
					$ts_send_options["smtpsecure"] = trim( $_POST['ts_smtp_smtpsecure'] );
					$ts_send_options["port"]       = $port;
					$ts_send_options["smtpauth"]   = trim( $_POST['ts_smtp_smtpauth'] );
					$ts_send_options["deactivate"] = ( isset( $_POST['ts_smtp_deactivate'] ) ) ? trim( $_POST['ts_smtp_deactivate'] ) : "";
					update_option( "ts_send_options", $ts_send_options );

					add_action( "admin_notices", function () use ($api) {
						?>
                        <div class="notice notice-success is-dismissible">
                            <p>
								<?php _e("Options successfully saved.", "turbosmtp"); ?>
                            </p>
                        </div>
						<?php
					} );

				}

			}
		}

	}

	if(isset($_POST['ts_reset']) && isset($_POST['ts_nonce_update'])){

		if(!wp_verify_nonce(trim($_POST['ts_nonce_update']),'ts_nonce')) {
			add_action( "admin_notices", function () {
				?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <strong><?php _e( 'Error', 'turbosmtp' ); ?></strong>: <?php _e( 'This request is not allowed, Please try again!', 'turbosmtp' ); ?>
                    </p>
                </div>
				<?php
			} );
		}
		else {
			TSdisactivate();
			TSactivate();

			if (wp_redirect(admin_url('admin.php?page=ts-dash'))) {
                exit;
			}

		}

	}

	if(isset($_POST['ts_mail_test']) && isset($_POST['ts_mail_nonce_test'])){

		if(!wp_verify_nonce(trim($_POST['ts_mail_nonce_test']),'ts_nonce')) {
			add_action( "admin_notices", function () {
				?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <strong><?php _e( 'Error', 'turbosmtp' ); ?></strong>: <?php _e( 'This request is not allowed, Please try again!', 'turbosmtp' ); ?>
                    </p>
                </div>
				<?php
			} );
		}

		else {

			$to      = $_POST['ts_mail_to'];
			$subject = $_POST['ts_mail_subject'];
			$message = $_POST['ts_mail_message'];

			if ( ! empty( $to ) && ! empty( $subject ) && ! empty( $message ) ) {
				try {
					$result = wp_mail( $to, $subject, $message );
				} catch ( phpmailerException $e ) {
					$failed = 1;
				}
			} else {
				$failed = 2;
			}
			if ( ! $failed ) {
				if ( $result == true ) {
					add_action( "admin_notices", function () {
						?>
                        <div class="notice notice-success is-dismissible">
                            <p>
                                <?php _e( 'Test email was sent successfully', 'turbosmtp' ); ?>
                            </p>
                        </div>
						<?php
					} );
				} else {
					$failed = 1;
				}
			}
			if ( $failed == 1 ) {
				add_action( "admin_notices", function () {
					?>
                    <div class="notice notice-error is-dismissible">
                        <p>
                            <strong><?php _e("Error", "turbosmtp"); ?></strong>: <?php _e( 'There were one or more errors, check your turboSMTP configuration or if you have installed other SMTP plugin.', 'turbosmtp' ); ?>
                        </p>
                    </div>
					<?php
				} );
			} elseif ( $failed == 2 ) {
				add_action( "admin_notices", function () {
					?>
                    <div class="notice notice-error is-dismissible">
                        <p>
							<strong><?php _e("Error", "turbosmtp"); ?></strong>: <?php _e( 'Recipient must be not empty', 'turbosmtp' ); ?>
                        </p>
                    </div>
					<?php
				} );
			}

		}

	}

}

?>