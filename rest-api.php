<?php
/**
 * Create wp rest api namespace and endpoint to post new csv files
 */
namespace Charter_Boat_Bookings;
use \DateTime;
use \DateInterval;
use \DateTimeZone;

$charterboat_rest = new Charter_Boat_Rest_API();

class Charter_Boat_Rest_API {
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

        //grant charter capabilities
        register_rest_route( 'charter-boat-bookings/v3', 'grant-charter-role', array(
            'methods' => 'POST',
            'callback' =>array($this, 'grant_charter_role'),
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
        register_rest_route( 'charter-boat-bookings/v3', 'reserve-charter-booking', array(
            'methods' => 'POST',
            'callback' =>array($this, 'reserve_charter_booking'),
            'permission_callback' => '__return_true'
            ) 
        );

        //edit charter
        register_rest_route( 'charter-boat-bookings/v3', 'edit-charter-booking', array(
            'methods' => 'POST',
            'callback' =>array($this, 'edit_charter_booking'),
            'permission_callback' => '__return_true'
            ) 
        );

        //add or update booking meta
        register_rest_route( 'charter-boat-bookings/v3', 'update-booking-meta', array(
            'methods' => 'POST',
            'callback' =>array($this, 'update_booking_meta'),
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
     * @param start_datetime Y-m-d H:i:s in WP Timezone
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
        $UTC_start_datetime = get_UTC_time($params['start_datetime']);
        $availability = new CB_Availability($UTC_start_datetime, $params['duration']);
        return $availability;
    }

     /**
     * Grant charter capability
     * 
     * Role | Scope: charter_admin | protected; Current user must have capability of charter_admin || 'edit_others_posts'
     * 
     * @param int required user_id: wordpress user_id
     * @param string required capability: charter_admin or charter_affiliate
     * 
     */
    public function grant_charter_role(\WP_REST_Request $request){
        if( !current_user_can('edit_others_posts') && !$this->user_is_charter_admin() ){
            return new \WP_Error( 'no_permission', 'Invalid user', array( 'status' => 404 ) );
        } else {
            //lets do this
            $response = array();
            $params = $request->get_params();
            if( !isset($params['charter_role']) || !isset($params['user_id']) ){
                return new \WP_Error( 'required param missing', 'Invalid Params', array( 'status' => 418 ) );
            }
            update_user_meta( $params['user_id'], $params['charter_role'], 'cb_pub_'.wp_rand(100000000010000000001000000000, 9999999999999999999999999999999999999999) );
            $response['public_key'] = get_user_meta( $params['user_id'], $params['charter_role'], true );
            $response['caution'] = "This is your public API key. Please keep this confidential.";
            return $response;
        }
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
        if( !$this->user_has_permission() ){
            return new \WP_Error( 'no_permission', 'Invalid user', array( 'status' => 404 ) );
        } else {
            $params = $request->get_params();
            $params['sort'] = ( !isset($params['sort']) ) ? 'ASC': $params['sort'];
            $params['value'] = ( $params['field'] === 'id' ) ? intval( $params['value'] ) : $params['value'] ;
            $query = new CB_Booking_Query($params['field'], $params['value'], $params['sort']);
            return $query;
        }
    }

    /**
     * Reserve the charter by passing in the required fields
     * 
     * Role | Scope: charter_admin | protected, charter_affiliate | protected
     * 
     * @param string $start_datetime Y-m-d H:i:s in WordPress Time Zone
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
    public function reserve_charter_booking(\WP_REST_Request $request){
        if( !$this->user_has_permission() ){
            return new \WP_Error( 'no_permission', 'Invalid user', array( 'status' => 404 ) );
        } else {
            $response = array();
            $params = $request->get_params();
            //convert time
            $UTC_start_datetime = get_UTC_time($params['start_datetime']);
            //get back to business
            $availability = new CB_Availability($UTC_start_datetime, $params['duration']);
            $response['availability'] = $availability->is_available;
            if($availability->is_available !== false){
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
            } else {
                $same_booking_time = new Charter_booking();
                $same_booking_time->get_booking_by_start_datetime($params['start_datetime']);
                if($same_booking_time->customer_email === $params['customer_email']){
                    $response = array(
                        'error' => "you're doing it wrong. This booking already exists. Edit the booking, please use the update function",
                        'booking' => $same_booking_time,
                    );
                    return new \WP_Error( 'booking_exists', $response, array( 'status' => 418 ) );
                }
                return new \WP_Error( 'no_availabilty', $availability, array( 'status' => 418 ) );
            }
        }
    }

    /**
     * Edit charter
     * 
     * Pass in a series of field: value pairs as parameters. Any of the properties of the Charter_Booking class are valid arguments. Reference the Charter_Booking class
     * 
     * Role | Scope: charter_admin | protected, charter_affiliate | protected
     * 
     * @param int charter_id required
     * @param string value: pass in the type after pipe character (ex: stringvalue | type)
     * @param string type sprintf syntax reference the WPDB prepare method. Options are %s string, %d int, %f float
     * 
     */
    public function edit_charter_booking(\WP_REST_Request $request){
        if( !$this->user_has_permission() ){
            return new \WP_Error( 'no_permission', 'Invalid user', array( 'status' => 404 ) );
        } else {
            $params = $request->get_params();
            $response = array();
            $id = intval($params['id']);
            unset($params['id']);
            $charter = new Charter_Booking();
            $charter->edit_booking($id, $params);
            $response['charter_booking'] = $charter;
            return $response;
        }
    }

    /**
     * Add or Update Booking Meta
     * 
     * Meta_key is not unique. Add as many meta_values as you want with the same meta_key
     * To update a meta_key, meta_id is required. To fecth the meta_id, set replace TRUE and meta_id NULL
     * 
     * Role | Scope: charter_admin | protected, charter_affiliate | protected
     * 
     * @param int booking_id required
     * @param string meta_key required
     * @param string meta_value required
     * @param int meta_id optional: default AUTO INCREMENT; see notes on param replace
     * @param bool replace: default false; If replace true and meta_id is null, the call will return the meta_id and do nothing. Make a second call and provide the meta_id to replace
     * 
     */
    public function update_booking_meta(\WP_REST_Request $request){
        if( !$this->user_has_permission() ){
            return new \WP_Error( 'no_permission', 'Invalid user', array( 'status' => 404 ) );
        } else {
            $params = $request->get_params();
            $required_params = array(
                'booking_id',
                'meta_key',
                'meta_value',
            );
            //check for missing parameters
            foreach($required_params as $param){
                if(!isset($params[$param])){
                    return new \WP_Error( 'required_parameters', $param.' is required', array( 'status' => 418 ) );
                }
            }
            //do business
            $booking = new Charter_Booking();
            $booking->get_booking_by_id($params['booking_id']);
            $params['replace'] = (!isset($params['replace'])) ? false : $params['replace'] ;
            $params['meta_id'] = (!isset($params['meta_id'])) ? NULL : $params['meta_id'] ;
            if($params['replace'] === false){
                $booking->add_booking_meta($params['booking_id'], $params['meta_key'], $params['meta_value'] );
            } else {
                $booking->update_booking_meta($params['booking_id'], $params['meta_key'], $params['meta_value'], $params['meta_id'], $params['replace'] );
            }
            $booking->get_booking_by_id($params['booking_id']);
            $response = array($booking);
            return $response;
        }
    }


    /**
     * checks user permissions
     */
    protected function user_has_permission(){
        if( get_user_meta( get_current_user_id(), 'cb_charter_affiliate', true) === '' && !current_user_can('edit_others_posts') && get_user_meta( get_current_user_id(), 'charter_admin', true) === ''){
            return false;
        } else {
            return true;
        }
    }

    /**
     * checks if user is a charter admin
     */
    protected function user_is_charter_admin(){
        if( get_user_meta( get_current_user_id(), 'cb_charter_admin', true) === '' && !current_user_can('edit_others_posts') && get_user_meta( get_current_user_id(), 'charter_admin', true) === ''){
            return false;
        } else {
            return true;
        }
    }
    /**
     * Tokenize for app logins
     * MEGTODO: once the PWA is in production, we will tokenize the application password, etc and create a properly secure Oauth
     */
    protected function check_charter_token(){

    }
    


}
