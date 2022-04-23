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
class Charter_Booking {
    public $id; //charter id
    public $customer_id; //as to hold a unique ID of the customer from whatever underlying framework
    public $customer_name; //
    public $customer_email; //required
    public $customer_phone; //string
    public $start_datetime; //date string
    public $duration; //hours float
    public $start_location; //location_id
    public $end_location; //location_id
    public $tickets; //number of tickets if not private
    public $is_private; //boolean
    public $booking_status;
    public $booking_meta;
    public $date_object;
    public $errors;
    public $booking_args;

    public function __construct(){
        $this->errors = array();
    }

    public function get_booking($id){
        global $wpdb;
        $booking = $wpdb->get_row(
          $wpdb->prepare("SELECT * from {$wpdb->prefix}charter_boat_bookings WHERE id=%d",
          $this->id)
        );
        $this->errors['db_error'] = $wpdb->print_error();
        if($booking){
            foreach($booking as $key=>$value){
            $this->$key = $value;
          }
        }
    
    }

    /**
     * pass in an array of booking details
     */
    public function save_booking($booking_args){
        $this->booking_args = $booking_args;
        $this->argstype = gettype($booking_args);
        //MEGTODO: set up errors for required fields
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix.'charter_boat_bookings',
            $this->booking_args
        );
        
        $this->booking_id = $wpdb->insert_id;
        if($this->booking_id !== 0){
           $this->get_booking($this->booking_id);
        }
        $this->errors['db_error'] = $wpdb->print_error();
    }


}


?>