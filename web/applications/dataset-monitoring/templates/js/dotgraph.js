function graphDatasetStatus(divClass){
    $(divClass).each(function(){
        myDiv = $(this);
        var project = myDiv.attr("project");
        $.getJSON( "https://proteus.tamucc.edu/pelagos/dev/mwilliamson/applications/dataset-monitoring/summaryCount/" + project, function( data ) {
            var projectBack = data[1];
            var rawJSON = data[0];
            var datasetsAvailable = rawJSON[0].data[0][0];
            var datasetsRegistered = rawJSON[1].data[0][0];
            var datasetsIdentified = rawJSON[2].data[0][0];

            var bitmapdatastring = '';
            
            for (var i=0; i<datasetsAvailable; i++) {
                bitmapdatastring += 'a';
            }
            for (var i=0; i<datasetsRegistered; i++) {
                bitmapdatastring += 'r';
            }
            for (var i=0; i<datasetsRegistered; i++) {
                bitmapdatastring += 'i';
            }
            createGraph(bitmapdatastring,divClass,"project",projectBack);
        });
    });
}

function createGraph(data,divClass,divSelAttr,divSelAttrVal){
   
    var mydiv = d3.selectAll(divClass).filter("["+divSelAttr+"="+"'"+divSelAttrVal+"']");
    
    var w = $(mydiv.node()).width();
    var h = $(mydiv.node()).height();

    var margin = {
        top: h * .2,
        bottom: h * .2,
        left: h * .2,
        right: h * .2
    };

    var width = w - margin.left - margin.right;
    var height = h - margin.top - margin.bottom;
    var dots = 50;
    var r = width/(dots*2);

    var x = d3.scale.linear()
            .domain([0,dots])
            .range([0,width]);
    var svg = mydiv.append("svg")
                .attr("id", "chart")
                .attr("width", w)
                .attr("height", h);
    console.log(svg);
    var chart = svg.append("g")
                .classed("display", true)
                .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
	
    chart.selectAll(".bar")
		.data(data)
		.enter()
			.append("circle")
			.classed("bar", true)
			.attr("class", function(d,i){ return "circleColor_" + data[i]; })
			.attr("cx", function(d,i){
                return x((i%dots));
            })
			.attr("cy", function(d,i){
                return (Math.floor(i/dots)*(2*r+r/2));
            })
			.attr("r", r)
}
