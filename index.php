<!DOCTYPE html>
<html>
    <head>
            <script type="text/javascript" src="js/jquery-2.1.0.js"></script>
           
        <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
                <style>
                    table,th,td
                    {
                        border:1px solid black;
                        border-collapse:collapse;
                    }
                    th,td
                    {
                        padding:5px;
                    }
                #info {
                        color: #bfd1e5;
                        position: absolute;
                        top: 10px;
                        width: 100%;
                        text-align: center;
                        z-index: 100;
                        display:block;
                    }
                    #info a, .button { color: #f00; font-weight: bold; text-decoration: underline; cursor: pointer }
                    </style>
        
    </head>
    <body >
        <script src="build/three.min.js"></script>
	<script src="js/controls/TrackballControls.js"></script>
	<script src="js/loaders/VTKLoader.js"></script>
	<script src="js/Detector.js"></script>
	<script src="js/libs/stats.min.js"></script>
     	<script src="js/controls/FirstPersonControls.js"></script>
        <script src='js/libs/dat.gui.min.js'></script>
	<script src="js/ImprovedNoise.js"></script>
        <script>
            if ( ! Detector.webgl ) Detector.addGetWebGLMessage();
            
	    var container, stats;
            var camera, controls, scene, renderer;
            var gui;
	    var cross;
	    var mouseX = 0, mouseY = 0;
            var screen, header;
            var edges_obj=[];
            var edges=[];
            var buildings=[];
            var edge_size=0;
            var nodes=[];
            var nodes_obj=[];
            var node_size=0;
            var floor=1;
			//var windowHalfX = window.innerWidth / 2;
			//var windowHalfY = window.innerHeight / 2;
            var cameraPosition={
                X:6000,
                Y:10000,
                Z:6000
            };
	    var mesh, texture;
            var deg=0;
	    var worldWidth = 256, worldDepth = 256,
		worldHalfWidth = worldWidth / 2, worldHalfDepth = worldDepth / 2;  
	    var clock = new THREE.Clock();
            var ter;
            
	    //Set up 
	    createscreen();
            
