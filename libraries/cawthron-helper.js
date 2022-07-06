
"use strict";

var _SETTINGS = {
    showErrors: true
}


if(!document.currentScript) {
    document.currentScript = (function () { 
        var scripts = document.getElementsByTagName("script");
        return scripts[scripts.length - 1];
    }());
}

var path = (function() {
    const filePath = "libraries/cawthron-helper.js"
    var _path = document.currentScript.dataset.path

    function getPathFromScript(src) {
        var regex = new RegExp("(.*)" + filePath);
        var match = src.match(regex);
        return match[1];
    }

    if (!_path) {
        _path = getPathFromScript(document.currentScript.src);
    }
    return _path;
})();

$(function(){
    var resizeTimeout = false;
    window.addEventListener('resize', function(event) {
        clearTimeout(resizeTimeout)
        resizeTimeout = setTimeout(function() {
            $.event.trigger("window.resized");
        }, 100);
    })
});


window.onerror = function(msg, source, lineno, colno, error) {
    if (_SETTINGS && !_SETTINGS.showErrors) {
        return false;
    } else {
        if (msg.toLowerCase().indexOf("script error") > -1) {
            alert("Script Error: See Browser Console for Detail");
        } else {

            var maskedSource = source;
            var pattern = /(([\?&])?apikey=)([\w]*)/;
            // patron ejemplo:
            //  0 = ?apikey=abc123
            //  1 = ?apikey=
            //  2 = ?
            //  3 = abc123
            var match = source.match(pattern);
            if (match) {
                if(match[2]==="&") {
                    maskedSource = source.replace(match[0], "")
                } else {
                    maskedSource = source.replace(match[0], "?")
                }
            }
            var messages = [
                "Cawthron telemetry service Error messages:",
                '-------------',
                "Message: " + msg,
                "Route: " + maskedSource.replace(path,""),
                "Line: " + lineno,
                "Column: " + colno
            ];
            if (Object.keys(error).length > 0) {
                messages.push("Error: " + JSON.stringify(error));
            }
            alert(messages.join("\n"));
        }
        return true;
    };
}

if (typeof localStorage !== 'undefined') {
    var themecolor = localStorage.getItem('themecolor');
    if (themecolor===null) {
        themecolor = current_themecolor
    }
    if (themecolor!=current_themecolor) {
        $("html").removeClass('theme-'+current_themecolor).addClass('theme-'+themecolor);
        current_themecolor = themecolor
    }
    
    var themesidebar = localStorage.getItem('themesidebar');
    if (themesidebar===null) {
        themesidebar = current_themesidebar
    }
    if (themesidebar!=current_themesidebar) {
        $("html").removeClass('sidebar-'+current_themesidebar).addClass('sidebar-'+themesidebar);
        current_themesidebar = themesidebar
    }
}
