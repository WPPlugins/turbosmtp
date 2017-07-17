<?php

	if( ! class_exists( 'WP_List_Table' ) ) {
	    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	
	function get_icon($item) {
		
		$status  = $item['status'];
		$opened  = $item['opened'];
		$spam    = $item['spam'];
		$unsub   = $item['unsub'];
		$clicked = $item['clicked'];
		
		
		if ($status === "REPORT" || $status === "SUCCESS" && $spam !== FALSE)

			$i_class = "icon-red_dot";
		
		else if ($status === "UNSUB" || $status === "SUCCESS" && $unsub !== FALSE)
			
			$i_class = "icon-maroon_dot";
		
		else if ($status === "CLICK" || $status === "SUCCESS" && $clicked !== FALSE)
			
			$i_class = "icon-darkgreen_dot";
		
		else if ($status === "OPEN" || $status === "SUCCESS" && $opened !== FALSE)
			
			$i_class = "icon-green_dot";
		
		else if ($status === "FAIL")
		
			$i_class = "icon-yellow_dot";
		
		else if ($status === "SYSFAIL")
			
			$i_class = "icon-orange_dot";
		
		else if ($status === "DEFER")
			
			$i_class = "icon-white_dot";
		
		else if ($status === "SUCCESS" && $clicked === FALSE && $opened === FALSE && $spam === FALSE && $unsub === FALSE)
			
			$i_class = "icon-gray_dot";
		
		else
			
			$i_class = "icon-white_dot";
		
		return '<i class="icon '.$i_class. '"></i>';
			
			
	}
	
	class TS_Ajax_List_Table extends WP_List_Table {
		
		private $begin;
		private $end;
		private $total_items;
		private $filter;
        private $csv_download;

        function getCSVDownload() {
            return $this->csv_download;
        }
		
		function get_columns() {
			
		  $columns = array(
		    'cb'        => '',
		    'subject'  => __('Subject', 'turbosmtp'),
			'subject_comp' => '',
		    'from'     => __('From', 'turbosmtp'),
		    'to'       => __('To', 'turbosmtp'),
		    'datetime' => __('Date / Time', 'turbosmtp'),
		    'error'    => __('Error description', 'turbosmtp')
		  );
		  return $columns;
			
		}

		function display() {
			wp_nonce_field( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );

			/**
			 *
             * Fix for Wordpress 4.3.8
             *
			 */

			/*$singular = $this->_args['singular'];
			$this->screen->render_screen_reader_content( 'heading_list' );*/

			?>
			<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
				<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
				</thead>

				<tbody id="the-list"<?php
				/*if ( $singular ) {
					echo " data-wp-lists='list:$singular'";
				}*/  ?>>
				<?php $this->display_rows_or_placeholder(); ?>
				</tbody>

			</table>
			<?php
			$this->display_tablenav( 'top' );
		}
		
		function get_ts_data($page_number) {
			
			global $ts_options;
			
			$data = [];
			
			$api = new TurboApiStats($ts_options["op_ts_email"], $ts_options["op_ts_password"]);
			$ts_data = $api->getEmails($this->begin, $this->end, $this->filter, $this->get_pagenum());

			$ts_emails = $ts_data['emails'];

			foreach ($ts_emails as $email) {

				array_push($data, array(
					
					"subject" => (strlen($email['subject']) > 40 ? substr($email['subject'], 0, 40)."..." : $email['subject']),
					"subject_comp" => $email['subject'],
					"from" => $email['sender'],
					"to" => $email['recipient'],
					"datetime" => $email['sendTime'],
					"status" => $email['status'],
					"opened" => isset($email['opened']) ? $email['opened'] : FALSE,
					"spam" => isset($email['spam']) ? $email['spam'] : FALSE,
					"unsub" => isset($email['unsub']) ? $email['unsub'] : FALSE,
					"clicked" => isset($email['clicked']) ? $email['clicked'] : FALSE,
					"error" => $email['error']
					
				));
				
			}
						
			$this->total_items = $ts_data['total'];

			return $data;
			
		}

		function ajax_response() {

			check_ajax_referer( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );

			$this->prepare_items();

			extract( $this->_args );
			extract( $this->_pagination_args, EXTR_SKIP );

			ob_start();
			if ( ! empty( $_REQUEST['no_placeholder'] ) )
				$this->display_rows();
			else
				$this->display_rows_or_placeholder();
			$rows = ob_get_clean();

			ob_start();
			$this->print_column_headers();
			$headers = ob_get_clean();

			ob_start();
			$this->pagination('top');
			$pagination_top = ob_get_clean();

			ob_start();
			$this->pagination('bottom');
			$pagination_bottom = ob_get_clean();

			$response = array( 'rows' => $rows );
			$response['pagination']['top'] = $pagination_top;
			$response['pagination']['bottom'] = $pagination_bottom;
			$response['column_headers'] = $headers;

			if ( isset( $total_items ) )
				$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

			if ( isset( $total_pages ) ) {
				$response['total_pages'] = $total_pages;
				$response['total_pages_i18n'] = number_format_i18n( $total_pages );
			}

			die( wp_json_encode( $response ) );

		}
		
		function prepare_items() {
		  $columns = $this->get_columns();
		  $hidden = array("spam", "unsub", "clicked", "status", "opened", "subject_comp", "error");
		  $sortable = array();		  
		  $this->_column_headers = array($columns, $hidden, $sortable);
		  
		  $current_page = $this->get_pagenum();
	      $this->items = $this->get_ts_data($current_page);

		  $per_page     = 10;
	  
		  $this->set_pagination_args( array(
		    'total_items' => (int)$this->total_items, 
		    'per_page'    => $per_page 
          ) );
		  
		}
		
		function column_default( $item, $column_name ) {
		  switch( $column_name ) {
			case 'status':
				return get_icon( $item );
		    case 'subject':
		    case 'from':
		    case 'to':
		    	return $item[ $column_name ];
		    case 'datetime':
		    	return  date("d/m/Y H:i" , strtotime( $item[ $column_name ]));
		    default:
		      return $item[ $column_name ];
		  }
		}

        function column_cb($item) {
            return get_icon( $item );
        }
		
		public function __construct($begin, $end, $filter = FALSE) {
			
			parent::__construct(array(
				'singular' => __('sent email', 'turbosmtp'),
				'plural' => __('sent emails', 'turbosmtp'),
				'ajax' => true
			));
			$this->begin = $begin;
			$this->end = $end;
			$this->filter = $filter;
							
		}
		
	}

?>