<?php
/**
 * Plugin Name: Charter Boat Bookings
 * Plugin URI: http://msp-media.org/projects/plugins/charter-bookings
 * Description: Charter Boat Bookings is is a back-end REST API for charter boats to take online bookings and reservations.
 * Author: Meg Phillips
 * Author URI: http://msp-media.org/
 * Version: 2.0.1
 * License: GPL2+
 * http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: charter-boat-bookings
 *
 */

 /*
 Charter Boat Bookings is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 2 of the License, or
 any later version.
 Charter Boat Bookings is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Charter Boat Bookings. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 */

namespace Charter_Boat_Bookings;
use \Datetime;
use \DateTimeZone;
use \DateInterval;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//MEGTODO: delete this before publishing
add_filter( 'wp_is_application_passwords_available', '__return_true' );
/**
  * Include plugin files
  */

require_once plugin_dir_path( __FILE__ ) . 'rest-api.php';
require_once plugin_dir_path( __FILE__ ) . 'helper-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'class-location.php';
require_once plugin_dir_path( __FILE__ ) . 'class-blackouts.php';
require_once plugin_dir_path( __FILE__ ) . 'class-charter-boat.php';
require_once plugin_dir_path( __FILE__ ) . 'class-charter-booking.php';
require_once plugin_dir_path( __FILE__ ) . 'class-availability.php';
require_once plugin_dir_path( __FILE__ ) . 'class-booking-query.php';

/**
* =======================================
* ON PLUGIN ACTIVATION
* functions to be called on PLUGIN ACTIVATION - i.e. purge all custom data and tables
* =======================================
*/
register_activation_hook( __FILE__, __NAMESPACE__ . '\\cb_maybe_create_tables' );

function cb_maybe_create_tables(){
  global $wpdb;
  $admin_abspath = str_replace( site_url(), ABSPATH, admin_url() );
  $admin_php_path = $admin_abspath . 'includes/upgrade.php';
  require_once( $admin_php_path);
  $charset_collate = $wpdb->get_charset_collate();

  // ota_id would hold the payment integration order_id or payment_id
  //=== charter boat bookings table
  $table_name = $wpdb->prefix . 'charter_boat_bookings';
  $sql = "CREATE TABLE $table_name (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    booking_status varchar(100) DEFAULT NULL,
    start_datetime datetime DEFAULT NULL,
    duration float DEFAULT NULL,
    start_location varchar(100) DEFAULT NULL,
    end_location varchar(100) DEFAULT NULL,
    tickets int(11) DEFAULT NULL,
    is_private varchar(100) DEFAULT NULL,
    ota_id varchar(100) DEFAULT NULL,
    customer_id varchar(100) DEFAULT NULL,
    customer_phone varchar(100) DEFAULT NULL,
    customer_name varchar(100) DEFAULT NULL,
    customer_email varchar(300) DEFAULT NULL,
    PRIMARY KEY (id)
  ) $charset_collate;";
  maybe_create_table($table_name, $sql );
  
  //=== charter boat booking meta table
  $table_name = $wpdb->prefix . 'charter_boat_booking_meta';
  $sql = "CREATE TABLE $table_name (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    booking_id bigint(20) NOT NULL DEFAULT 0,
    meta_key varchar(100) DEFAULT NULL,
    meta_value longtext DEFAULT NULL,
    last_update datetime DEFAULT NULL,
    PRIMARY KEY (id)
  ) $charset_collate;";
  maybe_create_table($table_name, $sql );

}

add_action( 'plugins_loaded', __NAMESPACE__.'\\charter_boat_bookings_check_for_woo' );
function charter_boat_bookings_check_for_woo() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', __NAMESPACE__.'\\woocommerce_missing_notice' );
		return;
	}

}

function woocommerce_missing_notice() {
	/* translators: 1. URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Charter Boat Booking Payments requires WooCommerce to be installed and active. You can download %s here.', 'charter-boat-bookings' ), '<a href="https://woocommerce.com" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

/**
 * =======================================
 * DEACTIVATION HOOK
 * functions to be called on de-activation
 * =======================================
 */ 


/**
* =======================================
* ON PLUGIN DELETION
* functions to be called on PLUGIN DELETION - i.e. purge all custom data and tables
* =======================================
*/
// MEGTODO: deactivation hook to delete the sunset times table which we do not need.


/**
 * MEGTODO: the hours notice field should have some relation to the cycle of the api updating and sync
 * so in other words if a captain wants a 4 hours notice, then the sync cycle should be less than 4 hours and should notify the captain every time that it syncs whether or not
 * there is a new charter. 
 */
 


?>