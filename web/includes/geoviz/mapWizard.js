function MapWizard(json)
{
    var $ = jQuery.noConflict();
    
    var json;
    
    var seed = Math.round(Math.random()*1e10);
    
    var buttonText = 'The Spatial Extent Wizard provides users with guided instruction and tools to describe the Spatial Extent of their dataset. Users can 1) indicate that their data has no spatial or temporal extent, or 2) Create a spatial extent geometry by either providing a list of coordinates or drawing a geometry using a map.';

    var featureSend = false;
    var drawTheMap = false;
    var startOffDrawing = true;
    var orderEnum;
    
    var wizGeoViz;
    
    var divSmallMap = '#'+json.divSmallMap;
    var divSpatialWizard ='#'+json.divSpatialWizard;
    var divNonSpatial = '#'+json.divNonSpatial;
    var gmlField = '#'+json.gmlField;
    var descField = '#'+json.descField;
    
    init();

    function init()
    {
        
        smlGeoViz = new GeoViz();
        smlGeoViz.initMap(json.divSmallMap,{'onlyOneFeature':false,'allowModify':false,'allowDelete':false,'staticMap':true});
        
        $(divSpatialWizard).html('<fieldset><div class="ui-widget-header ui-corner-all"><button style="color:#039203;font-size:larger;width:100%;" id="geowizBtn" type="button">Define Spatial Extent</button></div><p>'+buttonText+'</p></fieldset>').show();
        
        $(divNonSpatial).hide();
        
        $(document.body).append('<div id="divMapWizard"></div>');
        
         // $("#divMapWizard").load("mapWizard.html",function(){
            // // $('body').append($(this).clone().html());
            // // $(this).remove();
         // });
        
        $("#divMapWizard").append('<div id="geoWizard" style="width:100%;height:100%;"></div>                <div id="hasSpatial" style="display:none;">        <h2>Would you characterize your dataset as Spatial or Non-Spatial?</h2>        <p>Many datasets have an obvious spatial component (samples taken at a location or model results that describe an area). However, for some datasets, a location may not be relevant or even recorded (chemical analysis datasets wholly performed in the lab, data describing synthesis of new dispersants, etc.)</p>        <button id="yesSpatial" type="button">Spatial</button>        <button id="noSpatial" value="provideDesc" type="button">Non-Spatial</button>        </div>                <div id="provideDesc" style="display:none;">        <h2>Please provide a short statement describing why this dataset does not have a spatial component.</h2>        <p><i>Example - "Dataset contains laboratory measurements of oil degradation, no field sampling involved"</i></p>        <form id="wizDescForm">        <textarea class="required" id="wizDesc" cols="80" rows="5"></textarea><br/>        <button id="provideOk" type="button">Done</button>        </form>        </div>                <div id="mapwiz" style="display:none;">        <table id="maptoolstbl" width="100%" height="100%" border="0">        <tr valign="top">        <td width="80%" >        <!--Make sure the width and height of the map are 100%-->        <!--div id="olmap" style="width: 100%;height: 100%;"></div-->        </td>        <td width="20%">        <table width="100%" height="100%" border="0">        <tr>        <!--        <td align="center" valign="top" height="90%" style="position:relative;">        <label for="coordlist">Coordinate List</label>        <textarea id="coordlist" style="width:95%;position:absolute;left:5px;right:5px;top:20px;bottom:5px;"></textarea>        </td>        -->        <td align="center" valign="top">        <div id="coordtoolbar" class="ui-widget-header ui-corner-all">        <label id="coordlistLbl" for="coordlist">Coordinate List</label>        <textarea id="coordlist" style="width:95%;"></textarea>        <button style="width: 100%;" id="drawOnMap">Render on Map</button>        </div>        </td>        </tr>        <tr>        <td width="100%">        <h3>        <span id="wizDrawMode">Navigation</span> Mode</h3>        <fieldset>        <div id="maphelptxt" style="position:relative;overflow-x:hidden;overflow-y:auto;">                        </div>        </fieldset>        </td>                </tr>        <tr>        <td>        <div id="wiztoolbar" class="ui-widget-header ui-corner-all">        <button style="width: 100%;font-weight:bold;" id="saveFeature">Save and Finish</button>        <button style="width: 100%;" id="startDrawing">Start Drawing</button>        <button style="width: 100%;" id="deleteFeature">Delete</button>        <button style="width: 100%;" id="startOver">Change Mode</button>        <button style="width: 100%;" id="exitDialog">Restart Wizard</button>        </div>        </td>        </tr>        </table>        </td>        </tr>        </table>        </div>                <div id="helpinfo" title="Spatial Extent Wizard - 4" style="display:none;width:1000px">        <p>Define the spatial extent of your dataset by providing a list of coordinates or drawing on the map. Select the method and then the geometry type that best represents the spatial extent of your dataset.</p>                <div id="helptoolbar" style="font-size:100%;" class="ui-widget-header ui-corner-all">                <div style="display:table;width:100%;">        <div id="featMode" style="display:table-row;">        <div style="display:table-cell;">            <input type="radio" id="featPaste" name="featMode"><label for="featPaste">Insert Coordinate Text</label>        </div>        <div style="display:table-cell;">        <input type="radio" id="featDraw" name="featMode" checked="checked"><label for="featDraw">Draw on the Map</label>        </div>        </div>        </div>                <div style="display:table;width:100%;">        <div id="drawType" style="display:table-row;">        <div style="display:table-cell;">        <input type="radio" id="drawBox" name="drawType"><label for="drawBox">Box</label>        </div>        <div style="display:table-cell;">        <input class="button" type="radio" id="drawPolygon" name="drawType" checked="checked"><label for="drawPolygon">Polygon</label>                </div>        <div style="display:table-cell;">        <input type="radio" id="drawLine" name="drawType"><label for="drawLine">Line</label>        </div>        <div style="display:table-cell;">        <input type="radio" id="drawPoint" name="drawType"><label for="drawPoint">Point</label>        </div>                </div>        </div>                        </div>        </div>');
        
        wizGeoViz = new GeoViz();
        
        $(divSmallMap).on('gmlConverted', function(e, eventObj) {
            smlGeoViz.removeAllFeaturesFromMap();
            var addedFeature = smlGeoViz.addFeatureFromWKT(eventObj);
            smlGeoViz.gotoAllFeatures();
        });
        
        $(gmlField).change(function() {
            smlGeoViz.goHome();
            smlGeoViz.removeImage();
            smlGeoViz.removeAllFeaturesFromMap();
            smlGeoViz.gmlToWKT($(gmlField).val());
            if ($(gmlField).val() == "")
            {
                //difGeoViz.addImage('includes/images/notdefined.png',1);
            }
        });        
        
        $("#geowizBtn").button().click(function()
        {
            showWizard();
        });
        
        console.log('Spatial Wizard Ready');
    }
    
    function showWizard()
    {
        $.fn.qtip.defaults = $.extend(true, {}, $.fn.qtip.defaults, {
            show: {
                event: "mouseenter focus",
                solo: true
            },
            hide: {
                event: "mouseleave blur",
                delay: 100,
                fixed: true
            },
            style: {
                classes: "qtip-default qtip-shadow qtip-tipped"
            },
            position: {
                adjust: {
                    method: "flip flip"
                },
                my: "bottom right",
                at: "top left",
                viewport: $(window)
            }
        });
        
        $("#geoWizard").dialog({
            title: 'Spatial Extent Wizard',
            autoOpen: false,
            modal: true,
            resizable: false
        });
        
        orderEnum = wizGeoViz.orderEnum;
        
        $("#geoWizard").dialog({
            close: function(event, ui) { closeDialog() },
            resizeStop: function(){
                wizGeoViz.updateMap();
            },
            dragStop: function(){
                wizGeoViz.updateMap();
            }
        });
        
        $("#helpinfo").dialog({
            autoOpen: false,
            width: 400,
            modal: false,
            buttons: {
                OK: function() {
                    if ($("#drawPolygon:checked").length){$("#drawPolygon:checked").click();}
                    if ($("#drawLine:checked").length){$("#drawLine:checked").click();}
                    if ($("#drawPoint:checked").length){$("#drawPoint:checked").click();}
                    if ($("#drawBox:checked").length){$("#drawBox:checked").click();}
                    if ($("#featDraw:checked").length){$("#featDraw:checked").click();}
                    if ($("#featPaste:checked").length){$("#featPaste:checked").click();}
                    
                    if (startOffDrawing)
                    {wizGeoViz.startDrawing();}
                    else
                    {$('#geoWizard #coordlist').focus();}
                    
                    if (!$("#drawPolygon:checked").length && !$("#drawLine:checked").length && !$("#drawPoint:checked").length && !$("#drawBox:checked").length && !$("#featDraw:checked").length && !$("#featPaste:checked").length)
                    {alert('Please make a selection!');}
                    else
                    {
                        $(this).dialog('close');
                        wizGeoViz.updateMap();
                    }
                }
            }
        });
        
        $(document).on('imready', function(e,who) {
            if (who == '#olmap')
            {
                if (drawTheMap)
                {
                    drawMap();
                }
            }
        });
        
        
        //$("#wizard").fadeIn();
        //$(":button").button().click(function(){showMain($(this));});
        $("#geoWizard").html($("#hasSpatial").clone(true).fadeIn());
        //$("#geoWizard").html($("#mainStuff").html());
        $("#geoWizard").dialog( "option", "title", "Spatial Extent Wizard - 1" );
        $("#geoWizard").dialog( "option", "width", 700);
        $("#geoWizard").dialog( "option", "height", 250);
        $("#geoWizard").dialog("open");
        
        $("#geoWizard #yesSpatial").click(function()
        {
            drawMap();
        });
        
        $("#geoWizard #noSpatial").click(function()
        {
            console.log('click');
            noSpatial();
        });
        
    }
    
    function hasSpatial(Spatial)
    {
        if (Spatial)
        { 
            $('#'+json.divNonSpatial).show(); 
            $('#'+json.divSpatial).hide(); 
        }
        else
        { 
            $('#'+json.divSpatial).show(); 
            $('#'+json.divNonSpatial).hide(); 
        }
    }

    
    function animateTo(Width, Height, Complete)
    {
        var dlg = $("#geoWizard").parents(".ui-dialog:first");
        var dlgTop = dlg.position().top;
        var dlgHeight = dlg.height();
        var newTop = ($(window).height()-Height)/2;
        var newLeft = ($(window).width()-Width)/2;
        var topDiff = (Height - dlgHeight)/2;
        newTop = ((dlgTop-topDiff)+newTop)/2;
        
        //dlg.animate({ width: Width, height: Height, left: newLeft, top: newTop},{complete: Complete});
        //$("#geoWizard").height(Height-50);
        //$("#geoWizard").dialog("option", "position", "center");
        $("#geoWizard").dialog({
            width: Width,
            height: Height,
            title: 'Spatial Extent Wizard',
            autoOpen: false,
            modal: true,
            resizable: false,
            position: {
                my: "center",
                at: "center",
                of: window
            }
        });
        
        //Complete();
    }
    
    function drawMap()
    {
        var diaWidth = $(window).width()*.8;
        var diaHeight = $(window).height()*.8;
        
        hasSpatial(false);
        
        $("#mapwiz").height(diaHeight-50);
        
        animateTo(diaWidth,diaHeight);
        
        finalizeMap();
        
        $("#geoWizard").dialog( "option", "title", "Spatial Extent Wizard - 3" );
        
        
    }
    
    function finalizeMap()
    {
        $("#geoWizard").html($("#mapwiz").clone(true).show());
        $('#geoWizard table#maptoolstbl tbody tr td').first().html('<div id="olmap" style="width: 100%;height: 100%;"></div>');
        wizGeoViz.initMap('olmap',{'onlyOneFeature':true,'allowModify':true,'allowDelete':true});
        //$("#geoWizard").html($("#mapwiz").fadeIn());
        //wizGeoViz.updateMap();
        setEvents();
        fixMapToolHeight();
        
        if ($(gmlField).val() != "")// && !featureSend)
        {
            //addFeatureFromcoordinateList($(gmlField).val());
            
            wizGeoViz.gmlToWKT($(gmlField).val());
            
            $('#olmap').on('gmlConverted', function(e, eventObj) {
                //debugger;
                var addedFeature = wizGeoViz.addFeatureFromWKT(eventObj);
                $('#geoWizard #coordlist').val(wizGeoViz.getCoordinateList(addedFeature.id));
            });
            console.log('Initial Pop');
            featureSend = true;
        }
        else if ($(gmlField).val() == "")
        {
            $("#helpinfo").dialog('open');
        }
    }
    
    
    function convertToConvexHull()
    {
        wizGeoViz.convexHull(wizGeoViz.getSingleFeature());
        
        $('#olmap').on('featureConverted', function(e, eventObj) {
            wizGeoViz.removeAllFeaturesFromMap();
            wizGeoViz.addFeatureFromWKT(wizGeoViz.wktTransformToWGS84(eventObj.wkt));
        });
        
        return true;
    }
    
    function whatIsCoordinateOrder()
    {
        var whatOrder = wizGeoViz.determineOrder($('#geoWizard #coordlist').val());
        var diaMessage = '';
        var diaButtons = [ {text:"Yes",click:function(){$(this).dialog("close");}},{text:"No",click:function(){$(this).dialog("close");}} ];
        var realOrder = 0;
        
        
        if (whatOrder == orderEnum.LATLONG)
        {
            diaMessage = 'This is Latitude, Longitude order, right?';
            diaButtons = [ {text:"Yes",click:function(){$(this).dialog("close");wizAddFeature(orderEnum.LATLONG);}},{text:"No, it\'s Longitude,Latitude",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.LATLONGML)
        {
            diaMessage = 'Most likely this is Latitude, Longitude order, is this correct?';
            diaButtons = [ {text:"Yes",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}},{text:"No, it\'s Longitude,Latitude",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.LONGLAT)
        {
            diaMessage = 'This is Longitude, Latitude order, right?';
            diaButtons = [ {text:"Yes",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}},{text:"No, it\'s Latitude,Longitude",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.LONGLATML)
        {
            diaMessage = 'Most likely this is Longitude, Latitude order, is this correct?';
            diaButtons = [ {text:"Yes",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}},{text:"No, it\'s Latitude,Longitude",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.UNKNOWN)
        {
            diaMessage = 'What is the coordinate order?';
            diaButtons = [ {text:"Latitude,Longitude",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}},{text:"Longitude,Latitude",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}} ];
        }
        else if (whatOrder == orderEnum.MIXED)
        {
            diaMessage = 'The coordinate order seems to be mixed!';
            diaButtons = [ {text:"I'll fix it!",click:function(){wizAddFeature(orderEnum.MIXED);$(this).dialog("close");}},{text:"No, it\'s Latitude,Longitude",click:function(){wizAddFeature(orderEnum.LATLONG);$(this).dialog("close");}},{text:"No, it\'s Longitude,Latitude",click:function(){wizAddFeature(orderEnum.LONGLAT);$(this).dialog("close");}} ];
        }
        
        $("<div>"+diaMessage+"</div>").dialog({
            autoOpen: true,
            title: 'Coordinate Order?',
            height: 200,
            width: 500,
            buttons: diaButtons,
            modal: true,
            close: function( event, ui ) {
                $(this).dialog("destroy").remove();
                $(document).trigger('coordinateOrder',realOrder);
                realOrder = 0;
                
            }
        }); 
    }
    
    function wizAddFeature(llOrder)
    {
        var flipOrder = false;
        
        if (llOrder == orderEnum.LONGLAT)
        {
            flipOrder = true;
            //console.log('I AM FLIPPING IT!');
        }    
        wizGeoViz.removeAllFeaturesFromMap();
                
        var wktVal = $('#geoWizard #coordlist').val();
        
        var triedAdd = wizGeoViz.addFeatureFromcoordinateList(wktVal,flipOrder);
        
        if (!triedAdd)
        {
            $("<div>Those coordinates couldn't been made into a valid feature!</div>").dialog({
                autoOpen: true,
                title: 'WARNING!',
                //height: 140,
                buttons: {
                    OK: function() {
                        $(this).dialog('close');
                    }},
                modal: true,
                close: function( event, ui ) {
                    $(this).dialog("destroy").remove();
                }
            }); 
        }
        else
        {
            wizGeoViz.gotoAllFeatures();
            
        }
    }
    
    function renderOnMap()
    {
        wizGeoViz.stopDrawing();
        wizGeoViz.removeAllFeaturesFromMap();
        
        whatIsCoordinateOrder();
        
    }
    
    function saveFeature()
    {
        var myWKTid = wizGeoViz.getSingleFeature();
        //console.log(myWKTid);
        if (typeof myWKTid != "undefined")
        {
            var myWKT = wizGeoViz.getWKT(myWKTid);
            var wgsWKT = wizGeoViz.wktTransformToWGS84(myWKT);
            wizGeoViz.wktToGML(wgsWKT);
            
            $('#olmap').on('wktConverted', function(e, eventObj) {
                //console.log(eventObj);
                $(gmlField).val(eventObj);
                $(gmlField).trigger('change');
                
                closeDialog();
            });
        }
        else
        {
            $(gmlField).val("");
            closeDialog();
            $(gmlField).trigger('change');
        }
    }
    
    function setEvents()
    {
        $('#olmap').on('closeMe', function(e, eventInfo) {
            //$(gmlField).val(getCoordinateList(vlayer.features[0]));
            //$(gmlField).val(eventInfo);
            closeDialog();
        });
            
        $('#olmap').on('featureAdded', function(e, eventInfo) { 
            $('#geoWizard #coordlist').val(eventInfo);
        });
        
        $('#olmap').on('modeChange', function(e, eventInfo) { 
            $('#wizDrawMode').html(eventInfo);
            //console.debug(eventInfo);
            showNavMode();
        });
        
        $('#olmap').on('vectorChanged', function(e, eventInfo) { 
            $('#geoWizard #coordlist').val(eventInfo);
            //console.log(eventInfo);
            //console.log(e);
        });
        
        $('#olmap').on('coordinateError', function(e, eventInfo) { 
            $("<div>"+eventInfo+"</div>").dialog({
                autoOpen: true,
                title: 'WARNING!',
                //height: 140,
                buttons: {
                    OK: function() {
                    $(this).dialog('close');
                    }},
                modal: true,
                close: function( event, ui ) {
                    $(this).dialog("destroy").remove();
                }
            }); 
        });
  
        $("#geoWizard #saveFeature").button({ icons: { primary: "ui-icon ui-icon-disk"}}).click(function()
        {
            saveFeature();
        })
        .qtip({    content: {
            text: 'Saves extent to the metadata editor and closes wizard'
        }});
        
        $("#geoWizard #drawOnMap").button({ icons: { primary: "ui-icon ui-icon-check"}}).click(function()
        {renderOnMap();})
        .qtip({    content: {
                text: 'Re-renders to the exent on the map after changes to the coordinate list'
            }});
        
        $("#geoWizard #startDrawing").button({ icons: { primary: "ui-icon ui-icon-pencil"}}).click(function()
        {
            wizGeoViz.startDrawing();
            wizGeoViz.updateMap();
        })
        .qtip({    content: {
            text: 'Puts map in drawing mode, only one feature can be drawn on the map at a time'
        }});
        
        $("#geoWizard #deleteFeature").button({ icons: { primary: "ui-icon ui-icon-trash"}}).click(function()
        {
            wizGeoViz.deleteSelected();
        })
        .qtip({    content: {
            text: 'Deletes selected feature'
        }});
        
        $("#geoWizard #exitDialog").button({ icons: { primary: "ui-icon ui-icon-refresh"}}).click(function()
        {
            closeDialog();
            $("#geoWizard").html("");
            $("#geoWizard #coordlist").empty();
            wizGeoViz.removeAllFeaturesFromMap();
            $(gmlField).val("");
            //animateTo(700,250);
            //$("#BPLbtn_DataIdent").click();
            showWizard();
        })
        .qtip({    content: {
            text: 'Restart wizard from beginning'
        }});
        
        $("#geoWizard #startOver").button({ icons: { primary: "ui-icon ui-icon-wrench"}}).click(function()
        {
            wizGeoViz.stopDrawing();
            $('#geoWizard #coordlist').val('');
            $(gmlField).val('');
            wizGeoViz.removeAllFeaturesFromMap();
            wizGeoViz.goHome();
            $("#helpinfo").dialog('open');
            
        })
        .qtip({    content: {
            text: 'Reselect geometry type and mode'
        }});
        
        
        $("#drawPolygon").button().click(function()
        {wizGeoViz.setDrawMode('polygon');});
        
        $("#drawLine").button().click(function()
        {wizGeoViz.setDrawMode('line');});
        
        $("#drawPoint").button().click(function()
        {wizGeoViz.setDrawMode('point');});
        
        $("#drawBox").button().click(function()
        {wizGeoViz.setDrawMode('box');});
        
        $("#featDraw").button().click(function()
        {startOffDrawing=true;});
        
        $("#featPaste").button().click(function()
        {startOffDrawing=false;});
        
        $('#geoWizard #coordlist').focus(function () {
            showTextMode();
        });
        
        $("#geoWizard #coordlist").click(function()
        {
            wizGeoViz.stopDrawing();
            showTextMode();
        });
        
        $("#geoWizard #coordlist").keydown(function()
        {
            wizGeoViz.stopDrawing();
            showTextMode();
        });
        
        function showTextMode()
        {
            $('#geoWizard #wizDrawMode').html("Text");
            var mapHelpText = "Coordinates should be latitude, longitude, but the wizard reverse your coordinate in alternate order. Coordinates can be modified in the list, click Render on Map to update feature";
            $("#geoWizard #maphelptxt").html(mapHelpText);
        }
        
        function showNavMode ()
        {
            var mapHelpText = "Double-click to finish drawing feature. Click feature to edit or edit Coordinate List directly. Drag hollow circles to move vertexes, draw solid midpoint circles to create new vertexes. Select feature and click Delete button to delete feature";
            $("#geoWizard #maphelptxt").html(mapHelpText);
        }
    }
    
    function verifyMap()
    {
        $(gmlField).val($('#wizCoordChk').val());
        drawMap();
    }
    
    function noSpatial()
    {
        animateTo(700,350);
        hasSpatial(true);
        $("#geoWizard").dialog( "option", "title", "Spatial Extent Wizard - 2" );
        $("#geoWizard").html($("#provideDesc").clone(true).fadeIn());
        $("#wizDesc").focus();
        //$("#wizDescForm").validate();
        
        $('#geoWizard #provideOk').click(function()
        {
            $(descField).focus();
            $(descField).val($("#geoWizard #wizDesc").val());
            closeDialog(); 
        });
    }
    
    function noSpatialClose()
    {
        $("#wizDescForm").validate();
        //debugger;
        
        
    }
    
    function closeDialog()
    {
        //$("#geoWizard").fadeOut();
        $("#geoWizard").dialog("close");
        //$("#geoWizard").dialog("destroy");
    }
    
    function validateCoords(Unchecked, Checked)
    {
        $('#'+Checked).text(wizGeoViz.checkPointList($("#"+Unchecked).val()));
        var retval = wizGeoViz.determineOrder($('#'+Checked).val());
    }
    
    function fixMapToolHeight()
    {
        var tblHgt = $("#geoWizard #maptoolstbl").height();
        tblHgt = tblHgt - $("#geoWizard #wiztoolbar").height();
        tblHgt = tblHgt - $("#geoWizard #coordlistLbl").height();
        tblHgt = tblHgt - 50; //padding
    
        $("#geoWizard #coordlist").height((tblHgt*.4));
        $("#geoWizard #maphelptxt").height((tblHgt*.4));
        //$("#wiztoolbar").height();
        $("#geoWizard #coordlist").css("max-width:"+$("#geoWizard #coordlist").width()+"px;")
    
    }
    
}