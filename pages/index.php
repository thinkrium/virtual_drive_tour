<?php


   $root_path = implode('/', array_slice(explode('/', __FILE__), 0,6));
   // this variabel first explodes the existing file place
   // then removes the ending 2 elements
   // then recombines the array into a string with the / as the connector
   
   
   include "$root_path/pages/build/build.php";
   include "$root_path/includes/db.php";
   
   $vin = $_GET['vin'];
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Virtual Tour</title>
     <style>
         canvas, video {
             position: fixed;
             left: 0;
             top: 0;
             margin: 0;
             padding: 0;
             z-index; -10;
         }
         
         canvas.target {
             
             z-index: 10;
         }

         video.target {
             
             z-index: 10;
         }
         
         video {
             object-fit: cover; 
             width: 100vw; 
             height: 100vh; 
         }
         
         #switcher {
             
             position: fixed;
             top: 20px;
             left: 20px;
             z-index: 9999;
             background: red;
             padding: 20px;
         }
         
         #switcher  a {
             
             display: block;
             color: white;
             font-size: 20px;
             text-decoration: none;
         }         
         
     </style>
     
     <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body style="padding: 0; margin: 0;">
        
<video id="vid" controls >
    <source src="<?php print $vin?>/video.mp4" type="video/mp4"/>
</video>

    <div id="switcher">
        <a id="3d" href="#">3d</a>
        <a id="video" class="target" href="#">Video</a>
    </div>
    <script src="scripts/three.min.js"></script>
    <script src="scripts/vt.js"></script>
    </body>
</html>