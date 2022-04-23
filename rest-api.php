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

        //check availabilty
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
    public function charter_boat(\WP_REST_Request $request){
        $params = $request->get_params();
        $boat = new Charter_Boat();
        return $boat;
    }

    /**
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
     * Reserve the charter by passing in the required fields
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
        $booking = new Charter_Booking();
        $booking->save_booking($params);
        return $booking;
    }
    


}
