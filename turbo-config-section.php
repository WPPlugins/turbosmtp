<div class="wrap">

	<?php

	if ( ! ts_validapi() ):

		?>

        <div class="centered">
            <img src="<?php echo plugins_url( '/img/logo.png', __FILE__ ); ?>">
        </div>

        <div class="turbo-about wp-clearfix">

            <div class="register">

                <div class="box">

                    <h3><?php _e( "Register", "turbosmtp" ); ?></h3>
                    <p><?php _e( "Send 6000 emails per months", "turbosmtp" ); ?><br/>
                        <span class="big"><?php _e( "free", "turbosmtp" ); ?></span><br/>
						<?php _e( "up to 200 per day", "turbosmtp" ); ?><br/>
						<?php _e( "No purchase required.", "turbosmtp" ); ?><br/>
						<?php _e( "No debit card", "turbosmtp" ); ?>
                    </p>

                    <a target="_blank"
                       href="https://serversmtp.com/it/signup"><?php _e( "read more", "turbosmtp" ); ?></a>

                </div>

            </div>

            <div class="login">

                <div class="box">

                    <h3>Login</h3>

                    <form method="post" enctype="multipart/form-data" action="" name="turboSMTP_conf">

                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><label for="op_ts_email"><?php _e( "Email", "turbosmtp" ); ?></label>
                                </th>
                            </tr>
                            <tr valign="top">
                                <td><input type="text" id="op_ts_email" class="form-input" name="op_ts_email"
                                           value="<?php echo $ts_options["op_ts_email"]; ?>"/></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label
                                            for="op_ts_password"><?php _e( "Password", "turbosmtp" ); ?></label></th>
                            </tr>
                            <tr valign="top">
                                <td><input type="password" id="op_ts_password" class="form-input" name="op_ts_password"
                                           value="<?php echo $ts_options["op_ts_password"]; ?>"/></td>
                            </tr>
                        </table>

                        <input type="hidden" name="ts_mail_update" value="update"/>
                        <input type="hidden" name="action" value="tsconfig"/>
                        <input type="hidden" name="ts_nonce_update" value="<?php echo $ts_nonce; ?>"/>

                        <p class="submit">
                            <input type="submit" name="submit" id="submit" class="form-submit"
                                   value="<?php _e( "Login", "turbosmtp" ); ?>">
                        </p>

                    </form>

                </div>

            </div>

        </div>

		<?php

	else:

		?>

        <div class="pull-right">
            <p><?php _e( "Welcome", "turbosmtp" ); ?> <?php echo get_option( "ts_auth_options" )["op_ts_email"]; ?></p>
        </div>

        <h2><?php _e( "Settings", "turbosmtp" ); ?> turboSMTP</h2>

        <div class="container-white">

            <h3><?php _e( "Sender email configuration", "turbosmtp" ); ?></h3>

            <form method="post" enctype="multipart/form-data" name="turboSMTP_email">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="ts_auth_email_from">
                                <?php _e( "Sender name", "turbosmtp"); ?>
                            </label>
                        </th>
                        <td><input type="text" id="ts_auth_email_from" name="ts_auth_email_from"
                                   value="<?php echo $ts_send_options["fromname"]; ?>" size="43"
                                   style="width:272px;height:24px;"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="ts_auth_email">
		                        <?php _e( "Sender email address", "turbosmtp"); ?>
                            </label>
                        </th>
                        <td><input type="text" id="ts_auth_email" name="ts_auth_email"
                                   value="<?php echo $ts_send_options["from"]; ?>" size="43"
                                   style="width:272px;height:24px;"/></td>
                    </tr>
                </table>
                <h3>
	                <?php _e( "turboSMTP parameters", "turbosmtp" ); ?>
                </h3>
                <table class="form-table">

                    <tr valign="top">
                        <th scope="row"><label for="ts_smtp_mailport">
		                        <?php _e( "SMTP Port", "turbosmtp" ); ?>
                            </label>
                        </th>
                        <td><input type="text" id="ts_smtp_mailport" name="ts_smtp_mailport"
                                   value="<?php echo $ts_send_options["port"]; ?>" size="43"
                                   style="width:50px;height:24px;"/>
                            <br>
                            <span class="description">
                                <?php _e( "Use port 25, 587 or 2525 for non encrypted connections, 465 or 25025 for encrypted connections", "turbosmtp" ); ?>
							</span></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e( "Encryption", "turbosmtp" ); ?>
                        </th>
                        <td>
                            <p>
                                <input id="turboSMTP_mail_smtpsecure_none" name="ts_smtp_smtpsecure" type="radio"
                                       value=""<?php if ( $ts_send_options["smtpsecure"] == '' ) { ?> checked="checked"<?php } ?> />
                                <label for="ts_smtp_smtpsecure">
			                        <?php _e( "No encryption", "turbosmtp" ); ?>
                                </label>
                            </p>
                            <p>
                                <input id="turboSMTP_mail_smtpsecure_ssl" name="ts_smtp_smtpsecure" type="radio"
                                       value="ssl"<?php if ( $ts_send_options["smtpsecure"] == 'ssl' ) { ?> checked="checked"<?php } ?> />
                                <label for="turboSMTP_mail_smtpsecure_ssl">
	                                <?php _e( "Use SSL", "turbosmtp" ); ?>
                                </label>
                            </p>
                            </td>
                    </tr>

                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="std"
                           value="<?php _e( "Save changes", "turbosmtp" ); ?>">
                </p>

                <input type="hidden" name="ts_smtp_smtpauth" value="yes"/>
                <input type="hidden" name="ts_smtp_deactivate" value="yes"/>
                <input type="hidden" name="ts_mail_con" value="update"/>
                <input type="hidden" name="ts_nonce_update" value="<?php echo $ts_nonce; ?>"/>

            </form>

        </div>

        <div class="container-white">

            <h3><?php _e("Send test email", "turbosmtp"); ?></h3>

            <form action="" method="post" enctype="multipart/form-data" name="ts_mail_test">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="ts_mail_to">
		                        <?php _e("Recipient", "turbosmtp"); ?>
                            </label>
                        </th>
                        <td><input type="text" id="ts_mail_to" name="ts_mail_to" value="" size="43" style="width:272px;height:24px;" />
                            <br>
                            <span class="description">
                                <?php _e("Write your email address and click on \"Send test\"", "turbosmtp"); ?>
							</span></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="hidden" name="ts_mail_subject" value="<?php _e("Email sent with WordPress and turboSMTP", "turbosmtp"); ?>" />
                    <input type="hidden" name="ts_mail_message" value="<?php _e("If you read this email means that turboSMTP plugin is working properly.", "turbosmtp"); ?>"/>
                    <input type="hidden" name="ts_mail_test" value="test" />
                    <input type="hidden" name="ts_mail_nonce_test" value="<?php echo $ts_nonce; ?>" />
                    <input type="submit" name="submit" id="submit" class="std"
                           value="<?php _e( "Send test", "turbosmtp" ); ?>">
                </p>
            </form>

        </div>

		<?php
	endif;
	?>

</div>