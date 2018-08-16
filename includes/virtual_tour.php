<?php

class _virtual_tour {
    
    
    // private
    private $db;
    private $prepared_query;
    private $query_results;
    private $customers_message;  
    private $customers_id;
    private $message_elements = array();
    private $select;
    private $data;
    private $testing_string;
    private $directory;
    private $vt_path;
    private $vt_sources;
    private $tour_path;
    private $source_path;
    
    
    // private helper functions
    // name : create_callback_arguments
    // params: none;
    // functions: creates a string of arguments
    //             to send to the page
    // returns arguments
    private function create_callback_arguments($vin) {
        $arguments = "/$vin/" . $this->customers_id;
        return $arguments;
    }

    
    //public 
    
    
    function __construct($dbh) {
    
        $this->db = $dbh;
        $this->vt_path = "/virtual-tour/pages";
        $this->vt_sources = '/sites/default/files/virtual_tour_vehicles'; 
    $this->source_path = "https://highlife.thinkrium.com" . $this->vt_sources . "/";
        $this->tour_path = "https://highlife.thinkrium.com" . $this->vt_path;
    }


        public function log($message) {
            file_put_contents('contents.txt', $message);
            
        }
        

    // name : set_message
    // params : message is the incoming message from the customer
    // functions : sets the incoming message to the variable in the class
    //             message
    // returns : nothing
    public function set_message($incoming_message) {
         $this->customers_message = $incoming_message;
        
    }

    // name set_message_elements
    // params : element parsed message elements
    // function : sets the existing message elements to the local var
    // returns nothing
    public function set_message_elements($message_elements) {
         $this->message_elements = $message_elements;
    }
    
    // name : set_recipient_id
    // params : message is the incoming message from the customer
    // functions : sets the incoming message to the variable in the class
    //             message
    // returns : nothing
    public function set_recipient_id($rid) {
         $this->customers_id = $rid;
    }
    
    // name : set_data
    // params : data is the data array
    // functions : set the data for the slide view
    // returns : none
    public function set_data($data) {
        $this->data = $data;


    }
    
    // name : build_vt_data_array_base
    // params : none;
    // functions : builds the data array base to add to
    // returns : nothin
    public function build_vt_data_array_base() {
        $this->data = array(
            'recipient' => array( 'id' => $this->customers_id ),
            'message' => array(
                'attachment' => array(
                    'type' => 'template', 
                    'payload' => array(
                        'template_type' => 'generic',
                        'elements' => array(
                        ),    
                    ),        
                ),            
            ),
        );
    }
    
    // name : get_query_results
    // params : query_results is the query results created in the dialogue
    // functions : sets the internal variable to the parameter
    // returns : nothing
    public function get_query_results($query_results) {

        $this->query_results = $query_results;
    }
    
    // name : add_slide
    // params : none;
    // functions : adds a slide to the slide show based on query that adds to 
    //              the data array
    // returns : none
    public function add_slide() {
        
        $index = 0;
        
        foreach($this->query_results as $result) {

             $this->data['message']['attachment']['payload']['elements'][$index] = array(
                    'title' => $result['car_name'],
                    'image_url' => $this->source_path . $result['image_file_name'], 
                    'buttons' => array(
                        array(
                            "type" => 'web_url', 
                            'url' => $this->tour_path . $this->create_callback_arguments($result['vin']) ,
                            'title' => "See the virtual tour",
                    ),
                ), 
            );
                    
            $index++;
        }     
    }
    
        // name : get_message
        // params : none
        // functions : returns the existing mesage to only db once
        // returns messsage
        public function get_message() {
            return $this->customers_message;
        }
    
    
    
    // name : get_data 
    // params: none
    // functions : returns the data array to be converted to a context
    // returns : The data array
    public function get_data() {
        return $this->data;
    }
    
    
}