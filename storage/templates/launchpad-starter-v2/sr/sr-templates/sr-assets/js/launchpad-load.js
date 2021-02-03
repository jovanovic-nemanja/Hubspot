/*-----------------------------------------------------------------------------------*/
/*
/*  Lean Labs LaunchPad Dependency Load Helper
/*
/*  Author: @helloteichner
/*  Version: 1.0.2
/*
/*-----------------------------------------------------------------------------------*/

// LaunchPad Location
// var launchPadPath = "//cdn2.hubspot.net/hubfs/2135470/";
var launchPadPath = "http://127.0.0.1:8080/launchpad/custom/page/";

$('document').ready(function(){
	var cssLoaded = [];
	var jsLoaded = [];

	// Function to load a given css file
	function loadCSS(href) {
		if($.inArray( href, cssLoaded )==-1){
			var cssLink = $("<link rel='stylesheet' type='text/css' href='" + launchPadPath + href + "'>");
			$(cssLink).insertBefore( $("head link:last-child") );
			cssLoaded.push(launchPadPath + href);
			console.log(cssLoaded);
		}
	}

	function loadAndExecuteScripts(loadJS, index, callback) {
		$.getScript(launchPadPath + loadJS[index], function () {
			if((index + 1 <= loadJS.length - 1) && ($.inArray( launchPadPath + loadJS[index], jsLoaded )==-1)) {
				loadAndExecuteScripts(loadJS, index + 1, callback);
				jsLoaded.push(launchPadPath + loadJS[index]);
			} else {
				if(callback) {
					callback();
					jsLoaded.push(launchPadPath + loadJS[index]);
					console.log(jsLoaded);
				}
			}
		});
	}

	window.loadAndExecuteScripts=loadAndExecuteScripts;
	window.loadCSS=loadCSS;
});
