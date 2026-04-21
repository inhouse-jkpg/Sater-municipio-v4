$(document).ready(function() {

    if(typeof(webmaps) !== 'undefined') {
        require([
            "dojo/parser",
            "dojo/ready",
            "dijit/layout/BorderContainer",
            "dijit/layout/ContentPane",
            "dojo/dom",
            "esri/map",
            "esri/urlUtils",
            "esri/arcgis/utils",
            "esri/dijit/Legend",
            "esri/dijit/Scalebar",
            "dojo/domReady!"
        ], function(
            parser,
            ready,
            BorderContainer,
            ContentPane,
            dom,
            Map,
            urlUtils,
            arcgisUtils,
            Legend,
            Scalebar
        ) {
            ready(function(){
                parser.parse();
                
                $.each(webmaps, function(k, v){
                    
                    arcgisUtils.createMap(v, k).then(function(response){

                        var map = response.map;
    
                        //add the scalebar
                        var scalebar = new Scalebar({
                            map: map,
                            scalebarUnit: "metric"
                        });
                    });
                });

                

                // arcgisUtils.createMap('7f819ca6b79649319431953732d5ac5e','esri-map').then(function(response){

                //     var map = response.map;

                //     //add the scalebar
                //     var scalebar = new Scalebar({
                //         map: map,
                //         scalebarUnit: "metric"
                //     });
                // });
            });
        });
    }
});




