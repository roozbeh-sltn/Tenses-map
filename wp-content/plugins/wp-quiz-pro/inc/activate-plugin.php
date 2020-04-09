<?php

global	$wpdb;
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$create_emails = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wp_quiz_emails` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`pid` bigint(20) unsigned NOT NULL,
	`username` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`date` date NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
maybe_create_table( $wpdb->prefix . 'wp_quiz_emails', $create_emails );

$create_players = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wp_quiz_players` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`pid` bigint(20) unsigned NOT NULL,
	`date` date NOT NULL,
	`user_ip` varchar(50) NOT NULL,
	`username` varchar(255) NOT NULL,
	`correct_answered` smallint(5) unsigned NULL,
	`result` varchar(255) NOT NULL,
	`quiz_type` varchar(16) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
maybe_create_table( $wpdb->prefix . 'wp_quiz_players', $create_players );

$create_fb_users = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wp_quiz_fb_users` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`uid` bigint(20) unsigned NOT NULL,
	`created_at` date NOT NULL,
	`updated_at` date NOT NULL,
	`email` varchar(255) NOT NULL,
	`first_name` varchar(255) NOT NULL,
	`last_name` varchar(255) NOT NULL,
	`gender` varchar(16) NOT NULL,
	`picture` varchar(500) NOT NULL,
	`friends` longtext NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
maybe_create_table( $wpdb->prefix . 'wp_quiz_fb_users', $create_fb_users );

$create_plays = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "wp_quiz_fb_plays` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` bigint(20) unsigned NOT NULL,
	`pid` bigint(20) unsigned NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
maybe_create_table( $wpdb->prefix . 'wp_quiz_fb_plays', $create_plays );

$quiz_version = get_option( 'wp_quiz_pro_version' );
if ( false === $quiz_version ) {
	update_option( 'wp_quiz_pro_version' , '1.0.2' );
}

$quiz_settings = get_option( 'wp_quiz_pro_default_settings' );
// Create Settings
if ( false === $quiz_settings ) {

	$mts_username = '';
	// MTS Connect plugin username
	$mts_connect_data = get_option( 'mts_connect_data' );
	if ( false !== $mts_connect_data ) {
		$mts_username = $mts_connect_data['username'];
	}

	// Create Options
	$quiz_settings = array(
		'analytics' => array(
			'profile_name' 	=> '',
			'tracking_id' 	=> '',
		),
		'mail_service' => '0',
		'mailchimp' => array(
			'api_key' 	=> '',
			'list_id' 	=> '',
		),
		'getresponse' => array(
			'api_key' 		=> '',
			'campaign_name' => '',
		),
		'aweber' => array(
			'account_id' => '',
			'access_key' => '',
			'access_secret' => '',
			'consumer_key' => '',
			'consumer_secret' => '',
			'listid' => '',
		),
		'defaults'	=> array(
			'rand_questions' 	=> 0,
			'rand_answers' 		=> 0,
			'restart_questions' => 0,
			'promote_plugin' 	=> 0,
			'mts_username'		=> $mts_username,
			'embed_toggle'		=> 0,
			'show_ads'			=> 0,
			'repeat_ads'		=> 0,
			'fb_app_id'			=> '0',
			'ad_nth_display' 	=> '0',
			'countdown_timer' 	=> '0',
			'auto_scroll'		=> 1,
			'force_action' 		=> 0,
			'share_buttons'		=> array( 'fb', 'tw', 'g+', 'vk' ),
			'share_meta'		=> 1,
		),
		'ad_code' 			=> array(),
		'players_tracking' 	=> 0,
	);

	update_option( 'wp_quiz_pro_default_settings', $quiz_settings );
}

// Create Import/Export Directory
$wq_upload_dir = wp_upload_dir();
wp_mkdir_p( $wq_upload_dir['basedir'] . '/wp_quiz-import/' );
wp_mkdir_p( $wq_upload_dir['basedir'] . '/wp_quiz-result-images/' );


chmod( $wq_upload_dir['basedir'], 0755 );
chmod( $wq_upload_dir['basedir'] . '/wp_quiz-import/', 0755 );
chmod( $wq_upload_dir['basedir'] . '/wp_quiz-result-images/', 0755 );
