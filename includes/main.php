<?php
 
 include "db.php";
 include "virtual_tour.php";
 include "dialogue.php";
 
 
    // name : main
    // params : none
    // functions : operates as the main thread like c++ or java or any of my js work
    // returns : none
    function main() {
        
        file_put_contents('fb.txt', file_get_contents("php://input")) ;

        $seconds = 60;
        $minutes = 0;

        // time differenc in seconds
        $time_difference = $seconds * $minutes;

        $db = new _db();        
        
        $payload_exists = false;
        
        $virtual_tour = new _virtual_tour($db->get_pdo());
        
        $dialogue = new _dialogue($db->get_token(), $db->get_pdo());

        $contents = file_get_contents('fb.txt');

        $contents = json_decode($contents);
   
        if($contents->entry[0]->messaging[0]->message->quick_reply->payload == 'yes_to_virtual_tour') {
            $payload_exists = true;
            
        }
        
        $message_recieved = $contents->entry[0]->messaging[0]->message->text;

        $rid = $contents->entry[0]->messaging[0]->sender->id;

        $dialogue->set_recipient_id($rid);

        $virtual_tour->set_recipient_id($rid);
        
        $dialogue->check_for_user();

        if($payload_exists) {
            
            // if the payload exists than the message is set by the most recent message from the user
            $dialogue->set_message();  
        }
        else {

            // if the payload is not sent than it comes directly from facebook
            $dialogue->set_message($message_recieved);  
        }
        
        // send the message to virtual tour with 
        // it has to come from dialoge and not from the fb post directly because
        // dialogue is doing some work here to decide the origin of the message
        $virtual_tour->set_message($dialogue->get_message());

        $dialogue->log("here " . print_r($dialogue->get_message(), true));

        $dialogue->parse_message();
        
        $virtual_tour->set_message_elements($dialogue->get_elements());
        
        $dialogue->create_vehicle_adjectives();

        $dialogue->execute_query();

        $dialogue->set_query_results();
        

        $virtual_tour->get_query_results(
                 $dialogue->get_query_results()
        );
        
            
        if($dialogue->requests_tour() && !$payload_exists ) {

            $recent_message = $dialogue->get_recent_message();


            // if there was a recent message before the time is up you do not 
            // activate the functionality
            // otherwise you create the quick reply and store the message
            
            if( count($recent_message) == 0 || $recent_message['timestamp'] + $time_difference < time() ) {
                $dialogue->create_quick_reply(); 
                $dialogue->store_message();
            }
            
        }

        if($payload_exists) {

            $virtual_tour->build_vt_data_array_base();
            $virtual_tour->add_slide();
            $dialogue->set_data_array($virtual_tour->get_data());
        }



        $dialogue->set_options();
        $dialogue->set_context();
        $dialogue->send_to_fb();
        
          
}

?>