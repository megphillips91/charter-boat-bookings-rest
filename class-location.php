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
class CB_Location {
    public $id;
    public $name;
    public $address;
    public $latitude;
    public $longitude;
    public $description; //a permalink url
  
    public function __construct($field, $value){
        $this->get_location_by($field, $value);
    }
  
    private function get_location_by($field, $value){
      switch ($field) {
        case 'id':
          $this->id = $value;
          $this->address = get_option('cb_location_address_'.$this->id);
          $this->name = get_option('cb_location_name_'.$this->id);
          $this->latitude = get_option('cb_location_latitude_'.$this->id);
          $this->longitude = get_option('cb_location_longitude_'.$this->id);
          $this->longitude = get_option('cb_location_description_'.$this->id);
          break;
        case 'name':
          $this->get_id($value);
          $this->address = get_option('cb_location_address_'.$this->id);
          $this->name = get_option('cb_location_name_'.$this->id);
          $this->latitude = get_option('cb_location_latitude_'.$this->id);
          $this->longitude = get_option('cb_location_longitude_'.$this->id);
          $this->longitude = get_option('cb_location_description_'.$this->id);
          break;
      }
    }
  
    private function get_id($name){
      global $wpdb;
      $qry = "select option_name from ".$wpdb->prefix."options where option_value = '".$name."' and option_name like '%cb_location_name_%' limit 1";
      $row = $wpdb->get_row($qry);
      if($row){
        $str = $row->option_name;
        preg_match_all('!\d+!', $str, $matches);
        $this->id = implode(' ', $matches[0]);
      }
    }
  
    public function deactivate_location(){
      $fields = array('address', 'latitude', 'longitude', 'name');
      foreach($fields as $field){
        $value = $this->$field;
        $current_number = get_option('cb_number_locations');
        $option = 'cb_location_'.$field.'_'.$this->id;
        update_option('dep_'.$option, $value);
        delete_option($option);
        /* reduce number by 1 */
        $current_number = get_option('cb_number_locations');
        $number = ((int)$current_number) - 1 ;
      }
    }

    
  
  } //end class


?>