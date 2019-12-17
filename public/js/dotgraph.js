function graphDatasetStatus(){
    $(".project").each(function(){
        var myDiv = $(this);
        var project = myDiv.attr("project");
        var divClass = '.project[project="' + project + '"] .dotchart';
        var datasetsAvailable = $(".dotTotal", myDiv).attr("total-available");
        var datasetsRegistered = myDiv.find(".dotTotal").attr("total-registered");
        var datasetsIdentified = myDiv.find(".dotTotal").attr("total-identified");
        if (typeof datasetsAvailable == 'undefined') {
            datasetsAvailable = 0;
        }
        if (typeof datasetsRegistered == 'undefined') {
            datasetsRegistered = 0;
        }
        if (typeof datasetsIdentified == 'undefined') {
            datasetsIdentified = 0;
        }
        var bitmapdatastring = '';
        for (var i=0; i<datasetsAvailable; i++) {
            bitmapdatastring += 'a';
        }
        for (var i=0; i<datasetsRegistered; i++) {
            bitmapdatastring += 'r';
        }
        for (var i=0; i<datasetsIdentified; i++) {
            bitmapdatastring += 'i';
        }
        createGraph(bitmapdatastring, divClass, "project", project, datasetsAvailable, datasetsRegistered, datasetsIdentified, myDiv);
    });
}

function createGraph(data, divClass, divSelAttr, divSelAttrVal, cntA, cntR, cntI, myDiv){
    $('.dotchart_legend #a', myDiv).append(" "+cntA);
    $('.dotchart_legend #r', myDiv).append(" "+cntR);
    $('.dotchart_legend #i', myDiv).append(" "+cntI);
    $('.dotchart_legend #tot', myDiv).append(" " + (+cntA + +cntR + +cntI) + "");
    var dots = 50; // 50 dots per row easy "at a glance" quantity indicator
    var rows = Math.ceil(data.length/dots);
    var mydiv = d3.select(divClass);
    var myFontSize = parseFloat(window.getComputedStyle(mydiv.node()).getPropertyValue('font-size'));
    var myFontColor = window.getComputedStyle(mydiv.node()).getPropertyValue('font-color');
    var strokeWidth = parseFloat(window.getComputedStyle(mydiv.node()).getPropertyValue('stroke-width'));
    var w = $(mydiv.node()).width();
    //var r = myFontSize/2; # this is perhaps a future option, but likely would require not having fixed number of dots per line
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
}
