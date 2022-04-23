<?php
/**
 * Create wp rest api namespace and endpoint to post new csv files
 */
namespace Charter_Bookings;
use \DateTime;
use \DateInterval;
use \DateTimeZone;

/**
 * The assumption of this new plugin is to integrate with WP at the most basic level
 * WooCommerce can integrate, but you need an additional plugin to enable online payments for bookings
 */
class Charter_Boat {
   public $captain;
   public $terms; //a full url
   public $faqs; //a full url
   public $capacity;
   public $open_weather_key;
   public $temperature_units;
   public $wind_units;
   public $weeks_in_advance;
   public $durations;
   public $buffer_between;
   public $hours_prior_notice;
   public $open_days;
   public $black_outs;

   public function __construct(){

   }

   private function get_captain(){
       
   }

}

?>