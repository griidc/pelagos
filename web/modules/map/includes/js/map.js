<script type="text/javascript">
//<![CDATA[


var drawnShapes = [],  markers = [], midmarkers = [],  polyPoints = [], pointsArray = [], markersArray = [];
var addresssArray = [], pointsArrayKml = [], markersArrayKml = [], outerPoints = [],  placemarks = [], innerArray = [];
var polylinestyles = [], polygonstyles = [],  markerstyles = [], outerArray = [], innerArrays = [], outerArrayKml = [], innerArraysKml = [];
var map, polyShape, markerShape, outerShape, geocoder, startMarker, nemarker, tinyMarker, centerPoint, radiusPoint, calc, startpoint, prevpoint, prevnumber;
var text_for_box = ""; 
var adder = 0, shapeId = 0, plmcur = 0, lcur = 0, pcur = 0, ccur = 0, mcur = 0, firstdirclick = 0;
var dirmarknum = 1, codeID = 1, toolID = 5;
var notext = false;
var polylineDecColorCur = "255,0,0", polygonDecColorCur = "255,0,0";
var infowindow = new google.maps.InfoWindow();

var tmpPolyLine = new google.maps.Polyline({
    strokeColor: "#00FF00",
    strokeOpacity: 0.8,
    strokeWeight: 2
});

