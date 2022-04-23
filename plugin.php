<?php
/**
 * Plugin Name: Charter Boat Bookings
 * Plugin URI: http://msp-media.org/projects/plugins/charter-bookings
 * Description: Charter Boat Bookings is a bookings plugin designed gutenberg-first as a REST-API backend server. It offers the support that a charter boat booking system will need 
 * to serve the data needed for a platform agnostic UX. This plugin is one of a series of plugins intended to be used as a package that can provide a clean 
 * separation of concerns, integrate with the WooCommerce Store and Rest API, and even other platforms. This plugin could be used as a SaaS backend or pretty much anything. 
 * Author: megphillips91
 * Contributors: megphillips91
 * Author URI: http://msp-media.org/
 * Version: 1.7.1
 * License: GPL2+
 * http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

 /*
 This is the re-base from 1.7 on svn. I need to get the changes from yesterday in here. Charter Boat Bookings is free software: you can redistribute it and/or modify
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

namespace Charter_Bookings;
use \Datetime;
use \DateTimeZone;
use \DateInterval;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
  * Include plugin files
  */

  require_once plugin_dir_path( __FILE__ ) . 'rest-api.php';


?>