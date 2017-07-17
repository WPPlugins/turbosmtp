<div class="wrap">

	<?php

	if ( ts_validapi() ):
		?>
        <div class="pull-right">
            <p><?php _e( "Welcome", "turbosmtp" ); ?> <?php echo get_option( "ts_auth_options" )["op_ts_email"]; ?></p>
        </div>

        <div class="container-white">

            <h2><?php _e( "Logout", "turbosmtp" ); ?></h2>

            <div class="notice notice-warning">
                <p><?php _e( "You are going to logout: if you proceed you’ll have to re-configure your account to use all of the features, do you want to proceed?", "turbosmtp" ); ?></p>
            </div>

            <form method="post">
                <input type="hidden" name="ts_reset" value="1">
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="std" value="<?php _e( "Proceed", "turbosmtp" ); ?>">
                </p>
                <input type="hidden" name="ts_nonce_update" value="<?php echo $ts_nonce; ?>"/>

            </form>

        </div>

		<?php
	endif;
	?>
</div>
