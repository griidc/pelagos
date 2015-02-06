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
    var dots = 50; // 50 dots per row easy "at a glance" quantity indicator
    var rows = Math.ceil(data.length/dots);
    var mydiv = d3.selectAll(divClass).filter("["+divSelAttr+"="+"'"+divSelAttrVal+"']");
    var myFontSize = parseFloat(window.getComputedStyle(mydiv.node()).getPropertyValue('font-size'));
    var myFontColor = window.getComputedStyle(mydiv.node()).getPropertyValue('font-color');
    console.log(myFontColor);
    var strokeWidth = parseFloat(window.getComputedStyle(mydiv.node()).getPropertyValue('stroke-width'));
    var w = $(mydiv.node()).width();
    //var r = myFontSize/2;
    var r = w/dots*(1/2);
    var h = 2*r*rows;

    var x = d3.scale.linear()
            .domain([0,dots])
            .range([0,w]);
    var svg = mydiv.append("svg")
                .attr("id", "chart")
                .attr("width", w)
                .attr("height", h);
    var chart = svg.append("g")
                .classed("display", true)
                .attr("transform", "translate(" + r + "," + r + ")");
    chart.selectAll(divClass)
		.data(data)
		.enter()
			.append("circle")
			.attr("class", function(d,i){ return "circleColor_" + data[i]; })
			.attr("cx", function(d,i){
                return x((i%dots));
            })
			.attr("cy", function(d,i){
                return (Math.floor(i/dots)*(2*r));
            })
			.attr("r", r-strokeWidth);
    // Create Legend
    $(divClass+"_legend").html("<svg height=\"" + h + "\" id=\"chart_legend\"><circle class=\"circleColor_a\" cx=\"" + (r+10-strokeWidth) + "\" cy=\"" + r + "\" r=\"" + (r-strokeWidth) + "\"></circle><text x=\"" + (2*r+15-strokeWidth) + "\" y=\"" + 1.5*r + "\" font-size=\"" + myFontSize*(.5) + "px\" font-weight=\"50\">Available</text><circle class=\"circleColor_r\" cx=\"" + (r+10-strokeWidth) + "\" cy=\"" + 3*r + "\" r=\"" + (r-strokeWidth) + "\"></circle><text x=\"" + (2*r+15-strokeWidth) + "\" y=\"" + 3.5*r + "\" font-size=\"" + myFontSize*(.5) + "px\">Registered</text><circle class=\"circleColor_i\" cx=\"" + (r+10-strokeWidth) + "\" cy=\"" + 5*r + "\" r=\"" + (r-strokeWidth) + "\"></circle><text x=\"" + (2*r+15-strokeWidth) + "\" y=\"" + 5.5*r + "\" font-size=\"" + myFontSize*(.5) + "px\">Identified</text></svg>");

}
