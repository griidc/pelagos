var $ = jQuery.noConflict();
var geomap = new GeoViz();

$(document).ready(function () {
    geomap.initMap("olmap", {"onlyOneFeature": false, "allowModify": false, "allowDelete": false, "staticMap": true, "labelAttr": "label"});
});

$(document).on("imready", function(e) {
    var envelopeWkt = $('[envelope]').attr('envelope');
    var geometryWkt = $('[geometry]').attr('geometry');
    if (typeof (envelopeWkt) !== "undefined") {
        geomap.addFeatureFromWKT(envelopeWkt, {label: "bounding envelope"}, {"strokeColor": "#696969", "fillOpacity": "0"});
    }
    if (typeof (geometryWkt) !== "undefined") {
        geomap.addFeatureFromWKT(geometryWkt, {label: "data submitted"});
    }
    geomap.gotoAllFeatures();
});
