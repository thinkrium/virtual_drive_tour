	var scene = new THREE.Scene();
	var camera = new THREE.PerspectiveCamera( 75, window.innerWidth/window.innerHeight, 0.1, 1000 );

	var renderer = new THREE.WebGLRenderer({ alpha : true , antialias: true });
	renderer.setSize( window.innerWidth, window.innerHeight );

        renderer.domElement.id  = "virtual_tour_canvas";
	document.body.appendChild( renderer.domElement );
			
			


        var texture = THREE.ImageUtils.loadTexture('test_images/photo_sphere.jpg');

        
	var geometry = new THREE.SphereGeometry( 10, 10, 10 );
	var material = new THREE.MeshBasicMaterial( { map: texture , side:THREE.BackSide } );
	var cube = new THREE.Mesh( geometry, material );
	scene.add( cube );

	camera.position.z = 5;

          
        var vtc = document.getElementById('virtual_tour_canvas');
          
        var switcher = document.getElementById('switcher');
            
                      
        var vid = document.getElementById('vid');

            
        vtc.addEventListener('mousemove', function(e) {
             if(e.which === 1) {
                cube.rotation.y = (Math.PI / 180 ) * e.clientX ;
                cube.rotation.x = (Math.PI / 180 ) * e.clientY ;
             }
         });

         vtc.addEventListener('touchmove', function(e) {
                
                cube.rotation.y = (Math.PI / 180 ) * e.touches[0].clientX;
                cube.rotation.x = (Math.PI / 180 ) * e.touches[0].clientY;
                
         });
            
         switcher.addEventListener('click', function(e) {
                if(e.target.id == 'video') {
                     vid.className = 'target';
                     vtc.className = '';
                }
                
		if(e.target.id == '3d') {
                     vtc.className = 'target';
                     vid.className = '';
                }
                 
         });
            
	 var animate = function () {
	       requestAnimationFrame( animate );

	       renderer.render( scene, camera );
	 };

            
	animate();
			
			
