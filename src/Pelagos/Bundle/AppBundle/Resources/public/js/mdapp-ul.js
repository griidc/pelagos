var $ = jQuery.noConflict();
var geomap = new GeoViz();

$(document).ready(function () {
    geomap.initMap("olmap", {"onlyOneFeature": false, "allowModify": false, "allowDelete": false, "staticMap": true, "labelAttr": "label"});
});

$(document).on("imready", function(e) {
    if (typeof (envelope_wkt) !== "undefined") {
        geomap.addFeatureFromWKT(envelope_wkt, {label: "bounding envelope"}, {"strokeColor": "#696969", "fillOpacity": "0"});
    }
    if (typeof (geometry_wkt) !== "undefined") {
        geomap.addFeatureFromWKT(geometry_wkt, {label: "data submitted"});
    }
    geomap.gotoAllFeatures();
});
