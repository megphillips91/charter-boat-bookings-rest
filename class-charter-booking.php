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
    public $id_query;
    public $booking_args;

    public function __construct(){
        $this->errors = array();
    }

    public function get_booking($id){
        $this->id = intval($id);
        global $wpdb;
        $booking = $wpdb->get_row(
          $wpdb->prepare("SELECT * from {$wpdb->prefix}charter_boat_bookings WHERE id=%d",
          $this->id)
        );
        $this->errors['db_error'] = $wpdb->last_error;
        $this->id_query = $wpdb->last_query;
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
        //MEGTODO: set up errors for required fields
        global $wpdb;
        $this->booking_args = $booking_args;
        
        //setting up the query
        $wpdb->insert( 
            $wpdb->prefix.'charter_boat_bookings', 
            array(
                'booking_status' => $booking_args['booking_status'],
                'start_datetime' => $booking_args['start_datetime'],
                'duration' => $booking_args['duration'],
                'start_location'=>$booking_args['start_location'],
                'end_location'=>$booking_args['end_location'],
                'tickets'=>$booking_args['tickets'],
                'is_private'=>$booking_args['is_private'],
                'customer_name'=>$booking_args['customer_name'],
                'customer_phone'=>$booking_args['customer_phone'],
                'customer_email'=>$booking_args['customer_email'],
            ),
            array(
                '%s',
                '%s',
                '%f',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
            )
        );
        $this->booking_id = $wpdb->insert_id;
        if($wpdb->insert_id !== 0){
           $this->get_booking($this->booking_id);
        }
        $this->errors['db_error'] = $wpdb->last_error();
    }

    /**
     * @param array args
     */
    public function edit_booking($id, $args){
       global $wpdb;
       $charter_attributes = array(
        'booking_status' => '%s',
        'start_datetime' => '%s',
        'duration'=> '%f',
        'start_location'=> '%s',
        'end_location'=> '%s',
        'tickets'=> '%d',
        'is_private'=> '%s',
        'customer_name'=> '%s',
        'customer_phone'=> '%s',
        'customer_email'=> '%s',
       );
       $type = array();
       foreach($args as $key=>$arg){
            $type[] = $charter_attributes[$key];
       }
       
        $wpdb->update(
            "{$wpdb->prefix}charter_boat_bookings",
            $args,
            array(
                'id'=>$id
            ),
            $type,
            array(
                '%d'
            )
        );
        $this->get_booking($id);
    }


}


?>