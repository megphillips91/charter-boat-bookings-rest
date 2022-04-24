<?php
/**
 * Create wp rest api namespace and endpoint to post new csv files
 */
namespace Charter_Boat_Bookings;
use \DateTime;
use \DateInterval;
use \DateTimeZone;
use Automattic\WooCommerce\Client;

$charterboat_rest = new CharterBoat_Rest_API();

class CharterBoat_Rest_API {
    private $timezone;

    public function __construct(){
        $this->timezone = \get_option('timezone_string');
        add_action( 'rest_api_init',array( $this, 'register_routes' ) );
    }

    public function register_routes(){
        //details on the boat and business operations
        register_rest_route( 'charter-boat-bookings/v3', 'charter-boat', array(
            'methods' => 'GET',
            'callback' =>array($this, 'charter_boat'),
            'permission_callback' => '__return_true'
            ) 
        );

        //check availabilty
        register_rest_route( 'charter-boat-bookings/v3', 'check-availability', array(
            'methods' => 'POST',
            'callback' =>array($this, 'check_availability'),
            'permission_callback' => '__return_true'
            ) 
        );

        //get charters
        register_rest_route( 'charter-boat-bookings/v3', 'get-charters', array(
            'methods' => 'POST',
            'callback' =>array($this, 'get_charters_by'),
            'permission_callback' => '__return_true'
            ) 
        );

        //reserve charter
        register_rest_route( 'charter-boat-bookings/v3', 'reserve-charter', array(
            'methods' => 'POST',
            'callback' =>array($this, 'reserve_charter'),
            'permission_callback' => '__return_true'
            ) 
        );
    }


    /**
     * Business end of things. 
     * Each of these is a callback function. I have them in order and 
     * they coorespond to the routes above in the same order from top to bottom of page
     */

     /**
      * Charter boat schema information
      *
      * Role | Scope: all | public
      */
    public function charter_boat(\WP_REST_Request $request){
        $params = $request->get_params();
        $boat = new Charter_Boat();
        return $boat;
    }

    /**
     * Check availability
     * 
     * Role | Scope: all | public
     * 
     * @param start_datetime Y-m-d H:i:s in UTC
     * @param duration in minutes
     * @return object
     */
    public function check_availability(\WP_REST_Request $request){
        $params = $request->get_params();
        $response = array();
        if( !isset($params['start_datetime']) || !isset($params['duration']) ){
            $response['error'] = "You are doing it wrong: start_datetime and duration are required parameters";
            return $response;
        }
        $availability = new CB_Availability($params['start_datetime'], $params['duration']);
        return $availability;
    }

    /**
     * Get charters by
     * 
     * Role | Scope: charter_admin | protected, charter_affiliate | protected
     * 
     * @param string required field: id, customer_email, customer_phone, booking_status, date_range, past, future
     * @param string required value: date_range comma separated string (ex: start_datetime, end_datetime)
     * @param string booking_status values: all, reserved, confirmed, on-hold, pending-payment, cancelled, rescheduled
     * @param string date_range values: format as comma separated string (ex: start_datetime, end_datetime) Y-m-d H:i:s in UTC
     * @param string optional sort by start_datetime: ASC, DESC (default ASC)
     * 
     */
    public function get_charters_by(\WP_REST_Request $request){
        $params = $request->get_params();
        $params['sort'] = ( !isset($params['sort']) ) ? 'ASC': $params['sort'];
        $params['value'] = ( $params['field'] === 'id' ) ? intval( $params['value'] ) : $params['value'] ;
        $query = new CB_Booking_Query($params['field'], $params['value'], $params['sort']);
        return $query;
    }

    /**
     * Reserve the charter by passing in the required fields
     * 
     * Role | Scope: charter_admin | protected, charter_affiliate | protected
     * 
     * @param string $start_datetime Y-m-d H:i:s in UTC
     * @param string $duration  in minutes
     * @param string customer_id  optional
     * @param string customer_name string required //first and last with spaces
     * @param string customer_email string required
     * @param string customer_phone string required but not validated for actual phone
     * @param int start_location required
     * @param int end_location required
     * @param bool is_private required
     * @param int tickets optional default 1
     * 
     */
    public function reserve_charter(\WP_REST_Request $request){
        $params = $request->get_params();
        $required_params = array(
            'booking_status',
            'start_datetime',
            'duration',
            'start_location',
            'end_location',
            'tickets',
            'is_private',
            'customer_email',
            'customer_phone',
            'customer_name'
        );
        foreach($required_params as $required){
            if(!isset($params[$required])){
                $params[$required] = NULL;
            }
        }
        $booking = new Charter_Booking();
        $booking->save_booking($params);
        return $booking;
    }

    /**
     * Edit charter
     * 
     * Pass in a series of field: value pairs as parameters. Any of the properties of the Charter_Booking class are valid arguments. Reference the Charter_Booking class
     * 
     * Role | Scope: charter_admin | protected, charter_affiliate | protected
     * 
     * @param int charter_id required
     * 
     */
    public function edit_charter_booking(\WP_REST_Request $request){
        $params = $request->get_params();
        if( !issset($params['charter_id']) ){
            $params['id'] = intval($id);
            $response_data['error'] = 'charter_id is a required field';
            $response_data['status'] = 404;
            $response = new \WP_REST_RESPONSE($response_data);       
            return $response;
        } else {
            $response_data = array();
            
            $response = new \WP_REST_RESPONSE($response_data);       
            return $response;
        }

    }
    


}
