<?php
/**
 * Create wp rest api namespace and endpoint to post new csv files
 */
namespace Charter_Bookings;
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

        register_rest_route( 'charter-bookings/v3', 'charter-boat', array(
            'methods' => 'GET',
            'callback' =>array($this, 'get_boat'),
            'permission_callback' => '__return_true'
            ) 
        );



    }


    /**
     * Business end of things. 
     * Each of these is a callback function. I have them in order and 
     * they coorespond to the routes above in the same order from top to bottom of page
     */
    public function get_boat(\WP_REST_Request $request){
        $params = $request->get_params();
        $boat = new Charter_Boat();
        return $boat;
    }
    


}
