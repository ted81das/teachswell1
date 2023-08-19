// Lazy
var regularImages = [];
var active;
var activeRegular;
var img_count = 1;
var browserWidth;
var forceWidth = 0;
var jsDebug = 0;

function load() {
    browserWidth = window.innerWidth;
    regularImages = [].slice.call(document.querySelectorAll("img"));
    active = false;
    activeRegular = false;
    regularLoad();
}

if (wpc_vars.js_debug == 'true') {
    jsDebug = 1;
    console.log('JS Debug is Enabled');
}


if (jsDebug) {
    console.log('Safari: ' + isSafari);
}

function regularLoad() {
    if (activeRegular === false) {
        activeRegular = true;

        regularImages.forEach(function (Image) {

            if (Image.classList.contains('wps-ic-loaded')) {
                return;
            }

            Image.classList.add("ic-fade-in");
            Image.classList.add("wps-ic-loaded");
        });

        activeRegular = false;
    }
}

window.addEventListener("resize", regularLoad);
window.addEventListener("orientationchange", regularLoad);
document.addEventListener("scroll", regularLoad);
document.addEventListener("DOMContentLoaded", load);