<?php
namespace Charter_Boat_Bookings;
use \Datetime;
use \DateTimeZone;
use \DateInterval;

/**
 * CB_Booking_Query
 * WPDB query against fields of the bookings table.
 * @param string required field: id, customer_email, customer_phone, booking_status, date_range, past, future
 * @param string required value: date_range comma separated string (ex: start_datetime, end_datetime)
 * @param string optional sort by start_datetime: ASC, DESC (default ASC)
 */

class CB_Booking_Query {
  public $field;
  public $value;
  public $query_type;
  public $query;
  public $ids;
  public $bookings;
  public $wpdb_last_query;
  public $db_error;
  public $param_errors;

  public function __construct($field, $value, $sort = ASC){
    $this->field = $field;
    $this->value = trim($value);
    $this->sort = $sort;
    $this->param_errors = array();
    global $wpdb;
    if(NULL === $field){trigger_error("must provide query type. Options are date, date_range, simple, datetime", E_USER_ERROR);}
    $this->query_type = $this->field;
    if($this->args_are_valid()){
      $prepare_query = 'prepare_'.$field.'_query';
      $this->$prepare_query(); //doing a variable method name here to call the query preparation methods for the different types of booking queries I am offering here (hope this is okay :) 
      $results = $wpdb->get_results($this->query);
      $wpdb->show_errors();
      $this->last_error = $wpdb->last_error;
      $this->wpdb_last_query = $wpdb->last_query;
      $this->ids =  cb_wp_collapse($results, 'id') ;
      $this->bookings = array();
      foreach($this->ids as $id){
        $charter_booking = new Charter_Booking();
        $charter_booking->get_booking($id);
        $this->bookings[] = $charter_booking;
      }
    } else {
      return false;
    }
    

  }

  private function args_are_valid(){
    switch ($this->query_type){

      case 'id':
        if( is_int($this->value) ){
          return true;
        } else {
          $this->param_errors['value'] = 'id must be an integer';
          return false;
        }
        break;

      case 'future':
          if( 'future' === $this->value ){
            return true;
          } else {
            return false;
          }
          break;

      case 'past':
        if( 'past' === $this->value ){
          return true;
        } else {
          return false;
        }
        break;

      case 'booking_status':
        if( 'confirmed' === $this->value || 'reserved' === $this->value || 'all' === $this->value ){
          return true;
        } else {
          return false;
        }
        break;
      
      case 'date_range':
        if( !isset($this->value) ){
          return false;
        } else {
          $this->set_date_range_array();
          //MEGTODO: better validation on the string format
          if( array_key_exists('start', $this->value) && array_key_exists('end', $this->value) ){
            return true;
          }
        }
        break;
      
      case 'customer_email':
        if( !isset($this->value) ){
          return false;
        } else {
          //MEGTODO: validate email here
          return true;
        }
        break;
      
      case 'customer_phone':
        if( !isset($this->value) ){
          return false;
        } else {
          //MEGTODO: validate phone here
          return true;
        }
        break;

      case NULL:
        return false;
        break;
        
      default:
        return false;
    }

  }

  private function set_date_range_array(){
    $values = explode(', ', $this->value);
    $date_range = array();
    foreach($values as $value){
      if( str_contains( $value, 'start:' ) ){
        $date_range['start'] = str_replace('start: ', '', $value);
      }
      if( str_contains( $value, 'end:' ) ){
        $date_range['end'] = str_replace('end: ', '', $value);
      }
    }
    $this->value = $date_range;
  }

  private function prepare_id_query(){
    global $wpdb;
    $qry = "SELECT id FROM {$wpdb->prefix}charter_boat_bookings WHERE id=%d";
    $qry .= " ORDER BY start_datetime DESC";
    $this->query = $wpdb->prepare($qry, $this->value);
  }


  private function prepare_past_query(){
    global $wpdb;
    $sort = (!isset($this->sort)) ? 'ASC' : $this->sort;
    $qry = "select id from {$wpdb->prefix}charter_boat_bookings
where date(start_datetime)  <= date(now()) ";
    $qry .= " ORDER BY start_datetime $sort";
    $this->query = $wpdb->prepare($qry);
  }


  private function prepare_future_query(){
    global $wpdb;
    $sort = (!isset($this->sort)) ? "asc" : $this->sort;
    $qry = "select id from {$wpdb->prefix}charter_boat_bookings
where date(start_datetime)  >= date(now()) ";
    $qry .= " ORDER BY date(start_datetime) $sort";
    $this->query = $wpdb->prepare($qry);
  }

  private function prepare_booking_status_query(){
    global $wpdb;
    $sort = (!isset($this->sort)) ? 'ASC' : $this->sort;
    if('all' === $this->value ){
      $sort = (!isset($this->sort)) ? 'ASC' : $this->sort;
      $qry = "select * from {$wpdb->prefix}charter_boat_bookings ";
      $qry .= " ORDER BY start_datetime $sort";
      $this->query = $wpdb->prepare($qry);
    } else {
      $qry = "select * from {$wpdb->prefix}charter_boat_bookings
            where booking_status = %s ";
      $qry .= " ORDER BY start_datetime $sort";
      $this->query = $wpdb->prepare($qry, $this->value);
    }
    
  }

  private function prepare_date_range_query(){
      global $wpdb;
      $dates = $this->value;
      $start_date = sanitize_text_field($dates['start']);
      $end_date = sanitize_text_field($dates['end']);
      $sort = (!isset($this->sort)) ? 'ASC' : $this->sort;
      $qry = "select id from {$wpdb->prefix}charter_boat_bookings
  where date(start_datetime) ";
      if(is_array($this->value)){
        $qry .= ">= '%s'
  && date(start_datetime) <= %s";
      } else {
        if($this->value == 'future'){
          $qry .= " >= date(now()) ";
        }
        if($this->value == 'past'){
          $qry .= " <= now() ";
        }
      }
      $qry .= " ORDER BY start_datetime ".$sort;
      $this->query = $wpdb->prepare($qry, $start_date, $end_date);
  }

  /**
     * Get charters by
     * @param string field: id, customer_email, customer_phone, booking_status, date_range, past, future
     * @param string value
     * @value 
     */
    private function prepare_customer_email_query(){
      global $wpdb;
      $qry = "SELECT id FROM {$wpdb->prefix}charter_boat_bookings WHERE customer_email=%s ";
      $qry .= " ORDER BY start_datetime ".$this->sort;
      $this->query = $wpdb->prepare($qry, $this->value);
    }

  /**
   * Get charters by
   * @param string field: id, customer_email, customer_phone, booking_status, date_range, past, future
   * @param string value
   * @value 
   */
  private function prepare_customer_phone_query(){
    global $wpdb;
    $qry = "SELECT id FROM {$wpdb->prefix}charter_boat_bookings WHERE customer_phone=%s ";
    $qry .= " ORDER BY start_datetime ".$this->sort;
    $this->query = $wpdb->prepare($qry, $this->value);
  }

} // end class declaration CB_Booking_Query



 ?>