//	    animate();
        
	    function createscreen()
            {
	    	//Main screen
            	screen = document.createElement( 'div' );
            	document.body.appendChild( screen );
            	screen.id="screen";
            	screen.style= "width:"+(window.innerWidth-20)+"px";
            
	    	//Header
            	header= document.createElement('div');
	        header.style="background-color:#FFA500;";
            	header.innerHTML='<h1 style="margin-bottom:0;">NIJ Project</h1></div>';
            	screen.appendChild(header);
            
	    	//Content
	    	var content=document.createElement('div');
            	content.style="background-color:#EEEEEE;width:300px;float:left;";
            	content.innerHTML='<div id="content" style="background-color:#EEEEEE; width:200px; float:left;"></div>';
            
		    //Database connection buttons
		    var basic= document.createElement('div');
            	    basic.innerHTML='<input type="image" src="logo/DB.png" onclick="DB();" id="Db" width="80" height="80" />';
		    basic.innerHTML+='<input type="image" src="logo/building.gif" onclick="building("buil");" id="Building" width="80" height="80"/><br>';
            	    content.appendChild(basic);
            
		    //Building picker
		    var building = document.createElement('div');;
            	    building.innerHTML='<form><select id="building_list" onchange="building(this.value)"><option value="-1">Building List</option></select></form>';
            	    content.appendChild(building);
            
		    //Controls
		    var view=document.createElement('div');
            	    view.innerHTML='<input type="image" src="logo/Up.png" onclick="up();" id="up" width="80" height="80" disabled/><input type="image" src="logo/Down.png" onclick="down();" id="down" width="80" height="80" disabled/><input type="image" src="logo/2-D.png" onclick="two_d();" id="2-D" width="80" height="80"/><input type="image" src="logo/3-D.jpg" onclick="three_d();" id="3-D" width="80" height="80"/>';
            	    content.appendChild(view);
            
		    //Info table
            	    var responder=document.createElement('div');
            	    responder.innerHTML='<table style="width:200px"><tr><th>Name</th><th>Building Location</th><th>Room</th><th>Target Building</th><th>Room</th></tr><tr><th>hh</th><th>wdward</th><th>437</th><th>wdward</th><th>214</th></tr><tr></table>';
            	    content.appendChild(responder);
            
	    	screen.appendChild(content);
            
	    	//Info div
	    	container=document.createElement('div');
            	container.innerHTML='<div id="info"></div>';
            	screen.appendChild(container);
            }
            

	    //Populate the dropdown list with building values from database
            function DB() {
                //alert("DB");
                if(buildings.length == 0){
                $.ajax({
                    type: "POST",
                    url: "php/getBuildings.php",
                    success: function(data){
                    	var json = JSON.parse(data);
                    	var select= document.getElementById("building_list");
		    	//alert(json.length);
			
		    	//set an option for each building found
                    	for(var i = 0; i < json.length; i++){                        
			    var option=document.createElement('option');
                            
			    var tembuild={	
                            	ID:0,
                            	Name:''};
                            tembuild.ID=json[i]["id"];
                            tembuild.Name=json[i]["name"];
                            buildings.push(tembuild);	//Add building to the array
                            
			    option.value=json[i]["id"];
                            option.innerHTML=json[i]["name"];
                            select.appendChild(option);
                    	}
                    },
		    error: function() {
		    	alert("Error connecting to database");
		    }	
            	});
                }
            } 	// DB()

	    //Populate the edge[] and node[] arrays for the current building
            function building(value) {
                var formData = "id="+value;	//building id
                
		//Get Edge data
		$.ajax({
                    type: "POST",
                    url: "php/getEdges.php",
                    data: formData,
                    success: function(data){
                    	var json = JSON.parse(data);
                    	edges.length=0;
                    	
			//Add each edge to the edges array
			for(var i = 0; i < json.length; i++){
                            var temedge={
                                ID:0,
                            	SX:0,
                            	SY:0,
                            	SZ:0,
                            	EX:0,
                            	EY:0,
                            	EZ:0,
                            	floor:0,
                            	type:''
                            };
                            temedge.type=json[i]["type"];
                            temedge.SX=json[i]["start_x"];
                            temedge.SY=json[i]["start_y"];
                            temedge.SZ=json[i]["start_z"];
                            temedge.EX=json[i]["end_x"];
                            temedge.EY=json[i]["end_y"];
                            temedge.EZ=json[i]["end_z"];
                            temedge.ID=json[i]["id"];
                            temedge.floor=json[i]["floor"];
                            edges.push(temedge);
                            edge_size++;
                    	}
                    }
            	});
                
		//Get Node data
		$.ajax({
                    type: "POST",
                    url: "php/getNodes.php",
                    data: formData,
                    success: function(data){
                    	var json = JSON.parse(data);
                    	nodes.length=0;
                    
			//Add each node to the nodes array
			for(var i = 0; i < json.length; i++){
                            var temnode={
                            	ID:0,
                            	X:0,
	                        Y:0,
                            	Z:0,
                            	type:0,
                            	floor:0,
                            	width:0,
                            	height:0
                            };
                        
			    temnode.ID=json[i]["id"];
                            temnode.X=json[i]["X"];
                            temnode.Y=json[i]["Y"];
                            temnode.Z=json[i]["Z"];
                            temnode.type=json[i]["type"];
                            temnode.floor=json[i]["floor"];
                            temnode.width=json[i]["width"];
                            temnode.height=json[i]["height"];
                            nodes.push(temnode);
                            node_size++;
                    	}
                    }
            	});
            }	// building()


	    //2D view
            function two_d(){
                floor=1;
                graph();
                document.getElementById("up").disabled=false;
                document.getElementById("down").disabled=false;
            }

	    //Go up a floor
            function up()
            {
                floor=floor+1;
                graph();

            }
	
	    //Go down a floor
            function down()
            {
                if (floor>1)
                    floor=floor-1;
                graph();
            }
            
	    //3D view
            function three_d()
            {
                //bunnyClick();
                floor=0;
                graph();
                document.getElementById("up").disabled=true;
                document.getElementById("down").disabled=true;  
            }
           
	    // 
            function graph(){
		//remove all children of container div
            	while (container.firstChild) {
                    container.removeChild(container.firstChild);
                }
                ter=0;

                var min_x=5000000.;
                var min_y=5000000.;
                var max_x=0.;
                var max_y=0.;

		//Adjust values for min and max {x,y} values
                for(var i = 0; i < nodes.length; i++){
                    if (nodes[i].X < min_x) min_x = nodes[i].X;
                    if (nodes[i].Y < min_y) min_y = nodes[i].Y;
                    if (nodes[i].X > max_x) max_x = nodes[i].X;
                    if (nodes[i].Y > max_y) max_y = nodes[i].Y;
                }

		//Normalize nodes
                for(var i = 0; i < nodes.length; i++){
                    nodes[i].X=nodes[i].X-min_x;
                    nodes[i].Y=nodes[i].Y-min_y;
                }

		//Normalize edges
                for(var i = 0; i < edges.length; i++){
                    edges[i].SX=edges[i].SX-min_x;
                    edges[i].SY=edges[i].SY-min_y;
                    edges[i].EX=edges[i].EX-min_x;
                    edges[i].EY=edges[i].EY-min_y;
                }

		//Normalize boundaries
                max_x=max_x-min_x;
                max_y=max_y-min_y;
                min_x=0;
                min_y=0;

		//Set up 3.js camera
                camera = new THREE.PerspectiveCamera( 60, (window.innerWidth-220) / (window.innerHeight-80), 0.01, 1e10 );
		camera.position.z = -4000;
                //camera.position.z = Math.pow(Math.pow((max_x-min_x),2)+Math.pow((destination.Y-camera.position.y),2)+Math.pow((destination.Z-camera.position.z),2),.5);
		camera.position.x = (max_x)/2;
		camera.position.y = (max_y)/2;
                
	  	//vector??	
		var lkat=new THREE.Vector3((max_x)/2,(max_y)/2,0);
               
                //alert(camera.lookAt.x);
		//camera.lookAt(new THREE.Vector3((min_x+max_x)/2,(min_y+max_y)/2,0));
               
		//zoom/pan/rotate controls 
		controls = new THREE.TrackballControls( camera );
                
		//Set up controls 
		if(floor!=0)	// 2D
		    controls.rotateSpeed = 0.0;
		else 		// 3D
		    controls.rotateSpeed = 0.5;
		
		controls.zoomSpeed = 1.5;
		controls.panSpeed = 1.2;
                controls.noZoom = false;
		controls.noPan = false;
		controls.staticMoving = true;
		controls.dynamicDampingFactor = 0.3;
                controls.target=lkat;

		//Set up scene
		scene = new THREE.Scene();
		scene.add( camera );
		
		//Set up light
                var dirLight = new THREE.DirectionalLight( 0xffffff );
		dirLight.position.set( 200, 200, 1000 ).normalize();
                
		//camera.add( dirLight );
		//camera.add( dirLight.target );

		// var material = new THREE.LineBasicMaterial({color: 0x6699FF, linewidth: 5, fog:true});
    		// var lineg = new THREE.Geometry();
               
		//Display each edge 
                for(var i = 0; i < edges.length; i++){	
		    //Only display edges relevant to current floor/view
                    if(floor == edges[i].floor || floor==0) {
                        if (edges[i].type=="Hallway")
			    var material = new THREE.LineBasicMaterial({color: 0x0000FF, linewidth: 25, fog:true});
                        else 
			    var material = new THREE.LineBasicMaterial({color: 0x999966, linewidth: 2, fog:true});
                	
			//Create geometry with {x,y,z} of start end end points of line
			var lineg = new THREE.Geometry();
                	lineg.vertices.push(new THREE.Vector3(edges[i].SX, edges[i].SY, edges[i].SZ));
                	lineg.vertices.push(new THREE.Vector3(edges[i].EX, edges[i].EY, edges[i].EZ));

			//Create a line based on the geometry and material
                	var line = new THREE.Line(lineg, material);
                	scene.add(line);
                	edges_obj.push(line);
            	    }
		}

		//Display each node
		for(var i = 0; i < nodes.length; i++){
		    //Only display nodes relevant to current floor/view
                    if (floor == nodes[i].floor || floor==0) {
                        //Assign geometry based on node type
			//	NOTE: CubeGeometry(width,height,depth,...) deprecated.  
			if (nodes[i].type == 'Hallway')
			    var geometry = new THREE.CubeGeometry( 1, 1, 0);
                        else if(nodes[i].type == 'Room')
			    var geometry = new THREE.CubeGeometry( nodes[i].width, nodes[i].height, 0);
                        else   	
			    var geometry = new THREE.CubeGeometry(15, 15, 0);

			//Set node material based on node type
                	switch(nodes[i].type) {
                	    case "Hallway":
                	    	var material = new THREE.MeshBasicMaterial( {color: 0x3333ff} );
                		break;
                	    case "Room":
                        	//var cubeTexture = THREE.ImageUtils.loadTexture('logo/room.jpg');
	                        //var material = new THREE.MeshBasicMaterial({map: cubeTexture});
                     		var material = new THREE.MeshBasicMaterial( {color: 0xffff33} );
                		break;
                	    case "Elevator":
                		var material = new THREE.MeshBasicMaterial( {color: 0xffff00} );
                		break;
                	    case "Stair":
                		var material = new THREE.MeshBasicMaterial( {color: 0xff0000} );
                		break;
                	    default:
                		var material = new THREE.MeshBasicMaterial( {color: 0xfff20} );
			}
                	
			//var material = new THREE.MeshBasicMaterial( {color: 0xff0} );
                    	//var sphere = new THREE.Mesh(new THREE.SphereGeometry(15, 15, 15), material);
                	
			//Set position of node
			var sphere = new THREE.Mesh( geometry, material );
                	if(nodes[i].type == 'Room'){
                    	    sphere.position.x=nodes[i].X+((nodes[i].width)/2)-25.0;
                	    sphere.position.y=nodes[i].Y+((nodes[i].height)/2)-37.5;
                	    sphere.position.z=nodes[i].Z;
	                } else {
                            sphere.position.x=nodes[i].X;
                    	    sphere.position.y=nodes[i].Y;
                    	    sphere.position.z=nodes[i].Z;
                        }

			//Add node to scene
                    	scene.add( sphere );
                    	nodes_obj.push(sphere);
                    }
		} //for

		//Set camera 
                camera.lookAt(scene.position);	

		//Set up renderer
		renderer = new THREE.WebGLRenderer( { antialias: false } );
		renderer.setSize( (window.innerWidth-320), (window.innerHeight-80) );
                renderer.setClearColor(0xcccccc, 1);
                //elid="renderer";
                //renderer.id=elid;
                //screen.appendChild( container );
		
		//Add renderer to page
		container.appendChild( renderer.domElement );
               
		//Framerate stats 
		stats = new Stats();
		stats.domElement.style.position = 'absolute';
		stats.domElement.style.top = '0px';
		container.appendChild( stats.domElement );
                
		//
                renderer.domElement.addEventListener( 'mousedown', onDocumentMouseDown, false );
		window.addEventListener( 'resize', onWindowResize, false );

		animate();
            } // Graph()
           
	    //Handle clicks inside renderer element
	    // ???
            function onDocumentMouseDown( event ) {
                    var mouseX = ( event.clientX / window.innerWidth ) * 2 - 1;
                    var mouseY = -( event.clientY / window.innerHeight ) * 2 + 1;
                    
                    var vector = new THREE.Vector3( mouseX, mouseY, camera.near );
                    
                    // Convert the [-1, 1] screen coordinate into a world coordinate on the near plane
                    var projector = new THREE.Projector();
                    projector.unprojectVector( vector, camera );
                    
                    var raycaster = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );
                    
                    // See if the ray from the camera into the world hits one of our meshes
                    var intersects = raycaster.intersectObject( nodes_obj);
                    //lastIntersects = intersects;
                    
                    // Toggle rotation bool for meshes that we clicked
                    if ( intersects.length > 0 ) {
                        alert(intersects[0].position.x);                     
                    }
            }
            
	    // Render loop
            function render() {
                //deg+=.005;
                //camera.position.y=cameraPosition.Y;
                //camera.position.x=cameraPosition.X*Math.cos(deg);
                //camera.position.z=cameraPosition.Z*Math.sin(deg);
                //camera.lookAt(new THREE.Vector3( 0, 0, 0 ));
		//controls.update( clock.getDelta() );
		
		renderer.render( scene, camera );    
	    }
            
	    // Window resize function
            function onWindowResize() {
                content.style="background-color:#EEEEEE; height:"+(window.innerHeight-80)+"px"+";width:300px;float:left;";
                screen.style= "width:"+(window.innerWidth-20)+"px";
                header.style= "background-color:#FFA500; "+"width:"+(window.innerWidth-20)+"px";
		camera.aspect = (window.innerWidth-320) / (window.innerHeight-80);
		camera.updateProjectionMatrix();
                renderer.setSize( (window.innerWidth-320), (window.innerHeight-80) );
                controls.handleResize();
            }
            
	    //Animation loop
     	    function animate() {
            	requestAnimationFrame( animate );
            	controls.update();
		renderer.render( scene, camera );
                if(ter==1)
		    render();
		stats.update();
            }

        </script>
	<script src="js/shaders/BokehShader2.js"></script>
   </body>
</html>
