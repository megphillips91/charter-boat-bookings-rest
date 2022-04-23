<?php
/**
 * Create wp rest api namespace and endpoint to post new csv files
 */
namespace Charter_Boat_Bookings;
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
   private $open_weather_key;
   public $temperature_units;
   public $wind_units;
   public $weeks_in_advance;
   public $durations;
   public $buffer_between; //so this gets added to each charter duration during availability checks
   public $hours_prior_notice; //so this gets added to the start time of the desired start time to check if that works?
   public $open_days;
   public $blackouts;
   private $weather_key;

   public function __construct(){
        $this->get_boat_settings();
   }

   private function get_boat_settings(){
       $this->capacity = get_option('cb_booking_capacity');
       $this->captain = get_option('cb_captain'); //user_id
       $this->durations = get_option('cb_durations');
       $this->open_days = get_option('cb_open_days');
       $this->terms = get_option('cb_terms_slug');
       $this->faqs = get_option('cb_faqs_slug');
       $this->buffer_between = get_option('cb_same_day_buffer');
       $this->hours_prior_notice = get_option('cb_hours_prior_notice');
       $this->weeks_in_advance = get_option('cb_weeks_advance'); //available for booking how many weeks in advance?
       $this->wind_units = get_option('cb_wind_units');
       $this->temperature_units = get_option('cb_temp_units');
       $this->weather_key = get_option('cb_open_weather_key');
       $this->blackouts = new CB_Blackouts();
   }

}

?>