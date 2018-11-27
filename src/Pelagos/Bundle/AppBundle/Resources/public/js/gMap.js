(function($) {
    "use strict";

    $.fn.gMap = function(options) {
        return this.each(function() {
            var gml = $(this).data("extent");

            var thisMap = this;

            jQuery.ajax({
                url: Routing.generate("pelagos_app_gml_towkt"),
                type: "POST",
                data: {gml: gml},
                context: document.body
            })
            .done(function(wkt) {
                renderMap(thisMap, wkt);
            });
        });
    };

    function renderMap(map, wkt) {
        var raster = new ol.layer.Tile({
            source: new ol.source.OSM()
        });

        var format = new ol.format.WKT();

        var googleLayerSatellite = new ol.layer.Tile({
            title: "Google Satellite",
            source: new ol.source.TileImage({
                url: "https://mt1.google.com/vt/lyrs=y&hl=en&&x={x}&y={y}&z={z}"
            }),
        });

        var feature = format.readFeature(wkt, {
            dataProjection: "EPSG:4326",
            featureProjection: "EPSG:3857"
        });

        var style = new ol.style.Style({
            fill: new ol.style.Fill({
              color: "rgba(255, 255, 255, 0)"
            }),
            stroke: new ol.style.Stroke({
              color: "rgba(255, 165, 0, 0.6)",
              width: 2
            }),
            image: new ol.style.Circle({
                radius: 8,
                fill: new ol.style.Fill({
                    color: "rgba(255, 255, 255, 0)"
                }),
                stroke: new ol.style.Stroke({
                  color: "rgba(255, 165, 0, 0.6)",
                  width: 2
                })
            })
        });
        
        var source = new ol.source.Vector({
            features: [feature]
        });

        var vector = new ol.layer.Vector({
            source: source,
            style: [style]
        });

        var view = new ol.View({
          center: ol.proj.fromLonLat([-90.5, 25]),
          zoom: 4,
          maxZoom: 12,
          minZoom: 1,
        });

        var map = new ol.Map({
            target: map,
            layers: [
                googleLayerSatellite, vector
            ],
            view: view
        });
        
        var extent = source.getExtent();
        map.getView().fit(extent, map.getSize());

    };
}(jQuery));