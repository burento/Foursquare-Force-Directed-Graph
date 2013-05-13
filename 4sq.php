<!DOCTYPE html>
<html>
<head>
	<title>Foursquare Visualization</title>
	<meta charset="utf-8">
	<style>

		.node {
		  stroke-width: 1.8px;
		  fill: #000;
		}

		.node.travel, .legend.travel{ fill: #A6CEE3; /* light blue */}
		.node.air-travel, .legend.air-travel{ fill: #1F78B4; /* dark blue */ }
		.node.coffee, .legend.coffee{ fill: #B2DF8A; /* light green */}
		.node.restaurant, .legend.restaurant{ fill: #33A02C; /* dark green */ }
		.node.bar, .legend.bar{ fill: #FB9A99; /* pink */ }
		.node.leisure, .legend.leisure{ fill: #E31A1C; /* red */}
		.node.entertainment, .legend.entertainment{ fill: #FDBF6F; /* light orange */}
		.node.store, .legend.store{ fill: #FF7F00; /* dark orange*/ }
		.node.site, .legend.site{ fill: #CAB2D6; /* light purple */}
		.node.temple, .legend.temple{ fill: #6A3D9A; /* dark purple */}
		.node.fitness, .legend.fitness{fill: #FFFF99; /* yellow */}
		.node.home-work, .legend.home-work{fill: #8DD3C7; /* teal */}

		.legendCountry{ fill: #FFF; }
		.HK{ stroke: #000; }
		.US{ stroke: #3C3; }
		.TH{ stroke: #C33; }
		.TW{ stroke: #33C; }
		.MO{ stroke: #CCC; }

		.link {
		  stroke: #999;
		  stroke-opacity: .6;
		}

		.legend{
			font-size:14px;
		}
		div.ui-datepicker{
		 	font-size:12px;
		}

	</style>
	<link rel="stylesheet" href="jquery-ui-1.10.2.custom/css/ui-lightness/jquery-ui-1.10.2.custom.min.css" />
	<script type="text/javascript" src="http://d3js.org/d3.v3.min.js"></script>
	<script type="text/javascript" src="http://code.jquery.com/jquery.min.js"></script>
	<script type="text/javascript" src="jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
	<?php 
		include "4sq-json.php"; 
		date_default_timezone_set('America/Los_Angeles');
		
		$sd = "2012-12-01"; $ed = "2013-01-18"; 

		$start = strtotime($sd); $end = strtotime($ed); 
		createForceGraphJson($start, $end);
	?>
	<script>
	  $(function() {
		    $( "#from" ).datepicker({
		      defaultDate: "+1w",
		      changeMonth: true,
		      numberOfMonths: 3,
		      dateFormat: "yy-mm-dd",
		      onClose: function( selectedDate ) {
		        $( "#to" ).datepicker( "option", "minDate", selectedDate );
		      }
		    });
		    
		    var queryDate = <?php echo '"' . $sd . '"'?>,
			    dateParts = queryDate.match(/(\d+)/g),
			    realDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);  
		    $( "#from" ).datepicker('setDate', realDate );
		    
		    $( "#to" ).datepicker({
		      defaultDate: "+1w",
		      changeMonth: true,
		      numberOfMonths: 3,
		      dateFormat: "yy-mm-dd",
		      onClose: function( selectedDate ) {
		        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
		      }
		    });

		    queryDate = <?php echo '"' . $ed . '"'?>;
			dateParts = queryDate.match(/(\d+)/g);
			realDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);  
		    $( "#to" ).datepicker('setDate', realDate );

		  });
	  </script>
</head>
<body>
	<label for="from">From</label>
	<input type="text" id="from" name="from" />
	<label for="to">to</label>
	<input type="text" id="to" name="to" />
	
	<script>

	var width = 1200,
	    height = 650;

	var force = d3.layout.force()
	    .charge(-80)
	    .linkDistance(20)
	    .size([width, height]);

	var svg = d3.select("body").append("svg")
	    .attr("width", width)
	    .attr("height", height);

	var graph;

	d3.json("4sq.json", function(error, g) {
	  	graph = g; 

	  	force
	      .nodes(graph.nodes)
	      .links(graph.links)
	      .start();

	  	var scale = d3.scale.linear()
	  		.domain([1,d3.max(graph.nodes, function(d){return d.size;})])
	  		.range([4.5,8.5]);

	  	var link = svg.selectAll(".link")
	      .data(graph.links)
	      .enter().append("line")
	      .attr("class", "link")
	      .style("stroke-width", function(d) { return Math.sqrt(d.value); });

	 
	  var node = svg.selectAll(".node")
	      .data(graph.nodes)
	      .enter().append("circle")
	      .attr("class", function(d){return "node " + d.topcat + " " + d.cc; })
	      .attr("r", function(d){return scale(d.size); } )
	      .call(force.drag);

	  node.append("title")
	      .text(function(d) { return d.name + "\n\nCategory: " + d.category; });


	  force.on("tick", function() {
	    link.attr("x1", function(d) { return d.source.x; })
	        .attr("y1", function(d) { return d.source.y; })
	        .attr("x2", function(d) { return d.target.x; })
	        .attr("y2", function(d) { return d.target.y; });

	    node.attr("cx", function(d) { return d.x; })
	        .attr("cy", function(d) { return d.y; });
	  });
	});

	var legendData = [
		["travel", "Travel"]
		, ["air-travel", "Air travel"]
		, ["restaurant", "Restaurants"]
		, ["coffee", "Coffee / Tea"]
		, ["bar", "Bars / Lounges"]
		, ["store", "Shopping"]
		, ["temple", "Temples / Shrines"]
		, ["site", "Buildings and Sites"]
		, ["leisure", "Hotels / Spas / Salon"]
		, ["entertainment", "Entertainment"]
		, ["home-work", "Home / Work"]
		, ["fitness", "Gym / Fitness / Hiking"]
		];

	var legendCC = [
		[ "US", "United States" ]
		, [ "HK", "Hong Kong" ]
		, [ "MO", "Macau" ]
		, [ "TH", "Thailand" ]
		, [ "TW", "Taiwan" ]
		]

	var legend = svg.append("g")
	  .attr("class", "legend")
    
    legend.selectAll('rect')
      .data(legendData)
      .enter()
      .append("rect")
	  .attr("x", 10)
      .attr("y", function(d, i){ return (i+1)*20;})
	  .attr("width", 10)
	  .attr("height", 10)
	  .attr("class", function(d){ return "legend " + d[0]; })
      
    legend.selectAll('text')
      .data(legendData)
      .enter()
      .append("text")
	  .attr("x", 25)
      .attr("y", function(d, i){ return (i+1)*20 + 10;})
	  .text(function(d) { return d[1]; });

	var legendCountry = svg.append("g")
		.attr("class", "legend")

	legendCountry.selectAll('rect')
      .data(legendCC)
      .enter()
      .append("rect")
	  .attr("x", 10)
      .attr("y", function(d, i){ return ((i+1)*20)+300;})
	  .attr("width", 10)
	  .attr("height", 10)
	  .attr("class", function(d){ return "legend " + d[0] + " legendCountry"; })
      
    legendCountry.selectAll('text')
      .data(legendCC)
      .enter()
      .append("text")
	  .attr("x", 25)
      .attr("y", function(d, i){ return ((i+1)*20 + 10)+300;})
	  .text(function(d) { return d[1]; });


	</script>
</body>
</html>