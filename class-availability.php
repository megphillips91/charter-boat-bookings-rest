<?php
namespace Charter_Boat_Bookings;
use \Datetime;
use \DateTimeZone;
use \DateInterval;

/**
 * CB Check Availability
 *  
 * In other words, can I book your boat from start_datetime for duration? 
 * 
 * @param date_time //send UTC time class will handle wp_time
 * @param duration //in minutes
 * @return bool Is the boat available? true or false;
 */
class CB_Availability {
  public $available;
  public $passes_weeks_in_advance;
  public $passes_blackouts;
  public $passes_hours_prior_notice;
  public $passes_open_today;
  public $server_timezone;
  public $wp_timezone_offset;
  public $query;
  public $wp_start_datetime;
  public $wp_now;
  public $query_start_object;
  public $charterboat;
  
  
  public function __construct($start_datetime, $duration){
    $this->server_timezone = date_default_timezone_get();
    $this->query = array();
    $this->query['start_datetime'] = $start_datetime;
    $this->query['duration'] = $duration;
    $this->wp_start_datetime = wp_date('Y-m-d H:i:s', strtotime($this->query['start_datetime']) );
    $this->query_start_object = new DateTime($this->wp_start_datetime, new DateTimeZone(get_option('timezone_string')));
    $this->wp_now = new DateTime(NULL, new DateTimeZone(get_option('timezone_string')));
    $this->wp_timezone_offset = wp_date('P');
    $this->charterboat = new Charter_Boat();
    $this->passes_weeks_in_advance();
    $this->passes_blackouts();
    $this->passes_hours_prior_notice();
    $this->passes_open_today();
    $this->set_bookings_today();
    
  }

  private function passes_weeks_in_advance(){
    $window =  new DateTime(NULL, new DateTimeZone(get_option('timezone_string')));
    $window->add(new DateInterval('P'.$this->charterboat->weeks_in_advance.'W'));
    if($this->query_start_object > $window){
      $this->passes_weeks_in_advance = false;
    } else {
      $this->passes_weeks_in_advance = true;
    }
  }

  private function passes_blackouts(){
    $blackouts = $this->charterboat->blackouts->blackouts;
    $this->passes_blackouts = true;
    foreach($blackouts as $blackout){
      $start = new DateTime($blackout['start'], new DateTimeZone(get_option('timezone_string')));
      $end = new DateTime($blackout['end'], new DateTimeZone(get_option('timezone_string')));
      if($start <= $this->query_start_object && $this->query_start_object <= $end){
        $this->passes_blackouts = false;
      } 
    }
  }

  private function passes_hours_prior_notice(){
    //query start_datetime must give captain enough time to prepare the boat
    $window =  new DateTime(NULL, new DateTimeZone(get_option('timezone_string'))); //gets date object now
    $window->add(new DateInterval('PT'.$this->charterboat->hours_prior_notice.'H')); //goes back from now
    if($window < $this->query_start_object ){
      $this->passes_hours_prior_notice = true;
    } else {
      $this->passes_hours_prior_notice = false;
    }
  }

  private function passes_open_today(){
    $open_days = get_option('cb_open_days');
    $date = wp_date('D', strtotime($this->query['start_datetime']));
    if(!in_array($date, $open_days)){
      $this->passes_open_today = false;
    } else {
      $this->passes_open_today = true;
    }
  }

  private function set_bookings_today(){
    $args = array(
      'date_range' => array(
          'start' => $this->query_start_object->format('Y-m-d 00:00:00'),
          'end' => $this->query_start_object->format('Y-m-d 23:59:59')
      ),
      'booking_status'=>array('reserved', 'confirmed')
    );
    $this->booking_query = new CB_Booking_Query($args, 'date_range');
    $this->bookings_today = $this->booking_query->bookings;
  }




  

} //end class availabilty




 ?>
