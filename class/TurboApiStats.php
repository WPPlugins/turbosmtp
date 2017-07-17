<?php

function calculate_data( $curDate, $start_date, $first_date ) {

	$days_offset = $curDate - $first_date;

	$date = strtotime( "+" . $days_offset . " days", strtotime( $start_date ) );

	return date( "d/m/Y", $date );

}

function get_stat_array( $dataChart, $totalData, $totalLabels, $start_date, $end_date ) {

	$simple_array = array(
		"max"   => 0,
		"data"  => array(),
		"total" => $totalData
	);

	$setMax       = false;
	$setDate      = false;
	$setPointDate = 0;
	$curDate      = false;
	$label        = 0;
	$first_date   = 0;

	for ( $i = $totalLabels; $i < count( $dataChart ); $i ++ ) {

		if ( ! $setMax ) {
			$simple_array['max'] = $dataChart[ $i ];
			$setMax              = true;
			continue;
		}

		/*
		 *
		 * Fix in 1.7
		 */

        if ($setPointDate == 0 && !$setDate && $dataChart[$i] == -1)
            continue;

		if ( ! $setDate ) {

			$curDate = $dataChart[ $i ];

			if ( $first_date == 0 ) {
				$first_date = $curDate;
			}

			$simple_array['axis'][] = calculate_data( $curDate, $start_date, $first_date );
			$setDate                = true;
			continue;
		}

		if ( $dataChart[ $i ] == - 1 ) {

			$oldLabel = $label;

			for ( $j = 0; $j < ( $totalLabels - $setPointDate ); $j ++ ) {
				$simple_array["data"][ $dataChart[ $label ] ][] = 0;
				$label ++;
			}

			$label = $oldLabel;

			$setDate      = false;
			$setPointDate = $totalLabels;
		} else {

			$simple_array["data"][ $dataChart[ $label ] ][] = $dataChart[ $i ];
			$setPointDate ++;

		}

		$label ++;

		if ( $setPointDate == $totalLabels ) {
			$setDate      = false;
			$setPointDate = 0;
			$label        = 0;
		}

	}

	return $simple_array;

}

function custom_http_build_query( $query = array(), $custom = true ) {

	if ( $custom ) {
		return str_replace( '%2F', '%252F', http_build_query( $query ) );
	}

	return http_build_query( $query );

}

function routing_filter( $filter ) {

	switch ( $filter ) {

		case "queue":

			return '["NEW","DEFER"]';

			break;

		case "delivered":

			return '["SUCCESS","OPEN","CLICK","UNSUB","REPORT"]';

		case "read":

			return '["OPEN","CLICK","UNSUB","REPORT"]';

		case "click":

			return '"CLICK"';

		case "spam":

			return '"REPORT"';

		case "deleted":

			return '"UNSUB"';

		case "bounce":

			return '["FAIL"]';

		case "dropped":

			return '["SYSFAIL"]';

		default :

			return '"*"';

	}

}

class TurboApiStats extends TurboApiClient {

	private function getDecodedJson( $path, $args = array() ) {

		$response = $this->get( $path, $args );
		$body     = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );

	}

	public function getLastFeedEmail( $count ) {

		if ( ! in_array( $count, array( 10, 20, 30 ) ) ) {
			throw new TurboApiStatsError( "Only 10, 20, 30 values are accepted!" );
		}

		$api = $this->authorize();

		$response = $api->get( '/stats/email-feed-last/' . $count );

		return $response;

	}

	public function getPlans() {

		$api = $this->authorize();

		$response = $api->getDecodedJson( '/plans/' );

		return $response;

	}

	public function getEmails( $begin, $end, $filter, $page ) {


		$getParams = array(

			"start"  => $begin,
			"end"    => $end,
			"page"   => $page,
			"filter" => routing_filter( $filter ),
			"tz"     => "+01:00"

		);

		if ( $begin == false ) {
			unset ( $getParams['start'] );
		}

		if ( $end == false ) {
			$getParams['end'] = date( "Y-m-d" );
		}


		$api = $this->authorize();

		$emails = $api->getDecodedJson( '/stats/panes/emails', $getParams );


		unset ( $getParams["page"] );

		$total = $api->getDecodedJson( '/stats/panes/emails/count', $getParams );

		return array(

			"emails"      => $emails,
			"total"       => $total['count'],
			"total_pages" => ceil( $total['count'] / 10 )

		);


	}

	public function getStats( $start_date, $end_date ) {

		$api = $this->authorize();

		$getParams = array(

			"start_date" => $start_date,
			"end_date"   => $end_date,
			"grp"        => "d",
			"tz"         => "+01:00"

		);

		$stats = $api->getDecodedJson( '/stats2/chart-data', $getParams );

		$getParams["start"]  = $getParams["start_date"];
		$getParams["end"]    = $getParams["end_date"];
		$getParams["filter"] = routing_filter( "all" );

		unset ( $getParams['start_date'] );
		unset ( $getParams['end_date'] );
		unset ( $getParams["grp"] );

		$total = $api->getDecodedJson( '/stats/panes/emails/count', $getParams );

		return get_stat_array( $stats, $total['count'], 8, $start_date, $end_date );

	}

	public function CSVDownload( $begin, $end, $filter ) {

		$api = $this->authorize();

		$getParams = array(

			"authuser"   => $this->username,
			"authpass"   => $this->password,
			"start_date" => $begin,
			"end_date"   => $end,
			"offset"     => - 60,
			"filter"     => routing_filter( $filter )

		);

		if ( $begin == false ) {
			unset ( $getParams['start_date'] );
		}

		if ( $end == false ) {
			$getParams['end_date'] = date( "Y-m-d" );
		}

		return 'https://api.turbo-smtp.com/api/stats/emails/csv?' . custom_http_build_query( $getParams );

	}

	public function getOpensClicks( $start_date, $end_date ) {

		$api = $this->authorize();

		$getParams = array(

			"start_date" => $start_date,
			"end_date"   => $end_date,
			"offset"     => - 60,
		);

		$response = $api->getDecodedJson( '/stats/panes/opens-clicks?', $getParams );

		return $response;

	}

}

class TurboApiStatsError extends Exception {
}

?>