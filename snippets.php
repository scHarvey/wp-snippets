<?php
/*
*One issue somewhat common in WP is scheduled posts not beign published when they're supposed to be.
*The following bits of code create a new scheduled function that runs every 5 minutes and cleans up posts that missed their schedule.
*/
//create a new cron schedule for an action every 5 minutes
add_filter( 'cron_schedules', 'cron_add_5minutes' );
function cron_add_5minutes( $schedules ) {

   $schedules['5minutes'] = array( // Provide the programmatic name to be used in code
      'interval' => 5*60, // Intervals are listed in seconds
      'display' => __('Every 5 Minutes') // Easy to read display name
   );
   return $schedules;
}

//schedule a new item for wp-cron
function my_activation() {
	if ( !wp_next_scheduled( 'hook_fix_missed_schedule' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), '5minutes', 'hook_fix_missed_schedule');
	}
}
add_action('wp', 'my_activation');



//function to find all posts in the post table that have missed their scheduled post date and go ahead and publish them

function fix_missed_schedule() {
	global $wpdb;

	$scheduledIDs = $wpdb->get_col("SELECT`ID`FROM`{$wpdb->posts}`"."WHERE("."((`post_date`>0)&&(`post_date`<=CURRENT_TIMESTAMP()))OR"."((`post_date_gmt`>0)&&(`post_date_gmt`<=UTC_TIMESTAMP()))".")AND`post_status`='future'");
	if(!count($scheduledIDs))
		return;
	foreach($scheduledIDs as $scheduledID){
		if(!$scheduledID)
			continue;
		wp_publish_post($scheduledID);
	}
}
add_action('hook_fix_missed_schedule', 'fix_missed_schedule');

/*
*END SNIPPET
*/
