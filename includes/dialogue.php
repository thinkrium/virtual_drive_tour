<?php
    class _dialogue  {
        
        // private      
        private $options;
        private $context;
        private $token;
        private $message;
        private $recipient_id;
        private $db;
        
        private $vehicle_query_with_adjectives;
        
        private $message_elements;
        private $data;
        private $message_size;
        
        private $recent_message;
                
        // name : get_options
        // params : none
        // functions : returns the options created in set options
        // return :$options
        private function get_options() {
             return $this->options;
        }
        
        
        //public 

        public function log($message) {
            file_put_contents('contents.txt', $message);
            
        }
        
        
        // name : constructor
        // params : none
        // functions : creates the dialogue instance
        // returns : none
        function __construct($token, $dbh) {
            $this->token = $token;
            $this->db = $dbh;
            
            $this->vehicle_query_with_adjectives = "select count(vehicle_adjectives.vin) as count, vehicle_adjectives.vin as vin, vehicle_adjectives.adjective , virtual_tour_files.* from vehicle_adjectives join drive on drive.vin = vehicle_adjectives.vin join virtual_tour_files on virtual_tour_files.vin = vehicle_adjectives.vin where ";
            
            }
            
        // name : check_for_user
        // params : none
        // functions : queries the db and checks for the  user id c
        // returns : none;
        public function check_for_user() {
            try {
                $statement = "select fb_id, timestamp from customers_message where fb_id = :fb_id order by cm_id desc limit 1";
              
                $prepared = $this->db->prepare($statement);
                $prepared->execute([":fb_id" => $this->recipient_id]);
                $this->recent_message = $prepared->fetchAll(\PDO::FETCH_ASSOC)[0];
            }
            catch(PDOException $e) {
                $this->log( print_r($e->getMessage(), true) );
            }
         
   
        }


        // name : parse_message 
        // params : none
        // functions : breaks up the incoming message into individual elements
        //             and then populates an array to compare each word to a car
        //             in the db
        // returns : nothing
        public function parse_message() {
        
            $adjectives = explode(' ', $this->message);

            $index = 0;
            
            foreach($adjectives as $adjective) {
                $this->message_elements[":adjective_$index"] = strtolower($adjective);
                $index++;
            }
            
        }

        // name : get_elements
        // params : none
        // functions : returns the elements
        // returns : elements
        public function get_elements() {
            return $this->message_elements;   
        }
        
        // name: requests_tour
        // params: none
        // functions : determines the the type of request and returns a boolean
        //             if request is there
        // return : boolean true or false for tour requested
        public function requests_tour() {
            return (count($this->query_results) > 0) ? true : false;
        }
        
        // name : recently_asked
        // params : none
        // functions : checks if the conversation has already placed calls check
        //             for user which is a  private function
        // returns : boolean whether or not converstion was already underway
        public function recently_asked() {
            return (count($this->recent_message) > 0) ? true : false;
        }
        
        // name : get_recent_message()
        // params : none
        // functions : returns the recently asked question
        // returns : recent_message;
        public function get_recent_message() {

            return $this->recent_message;      
        }
        
        // name : set_data_array();
        // params: data holds the data array defaults to null
        // functions : sets the internal data variable to the existing data array
        // returns nothing
        public function set_data_array($data = null) {
            if($data) {
                $this->data = $data;   
            }    
            else {
                $this->data = array(
                    'recipient' => array('id' => $this->recipient_id),
                    'message' => array('text', 'test'),
                );
            }
        }
        
        // name : get_data
        // params : none
        // functions : returns the data array for other uses
        // returns : data array
        public function get_data() {
             return $this->data;
        }
        
        // name: set_options
        // params : data holds the data set by virtual tour
        // functions : sets the options to a hard coded value
        public function set_options() {
            
            $this->options =  array(
                'http' => array(
                    'method' => "POST",
                    'content' => json_encode($this->data),
                    'header' => "Content-Type: application/json\n"
                )     
            );
        }
        
        
        // name : set_message 
        // params : message given by facebook
        // functions : sets the message for the dialoge if the parameter is set 
        //             to null it pulls the most recent message from db and sets
        //             it the met message
        // returns : nothing
        public function set_message($message = null) {

            if($message) {
               $this->message = $message;    
            } 
            else {

                try {
                    
                    $statement = 'select message from customers_message where fb_id = :fb_id order by cm_id desc limit 1';       

                    $prepared_statement = $this->db->prepare($statement);
                    $prepared_statement->bindParam(':fb_id', $this->recipient_id, \PDO::PARAM_INT);
                    $prepared_statement->execute();
                    $this->message = $prepared_statement->fetchAll(\PDO::FETCH_ASSOC)[0]['message'];

                }
                catch(PDOException $e) {
                   $this->log( print_r($e->getMessage(), true));   
                }
                
            }
        }
        
        // name : set_recipient 
        // params : message given by facebook
        // functions : sets the message for the dialoge
        // returns : nothing
        public function set_recipient_id($recipient) {
           $this->recipient_id = $recipient;    
        }
        
        // name : create_quick_replay
        // params: none
        // functions : creates a data array that quick replys 'would you like to see a virtual tour of the car'
        // returns : nothing
        public function create_quick_reply() {
            $this->data = array(
                'recipient' => array('id' => $this->recipient_id),
                'message' => array(
                    'text' => 'Would you like to see a virtual tour of any of our vehicles?',
                    'quick_replies' => array(
                        
                        array(
                           'content_type' => 'text', 
                           'title' => "Yes",
                           'payload' => "yes_to_virtual_tour",
                        ),
                        array(
                           'content_type' => 'text', 
                           'title' => "No",
                           'payload' => "no_to_virtual_tour",
                        ),
                    ),
                ),
            );
            
        }
        
    
        // name : create_vehicle_adjectives
        // params : none;
        // functions : breaks up the multiple possible terms in the message to
        //             create possible adjectives and pushes them into an array
        //             all while incrementing an index and if the index is not =
        //             the size of the message array than add the 'or' to the 
        //             end of the sentence
        // returns : nothing    
        public function create_vehicle_adjectives() {
            
            $this->message_size = count($this->message_elements);
        
            $index = 0;


            foreach($this->message_elements as $element) {
            
                $this->vehicle_query_with_adjectives .= " vehicle_adjectives.adjective = :adjective_$index ";
            
                $index++;

                if($index < $this->message_size) {
                    $this->vehicle_query_with_adjectives .= " or ";
                }
            }
        
           $this->vehicle_query_with_adjectives .= " group by vehicle_adjectives.vin order by count(vehicle_adjectives.vin) desc;";

        }
    
        // name : execute_query
        // params : nothing
        // functions : prepares and executes each query 
        // returns : nothing
        public function execute_query() {

            try {
                $this->prepared_query = $this->db->prepare($this->vehicle_query_with_adjectives);
          
                $this->prepared_query->execute($this->message_elements);
                
            }
            catch(PDOException $e) {
                $this->log(  print_r($e->getMessage(), true) );
                
            }
        }
        
        // name : get_message
        // params : none
        // functions : returns the existing mesage to only db once
        // returns messsage
        public function get_message() {
            return $this->message;
        }
    
        // name : set_query_results 
        // params: none;
        // functions : after the query has been executed this function is called
        //           and retrieves the results of the query and sets the results
        // returns : returns a query object
        public function set_query_results() {
            try {
                 $this->query_results = $this->prepared_query->fetchAll(\PDO::FETCH_ASSOC);

            }
            catch(PDOException $e) {
                $this->log(  print_r($e->getMessage(), true) );
                
            }

        }
    
        // name : get_query_results
        // params : none
        // functions:  returns the query results
        // returns query results
        public function get_query_results() {
            return $this->query_results;
        }
        
        
        // name : store_message
        // params : none
        // functions : stores the message in format in the db to retrieve for later 
        // returns : nothing
        public function store_message() {
             try {
                 $prepared = $this->db->prepare('insert into customers_message ( fb_id, message, timestamp) values (:fb_id, :message, :timestamp)');
                 $prepared->bindParam(':fb_id', $this->recipient_id,  \PDO::PARAM_INT);
                 $prepared->bindParam(':message', $this->message, PDO::PARAM_STR);
                 $prepared->bindParam(':timestamp', time(),  \PDO::PARAM_INT);
                 $prepared->execute();
             }
             catch(PDOException $e) {
                    $this->log(print_r($e->getMessage(), true ) );   
             }
        }
        
        // name
        
        // name : set_context 
        // params : none
        // functions : sets teh context based on options
        // returns : nothing
        public function set_context() {
            $this->context = stream_context_create($this->get_options());
        }
        
        // name : send_to_fb
        // params : none
        // functions : takes the context and sneds it to fb to put in the 
        //             messenger dialogue
        // returns : nothing
        public function send_to_fb() {
            file_get_contents("https://graph.facebook.com/v2.6/me/messages?access_token=" . $this->token, false, $this->context);
        }
    }
    
?>