var tinyIcon = new google.maps.MarkerImage(
    'images/red-dot.png',
    new google.maps.Size(60,60),
    new google.maps.Point(0,0),
    new google.maps.Point(12,32)
);
function gob(e){//DONE
if(typeof(e)=='object')return(e);if(document.getElementById)return(document.getElementById(e));return(eval(e))}
function initmap(){ //DONE
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(25.329732, -90.913127);
    var mapTypeIds = [];
    for(var type in google.maps.MapTypeId) { mapTypeIds.push(google.maps.MapTypeId[type]); }
    mapTypeIds.push("OSM");
    var myOptions = {
        zoom: 6,
        center: latlng,
        draggableCursor: 'default',
        draggingCursor: 'pointer',
        scaleControl: true,
        mapTypeControl: true,
        //mapTypeControlOptions: {mapTypeIds: mapTypeIds},
        mapTypeControlOptions:{style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        styles: [{featureType: 'poi', stylers: [{visibility: 'off'}]}],
        streetViewControl: false};
    map = new google.maps.Map(gob('map_canvas'),myOptions);
    map.mapTypes.set("OSM", new google.maps.ImageMapType({
        getTileUrl: function(coord, zoom) {
            //return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
        },
        tileSize: new google.maps.Size(256, 256),
        name: "OpenStreetMap",
        maxZoom: 18
    }));
    polyPoints = new google.maps.MVCArray(); 
    tmpPolyLine.setMap(map);
    createplacemarkobject();
    createlinestyleobject();
    createpolygonstyleobject();
    createmarkerstyleobject();
    preparePolyline(); 
    google.maps.event.addListener(map, 'click', addLatLng);
    google.maps.event.addListener(map,'zoom_changed',mapzoom);
    cursorposition(map);
}
function mapzoom(){//DONE
    var mapZoom = map.getZoom();
    if (mapZoom < 10){mapZoom="0"+map.getZoom();}
    gob("myzoom").value = mapZoom;
}
function showAddress(address) {//DONE
    geocoder.geocode({'address': address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var pos = results[0].geometry.location;
            map.setCenter(pos);
            //tinyMarker = new google.maps.Marker({
             //  position: pos,
			//zoom: 6,
             //   map: map,
                //icon: tinyIcon
            //});
            //drawnShapes.push(tinyMarker);
            //if(toolID == 5) drawMarkers(pos);
        } else {alert("Request Not Found. Please check ");}
    });
}
function linestyle() {//DONE
  this.color = "#FF0000";
   this.width = 3;
   this.lineopac = 1;
}
function markerstyleobject() {//DONE
    //this.name = "markerstyle";
    //this.icon = "images/red-dot.png";
}
function polystyle() {//DONE
    this.name = "Lump";
    this.kmlcolor = "CD0000FF";
    this.kmlfill = "9AFF0000";
    this.color = "#FF0000";
    this.fill = "#0000FF";
    this.width = 2;
    this.lineopac = 0.8;
    this.fillopac = 0.6;
}
function cursorposition(mapregion){//DONE
    google.maps.event.addListener(mapregion,'mousemove',function(point){
        var LnglatStr6 = point.latLng.lng().toFixed(6) + ', ' + point.latLng.lat().toFixed(6);
        var latLngStr6 = point.latLng.lat().toFixed(6) + ', ' + point.latLng.lng().toFixed(6);
        gob('over').options[0].text = LnglatStr6;
        gob('over').options[1].text = latLngStr6;
    });
}
function code_2(){//POLYGON TEXT
    if (notext === true) return;
    var code = "";
    if(pointsArrayKml.length != 0) {
        for(var i = 0; i < pointsArrayKml.length; i++) {
            code += pointsArrayKml[i]+' ';
        }
        code += pointsArrayKml[0]+'';
        placemarks[plmcur].jscode = pointsArray;
        placemarks[plmcur].text_for_box = pointsArrayKml;
    }
    placemarks[plmcur].plmtext = text_for_box = code;
    placemarks[plmcur].poly = "pg";
    gob('coords1').value = code;
}
function logCode9() { //MARKER TEXT
    if(notext === true) return;
    gob('coords1').value = "";
    var markers_for_box = "";
    placemarks[plmcur].plmtext = text_for_box = markers_for_box += placemarks[plmcur].text_for_box;
    gob('coords1').value = markers_for_box;
}
function showCodeintextarea(){ //SHOW TEXT
        notext = false;
        if(polyPoints.length > 0){
            //if(toolID==1) { if(codeID==1) code_1();}
            if(toolID==2) { if(codeID==1) code_2();}
            if(toolID==5) {if(codeID == 1) logCode9();}

        }
}
function nextshape() {//CLEAR OR NEXT SHAPE ONE OR THE OTHER
    if(plmcur < placemarks.length -1) {
        addpolyShapelistener();
        plmcur = placemarks.length -1;
    }
    //Set listener on current shape. Create new placemark object.
    //Increase counter for placemark
    increaseplmcur();
    if(polyShape) drawnShapes.push(polyShape); // used in clearMap, to have it removed from the map, drawnShapes[i].setMap(null)
    if(outerShape) drawnShapes.push(outerShape);
    if(tinyMarker) drawnShapes.push(tinyMarker);
    polyShape = null;
    outerShape = null;
    markerShape = null;
    newstart();
}
function increaseplmcur() {
    if(placemarks[plmcur].plmtext != "") {
        if(toolID==1 || toolID==2 && directionsYes == 0) {
            placemarks[plmcur].shape = polyShape;
            addpolyShapelistener();
            createplacemarkobject();
            plmcur = placemarks.length -1;
        }
        if(markerShape && directionsYes == 0) {
            placemarks[plmcur].shape = markerShape;
            createplacemarkobject();
            plmcur = placemarks.length -1;
        }
        if(toolID==3 && directionsYes == 0) {
            placemarks[plmcur].shape = rectangle;
            addpolyShapelistener();
            createplacemarkobject();
            plmcur = placemarks.length -1;
        }
        //Set a listener on the directions line (path)
        if(directionsYes == 1) {
            placemarks[plmcur].shape = polyShape;
            plmcur = dirline;
            addpolyShapelistener();
            createplacemarkobject();
            plmcur = placemarks.length -1;
        }
    }
}
function clearMap(){
    iif(!mapOverlays.isEmpty()) 
     { 
     mapOverlays.clear(); 
     mapView.invalidate();

 }
    if(polyShape) polyShape.setMap(null); // polyline or polygon
    if(outerShape) outerShape.setMap(null);

    if(drawnShapes.length > 0) {
        for(var i = 0; i < drawnShapes.length; i++) {
            drawnShapes[i].setMap(null);
        }
    }
    plmcur = 0;
    dirmarknum = 1;
    newstart();
    placemarks = [];
    createplacemarkobject();
}
function placemarkobject() {
    this.name = "Marker";
    this.style = "Path";
    this.stylecur = 0;
    this.tess = 1;
    this.alt = "clampToGround";
    this.plmtext = "";
    this.text_for_box = [];
    this.poly = "pl";
    this.shape = null;
    this.point = null;
    this.toolID = 1;
    this.ID = 0;
}
function createplacemarkobject() {
    var thisplacemark = new placemarkobject();
    placemarks.push(thisplacemark);
}
function createpolygonstyleobject() {
    var polygonstyle = new polystyle();
    polygonstyles.push(polygonstyle);
}
function createlinestyleobject() {
    var polylinestyle = new linestyle();
    polylinestyles.push(polylinestyle);
}
function createmarkerstyleobject() {
    var thisstyle = new markerstyleobject();
    markerstyles.push(thisstyle);
}
function preparePolyline(){
    var polyOptions = {
        path: polyPoints,
        strokeColor: polylinestyles[lcur].color,
        strokeOpacity: polylinestyles[lcur].lineopac,
        strokeWeight: polylinestyles[lcur].width};
    polyShape = new google.maps.Polyline(polyOptions);
    polyShape.setMap(map);
 
}
function preparePolygon(){




    var polyOptions = {
        path: polyPoints,
		
        strokeColor: polygonstyles[pcur].color,
        strokeOpacity: polygonstyles[pcur].lineopac,
        strokeWeight: polygonstyles[pcur].width,
        fillColor: polygonstyles[pcur].fill,
        fillOpacity: polygonstyles[pcur].fillopac};
        polyShape = new google.maps.Polygon(polyOptions);
    polyShape.setMap(map);
}
function activateMarker() {
    markerShape = new google.maps.Marker({
        //map: map,
        //icon: markerstyles[mcur].icon
    });
}
function addLatLng(point){
    if(plmcur != placemarks.length-1) {nextshape();}
    polyPoints = polyShape.getPath();
    polyPoints.insertAt(polyPoints.length, point.latLng);
    if(polyPoints.length == 1) {
        startpoint = point.latLng;
        placemarks[plmcur].point = startpoint; 
        setstartMarker(startpoint);
        if(toolID == 5) {drawMarkers(startpoint);}
    }

    if(toolID == 2) { 
        //var stringtobesaved = point.latLng.lat().toFixed(6) + ',' + point.latLng.lng().toFixed(6);
        //var kmlstringtobesaved = point.latLng.lng().toFixed(6) + ',' + point.latLng.lat().toFixed(6);
		var stringtobesaved = point.latLng.lng().toFixed(6) + ',' + point.latLng.lat().toFixed(6) ;
        var kmlstringtobesaved = point.latLng.lat().toFixed(6) + ',' + point.latLng.lng().toFixed(6);

        if(adder == 0) { 
            pointsArray.push(stringtobesaved); 
            pointsArrayKml.push(kmlstringtobesaved);
            if(toolID == 2) code_2(); 
        }

        if(adder == 2) {
            innerArray.push(stringtobesaved);
            innerArrayKml.push(kmlstringtobesaved);
        }
    }
}
function setstartMarker(point){
    startMarker = new google.maps.Marker({
        position: point,
        map: map});

}
function drawMarkers(point) {
    if(startMarker) startMarker.setMap(null);
    if(polyShape) polyShape.setMap(null);
    var id = plmcur;
    placemarks[plmcur].text_for_box = point.lat().toFixed(6)  + ',' + point.lng().toFixed(6);
    activateMarker();
    markerShape.setPosition(point);
    var marker = markerShape;
    tinyMarker = new google.maps.Marker({
        position: placemarks[plmcur].point,
        map: map,
        icon: tinyIcon
    });
    google.maps.event.addListener(marker, 'click', function(event){
        plmcur = id;
        markerShape = marker;
        var html = "<b>" + placemarks[plmcur].name + "</b> <br/>" + placemarks[plmcur].desc;
        infowindow.setContent(html);
        if(tinyMarker) tinyMarker.setMap(null);
        tinyMarker = new google.maps.Marker({
            position: placemarks[plmcur].point,
            map: map,
            icon: tinyIcon
        });
        if(toolID != 5) toolID = gob('toolchoice').value = 5;
        if(codeID == 1)logCode9();
        //infowindow.open(map,marker);
    });
    drawnShapes.push(markerShape);
    if(codeID == 1) logCode9();
}
function setTool(){
    //if(polyPoints.length == 0 && text_for_box == "") {
     //   newstart();
    //}else{
        if(toolID == 2){ // polygon
					
				newstart();
            //if(markerShape) {
             //   toolID = 5;
             //   nextshape();
             //   toolID = 2;
             //   newstart();
             //   return;
           // }
            placemarks[plmcur].style = polygonstyles[polygonstyles.length-1].name;
            placemarks[plmcur].stylecur = polygonstyles.length-1;
            if(polyShape) polyShape.setMap(null);

            preparePolygon(); 
            if(codeID == 1) code_2(); 
        }
        if(toolID == 5){
            if(polyShape) polyShape.setMap(null);
            newstart();
        }
//    }
}
function newstart() {

    polyPoints = [];
    outerPoints = [];
    pointsArray = [];
    markersArray = [];
    pointsArrayKml = [];
    markersArrayKml = [];
    addresssArray = [];
    outerArray = [];
    innerArray = [];
    outerArrayKml = [];
    innerArrayKml = [];
    innerArrays = [];
    innerArraysKml = [];
    adder = 0;
    firstdirclick = 0;
    if(startMarker) startMarker.setMap(null);
    if(nemarker) nemarker.setMap(null);
    if(tinyMarker) tinyMarker.setMap(null);
    if(toolID == 2){

	//showthis('polygonstuff');
        placemarks[plmcur].style = polygonstyles[polygonstyles.length-1].name;
        placemarks[plmcur].stylecur = polygonstyles.length-1;
        preparePolygon();
    }
    if(toolID == 5) {
        placemarks[plmcur].style = markerstyles[markerstyles.length-1].name;
        placemarks[plmcur].stylecur = markerstyles.length-1;
        preparePolyline();
    }
    text_for_box = "";

}


//]]>
</script>










