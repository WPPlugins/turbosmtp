<div class="wrap">

    <div class="actions bulkactions pull-right">

        <form method="post" action="" class="change_date">

            <input name="from_date" value="" type="text">

            <?php

            foreach ($_GET as $key => $value) {
                if ('from' != $key && 'to' != $key)
                    echo("<input type='hidden' name='$key' value='$value' />");
            }

            ?>

        </form>

    </div>

    <div class="ts-loading other-infos-loading">

        <div class="ts-spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
        <h3><?php _e("Loading analytics data...", "turbosmtp"); ?></h3>

    </div>

    <div class="other-infos">

        <div class="other-infos-header">
            <div class="other-infos-toggle"><a href="#"><span class="icon-arrow-down" data-close="icon-arrow-left"
                                                              data-open="icon-arrow-left"></span></a></div>
            <h3><span class="icon-stat"></span> Line Chart</h3>
        </div>

        <canvas id="turbo-stat-chart" width="400" height="400"></canvas>

    </div>

    <div class="notice notice-error other-infos-noresults">
        <p>
		    <?php _e("No results found for your temporal choice.", "turbosmtp" ); ?>
        </p>
    </div>

    <div class="other-infos-columns">
        <div class="total-email active" data-ts-filter="all">

            <div class="panel">

                <div class="heading">
                    <h3><?php _e("total sent emails", "turbosmtp"); ?></h3>
                </div>

                <div class="body">
                    <h4></h4>
                </div>

                <div class="foot">
                    <p></p>
                </div>

            </div>

        </div>
        <div class="delivered" data-ts-filter="delivered">
            <div class="panel">

                <div class="heading">
                    <h3><?php _e("delivered emails", "turbosmtp"); ?></h3>
                </div>

                <div class="body">
                    <h4></h4>
                </div>

                <div class="foot">
                    <p></p>
                </div>

            </div>
        </div>
        <div class="opens" data-ts-filter="read">
            <div class="panel">

                <div class="heading">
                    <h3><?php _e("opened emails", "turbosmtp"); ?></h3>
                </div>

                <div class="body">
                    <h4></h4>
                </div>

                <div class="foot">
                    <p></p>
                </div>

            </div>
        </div>
        <div class="clicks" data-ts-filter="click">
            <div class="panel">

                <div class="heading">
                    <h3><?php _e("clicked emails", "turbosmtp"); ?></h3>
                </div>

                <div class="body">
                    <h4></h4>
                </div>

                <div class="foot">
                    <p></p>
                </div>

            </div>
        </div>
        <div class="other-stats">
            <div class="queued" data-ts-filter="queue">
                <div class="heading">
                    <h3><?php _e("queued", "turbosmtp"); ?></h3>
                </div>
                <div class="body">
                    <p></p>
                </div>
            </div>
            <div class="unsubscribes" data-ts-filter="deleted">
                <div class="heading">
                    <h3><?php _e("unsubscribes", "turbosmtp"); ?></h3>
                </div>
                <div class="body">
                    <p></p>
                </div>
            </div>
            <div class="spam" data-ts-filter="spam">
                <div class="heading">
                    <h3><?php _e("spam reports", "turbosmtp"); ?></h3>
                </div>
                <div class="body">
                    <p></p>
                </div>
            </div>
            <div class="bounce" data-ts-filter="bounce">
                <div class="heading">
                    <h3><?php _e("bounces", "turbosmtp"); ?></h3>
                </div>
                <div class="body">
                    <p></p>
                </div>
            </div>
        </div>
    </div>

    <div class="ts-loading history-email-loading">

        <div class="ts-spinner">
            <div class="rect1"></div>
            <div class="rect2"></div>
            <div class="rect3"></div>
            <div class="rect4"></div>
            <div class="rect5"></div>
        </div>
        <h3><?php _e("Loading emails history...", "turbosmtp"); ?></h3>

    </div>

    <div class="history-email">

        <form id="email-sent-list" class="history-step" method="get">

            <div class="ts-history-table-loading">
                <div class="ts-spinner">
                    <div class="rect1"></div>
                    <div class="rect2"></div>
                    <div class="rect3"></div>
                    <div class="rect4"></div>
                    <div class="rect5"></div>
                </div>
            </div>

            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

            <div id="ts-history-table" style="">
                <?php
                wp_nonce_field('ajax-custom-list-nonce', '_ajax_custom_list_nonce');

                $end = date('Y-m-d');
                $begin = strtotime ( '-7 days' , strtotime ( $end ) ) ;
                $begin = date ( 'Y-m-d' , $begin );

                $wp_list_table = new TS_Ajax_List_Table($begin, $end, "all");
                $wp_list_table->prepare_items();
                $wp_list_table->display();

                ?>
            </div>

        </form>
    </div>

</div>

