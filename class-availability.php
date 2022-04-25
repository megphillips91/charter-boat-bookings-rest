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
 * @param start_datetime //send UTC time class will handle wp_time
 * @param duration //in minutes
 * @return bool Is the boat available? true or false;
 */
class CB_Availability {
  public $is_available;
  public $passes_weeks_in_advance;
  public $passes_blackouts;
  public $passes_hours_prior_notice;
  public $passes_open_today;
  public $server_timezone;
  public $wp_timezone_offset;
  public $query;
  public $query_start_object;
  private $charterboat;
  private $booking_query;
  private $wp_start_datetime;
  private $wp_now;
  
  
  public function __construct($start_datetime, $duration){
    $this->server_timezone = date_default_timezone_get();
    $this->query = array();
    $this->query['start_datetime'] = $start_datetime;
    $this->query['duration'] = $duration;
    //$this->wp_start_datetime = wp_date('Y-m-d H:i:s', strtotime($start_datetime) ); //this doesn't work
    $this->query_start_object = new DateTime($start_datetime);
    $this->set_end_datetime();
    $this->wp_now = new DateTime(NULL, new DateTimeZone(get_option('timezone_string')));
    $this->wp_timezone_offset = wp_date('P');
    $this->charterboat = new Charter_Boat();
    $this->passes_weeks_in_advance();
    $this->passes_blackouts();
    $this->passes_hours_prior_notice();
    $this->passes_open_today();
    $this->passes_booking_conflicts();
    
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

  private function passes_booking_conflicts(){
    $this->set_bookings_today();
    foreach($this->bookings_today as $booking){
      if($this->booking_conflicts($booking) === true){
        $this->passes_booking_conflicts = false;
        $this->is_available = false;
      }
    }
  }

  private function set_bookings_today(){
    $query_start = $this->query_start_object->format('Y-m-d 00:00:00');
    $query_end = $this->query_start_object->format('Y-m-d 23:59:59');
    $this->booking_query = new CB_Booking_Query('date_range', "$query_start | $query_end", 'DESC');
    $this->bookings_today = $this->booking_query->bookings;
  }
  
  private function booking_conflicts($booking){
    $UTC_booking_end_datetime = get_UTC_time($booking->end_datetime);
    $booking_endtime_object = new DateTime($UTC_booking_end_datetime);
    //the product ends within the booking window
    if($booking_endtime_object >= $this->query_start_object
      //the booking ends after the product starts
      && $booking_endtime_object <= $this->query['end_datetime_object'])
      //the booking ends before the product ends
      {
        return true;
      }
    $UTC_booking_start_datetime = get_UTC_time($booking->start_datetime);
    $booking_starttime_object = new DateTime($UTC_booking_start_datetime); 
    //the product starts within the booking window
    if($booking_starttime_object >= $this->query_start_object
      //the booking starts after the product starts
      && $booking_starttime_object <= $this->query['end_datetime_object'])
      //the booking starts before the product ends
      {
        return true;
      }
    //the booking ranges over the entire product
      if($booking_starttime_object <= $this->query_start_object
        //the booking starts before product starts
        && $booking_endtime_object >= $this->query['end_datetime_object'])
      {
        return true;
      }
    //the product ranges over the entire booking
      if($booking_starttime_object >= $this->query_start_object
        //the booking starts after product starts
        && $booking_endtime_object <= $this->query['end_datetime_object'])
        //the booking ends before the product ends
      {
        return true;
      }
  }

  protected function set_end_datetime(){
    $chunks = explode(' ', $this->query['start_datetime']);
    $query_start_date = $chunks[0]; //splitting date from time
    $query_start_time = $chunks[1];//splitting time from date
    $end_datetime_object = new DateTime($this->query['start_datetime']);
    $hoursminutes = $this->duration_to_hours_mins($this->query['duration']);
    if (strpos($this->query['duration'], '.') !== false){
      $end_datetime_object->add(new DateInterval("PT".$hoursminutes['H']."H".$hoursminutes['M']."M"));
    } else {
      $end_datetime_object->add(new DateInterval("PT".$hoursminutes['H']."H"));
    }
    
    $this->query['end_datetime'] = $query_start_date.$end_datetime_object->format(" H:i:s");
    $this->query['end_datetime_object'] = new DateTime($this->query['start_datetime']); //in wp time
}

/**
     * Return Hours and Minutes from Duration
     *
     * basically provides the needed information for PHP DateInterval
     *
     * @param  string $str_duration in hours float
     * @return array of integers
     */
    protected function duration_to_hours_mins($str_duration){
      $duration = (float)$str_duration;
      $duration_hours = floor($duration);
      $duration_minutes = ($duration-$duration_hours)*60;
      return array('H'=>$duration_hours, 'M'=>$duration_minutes);
  }




  

} //end class availabilty




 ?>
