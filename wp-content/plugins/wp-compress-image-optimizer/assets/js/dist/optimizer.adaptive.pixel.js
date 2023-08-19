// IsMobile
var mobileWidth;
var isMobile = false;
var jsDebug = false;
var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

if (wpc_vars.js_debug == 'true') {
    jsDebug = true;
}

function checkMobile() {
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 680) {
        isMobile = true;
        mobileWidth = window.innerWidth;
    }
}

checkMobile();
// All in One
(function (w) {
    // var dpr = ((w.devicePixelRatio === undefined) ? 1 : w.devicePixelRatio);
    // document.cookie = 'ic_pixel_ratio=' + dpr + '; path=/';
})(window);
var preloadRunned = false;
var windowWidth = window.innerWidth;

// Delay JS Script
var wpcEvents = ['keydown', 'mousemove', 'touchmove', 'touchstart', 'touchend', 'wheel', 'visibilitychange', 'resize'];
wpcEvents.forEach(function (eventName) {

    if (jsDebug) {
        console.log('Event registered: ' + eventName);
    }

    window.addEventListener(eventName, function(){
        preloadTimeout(eventName);
    });
});

function preloadTimeout(event) {

    if (jsDebug) {
        console.log('Running Preload Timeout');
    }

    if (!preloadRunned) {

        if (jsDebug) {
            console.log('Event name in preload is ');
            console.log(event);
            console.log('Before width: ' + windowWidth);
            console.log('After width: ' + window.innerWidth);
        }

        if (event == 'resize') {
            if (windowWidth === window.innerWidth) {
                // Nothing changed, ignore the event
                return false;
            }
        }

        preloadRunned = true;
        setTimeout(function () {
            if (jsDebug) {
                console.log('Inside Preload Timeout');
            }
            preload();
            removeEventListeners();
        }, 50);
    }
}

function removeEventListeners() {
    wpcEvents.forEach(function (eventName) {
        window.removeEventListener(eventName, preloadTimeout);
    });
}

window.addEventListener('load', function () {
    var scrollTop = window.scrollY;
    if (scrollTop > 60) {
        preload();
    }
});

function preload() {
    var iframes = [].slice.call(document.querySelectorAll("iframe.wpc-iframe-delay"));
    var allScripts = [].slice.call(document.querySelectorAll('script[type="wpc-delay-script"]'));
    var styles = [].slice.call(document.querySelectorAll('[rel="wpc-stylesheet"]'));
    var mobileStyles = [].slice.call(document.querySelectorAll('[rel="wpc-mobile-stylesheet"]'));

    var wpScripts = [];
    var customPromiseFlag = [];

    if (jsDebug) {
        console.log('Found scripts');
        console.log(allScripts);
    }

    // Move wp-include scripts into wpScripts array to load them first
    for (var i = 0; i < allScripts.length; i++) {
        var script = allScripts[i];
        if (script.src && script.src.includes('wp-includes')) {
            wpScripts.push(script);
            allScripts.splice(i, 1);
            i--;
        }
    }

    if (jsDebug) {
        console.log('Found WP scripts');
        console.log(wpScripts);
    }

    wpScripts.forEach(function (element, index) {
        var newScript = document.createElement('script');
        newScript.setAttribute('src', element.getAttribute('src'));
        newScript.setAttribute('type', 'text/javascript');
        document.body.appendChild(newScript);
    });

    wpScripts = [];

    allScripts.forEach(function (element, index) {
        var elementID = element.id;

        if (jsDebug) {
            console.log(element);
        }

        if (!element.hasAttribute('src') && !element.id.includes('-before') && !element.id.includes('-after') && !element.id.includes('-extra')) {
            var newElement = document.createElement('script');
            newElement.textContent = element.textContent;
            newElement.setAttribute('type', 'text/javascript');
            newElement.async = false;
            document.body.appendChild(newElement);
        } else {
            // External script
            var jsBefore = document.getElementById(elementID + '-before');
            var jsAfter = document.getElementById(elementID + '-after');
            var jsExtra = document.getElementById(elementID + '-extra');

            if (jsBefore !== null) {
                var newElementBefore = document.createElement('script');
                newElementBefore.textContent = jsBefore.textContent;
                newElementBefore.setAttribute('type', 'text/javascript');
                newElementBefore.async = false;
                document.body.appendChild(newElementBefore);
            }

            if (jsAfter !== null) {
                //jsAfter.setAttribute('type', 'text/javascript');
                // eval(jsAfter.textContent);
            }

            if (jsExtra !== null) {
                var newElementExtra = document.createElement('script');
                newElementExtra.textContent = jsExtra.textContent;
                newElementExtra.setAttribute('type', 'text/javascript');
                newElementExtra.async = false;
                document.body.appendChild(newElementExtra);
            }

            if (element !== null) {
                var new_element = document.createElement('script');
                new_element.setAttribute('src', element.getAttribute('src'));
                new_element.setAttribute('type', 'text/javascript');
                new_element.async = false;
                new_element.onload = function () {
                    if (jsAfter !== null) {
                        var new_elementAfter = document.createElement('script');
                        new_elementAfter.textContent = jsAfter.textContent;
                        new_elementAfter.setAttribute('type', 'text/javascript');
                        document.body.appendChild(new_elementAfter);
                        jsAfter.remove();
                    }
                };
                document.body.appendChild(new_element);
            }


            if (element !== null) {
                element.remove();
            }

            if (jsBefore !== null) {
                jsBefore.remove();
            }

            if (jsExtra !== null) {
                jsExtra.remove();
            }
        }

        // Remove the element from the array
        //allScripts.splice(index, 1);
    });

    allScripts = [];

    styles.forEach(function (element, index) {
        var promise = new Promise(function (resolve, reject) {
            element.setAttribute('rel', 'stylesheet');
            element.setAttribute('type', 'text/css');
            element.addEventListener('load', function () {
                resolve();
            });

            element.addEventListener('error', function () {
                reject();
            });
        });
        customPromiseFlag.push(promise);
    });

    styles = [];

    iframes.forEach(function (element, index) {
        var promise = new Promise(function (resolve, reject) {
            var iframeUrl = element.getAttribute('data-src');
            element.setAttribute('src', iframeUrl);
            element.addEventListener('load', function () {
                resolve();
            });

            element.addEventListener('error', function () {
                reject();
            });
        });
        customPromiseFlag.push(promise);
    });

    iframes = [];

    mobileStyles.forEach(function (element, index) {
        var promise = new Promise(function (resolve, reject) {
            element.setAttribute('rel', 'stylesheet');
            element.setAttribute('type', 'text/css');
            element.addEventListener('load', function () {
                resolve();
            });

            element.addEventListener('error', function () {
                reject();
            });
        });
        customPromiseFlag.push(promise);
    });

    mobileStyles = [];

    Promise.all(customPromiseFlag).then(function () {
        var criticalCss = document.querySelector('#wpc-critical-css');
        if (criticalCss) {
            criticalCss.remove();
        }
    }).catch(function () {
        styles.forEach(function (element, index) {
            element.setAttribute('rel', 'stylesheet');
            element.setAttribute('type', 'text/css');
        });
    });

    wpcEvents.forEach(function (eventName) {
        window.removeEventListener(eventName, preload);
    });

}
// OK
function SetupNewApiURL(newApiURL, imgWidth, imageElement) {
    if (imgWidth > 0 && !imageElement.classList.contains('wpc-excluded-adaptive')) {
        newApiURL = newApiURL.replace(/w:(\d{1,5})/g, 'w:' + imgWidth);
    }

    if (jsDebug) {
        console.log('Set new Width');
        console.log(imageElement);
        console.log(imageElement.width);
        console.log(imageElement.parentElement);
        console.log(imageElement.parentElement.offsetWidth);
        console.log(imgWidth);
    }

    if ((window.devicePixelRatio >= 2 && wpc_vars.retina_enabled == 'true') || wpc_vars.force_retina == 'true') {
        newApiURL = newApiURL.replace(/r:0/g, 'r:1');

        if (jsDebug) {
            console.log('Retina set to True');
            console.log('DevicePixelRation ' + window.devicePixelRatio);
        }

    } else {
        newApiURL = newApiURL.replace(/r:1/g, 'r:0');

        if (jsDebug) {
            console.log('Retina set to False');
            console.log('DevicePixelRation ' + window.devicePixelRatio);
        }
    }

    if (wpc_vars.webp_enabled == 'true' && isSafari == false) {
        if (!imageElement.classList.contains('wpc-excluded-webp')) {
            newApiURL = newApiURL.replace(/wp:0/g, 'wp:1');
        }

        if (jsDebug) {
            console.log('WebP set to True');
        }

    } else {
        newApiURL = newApiURL.replace(/wp:1/g, 'wp:0');

        if (jsDebug) {
            console.log('WebP set to False');
        }

    }

    if (wpc_vars.exif_enabled == 'true') {
        newApiURL = newApiURL.replace(/e:0/g, 'e:1');
    } else {
        newApiURL = newApiURL.replace(/\/e:1/g, '');
        newApiURL = newApiURL.replace(/\/e:0/g, '');
    }

    if (isMobile) {
        newApiURL = getSrcset(newApiURL.split(","), mobileWidth, imageElement);
    }

    return newApiURL;
}
// OK
function srcSetUpdateWidth(srcSetUrl, imageWidth, imageElement) {

    if (imageElement.classList.contains('wpc-excluded-adaptive')) {
        imageWidth = 1;
    }

    var srcSetWidth = srcSetUrl.split(' ').pop();
    if (srcSetWidth.endsWith('w')) {
        // Remove w from width string
        var Width = srcSetWidth.slice(0, -1);
        if (parseInt(Width) <= 5) {
            Width = 1;
        }
        srcSetUrl = srcSetUrl.replace(/w:(\d{1,5})/g, 'w:' + Width);
    } else if (srcSetWidth.endsWith('x')) {
        var Width = srcSetWidth.slice(0, -1);
        if (parseInt(Width) <= 3) {
            Width = 1;
        }
        srcSetUrl = srcSetUrl.replace(/w:(\d{1,5})/g, 'w:' + Width);
    }
    return srcSetUrl;
}
// OK
function getSrcset(sourceArray, imageWidth, imageElement) {
    var changedSrcset = '';

    sourceArray.forEach(function (imageSource) {

        if (jsDebug) {
            console.log('Image src part from array');
            console.log(imageSource);
        }

        newApiURL = srcSetUpdateWidth(imageSource.trimStart(), imageWidth, imageElement);
        changedSrcset += newApiURL + ",";
    });

    return changedSrcset.slice(0, -1); // Remove last comma
}
// OK
function listHas(list, keyword) {
    var found = false;
    list.forEach(function (className) {
        if (className.includes(keyword)) {
            found = true;
        }
    });


    if (found) {
        return true;
    } else {
        return false;
    }

}
// OK
function removeElementorInvisible() {
    var elementorInvisible = document.querySelectorAll(".elementor-invisible");

    for (i = 0; i < elementorInvisible.length; ++i) {
        elementorSection = elementorInvisible[i];
        if ((elementorSection.getBoundingClientRect().top <= window.innerHeight && elementorSection.getBoundingClientRect().bottom >= 0) && getComputedStyle(elementorSection).display !== "none") {
            elementorSection.classList.remove('elementor-invisible');
        }
    }
}
function runAdaptive() {
    var adaptiveImages = [].slice.call(document.querySelectorAll("img[data-wpc-loaded='true']"));

    adaptiveImages.forEach(function (entry) {
        var adaptiveImage = entry;

        // Integrations
        masonry = adaptiveImage.closest(".masonry");
        owlSlider = adaptiveImage.closest(".owl-carousel");
        SlickSlider = adaptiveImage.closest(".slick-slider");
        SlickList = adaptiveImage.closest(".slick-list");
        slides = adaptiveImage.closest(".slides");

        if (jsDebug) {
            console.log(masonry);
            console.log(owlSlider);
            console.log(SlickSlider);
            console.log(SlickList);
            console.log(slides);
        }

        /**
         * Is SlickSlider/List?
         */
        if (SlickSlider || SlickList || slides || owlSlider || masonry) {
            if (typeof adaptiveImage.dataset.src !== 'undefined' && adaptiveImage.dataset.src != '') {
                newApiURL = adaptiveImage.dataset.src;
            }
            else {
                newApiURL = adaptiveImage.src;
            }

            newApiURL = newApiURL.replace(/w:(\d{1,5})/g, 'w:1');
            adaptiveImage.src = newApiURL;
            adaptiveImage.classList.add("ic-fade-in");
            adaptiveImage.classList.add("wpc-remove-lazy");
            adaptiveImage.classList.remove("wps-ic-lazy-image");
            return;
        }

        if (wpc_vars.adaptive_enabled == 'false' || adaptiveImage.classList.toString().includes('logo')) {
            imgWidth = 1;
        } else {
            imageStyle = window.getComputedStyle(adaptiveImage);
            imgWidth = Math.round(parseInt(imageStyle.width));

            if (typeof imgWidth == 'undefined' || !imgWidth || imgWidth == 0 || isNaN(imgWidth)) {
                imgWidth = 1;
            }

            if (listHas(adaptiveImage.classList, 'slide')) {
                imgWidth = 1;
            }
        }

        if (jsDebug) {
            console.log('Image Stuff 2');
            console.log(adaptiveImage.parentElement.offsetWidth);
            console.log(adaptiveImage.offsetWidth);
            console.log(imageStyle);
            console.log(imageStyle.width);
            console.log(parseInt(imageStyle.width));
            console.log(Math.round(parseInt(imageStyle.width)));
            console.log(imgWidth);
            console.log('Image Stuff END');
        }



        /**
         * Setup Image SRC only if srcset is empty
         */
        if ((typeof adaptiveImage.dataset.src !== 'undefined' && adaptiveImage.dataset.src != '')) {
            newApiURL = adaptiveImage.dataset.src;

            newApiURL = SetupNewApiURL(newApiURL, imgWidth, adaptiveImage);

            adaptiveImage.src = newApiURL;
            if (typeof adaptiveImage.dataset.srcset !== 'undefined' && adaptiveImage.dataset.src != '') {
                adaptiveImage.srcset = adaptiveImage.dataset.srcset;
            }
        }
        else if (typeof adaptiveImage.src !== 'undefined' && adaptiveImage.src != '') {
            newApiURL = adaptiveImage.src;

            newApiURL = SetupNewApiURL(newApiURL, imgWidth, adaptiveImage);

            adaptiveImage.src = newApiURL;
            if (typeof adaptiveImage.dataset.srcset !== 'undefined' && adaptiveImage.dataset.src != '') {
                adaptiveImage.srcset = adaptiveImage.dataset.srcset;
            }
        }

        adaptiveImage.classList.add("ic-fade-in");
        adaptiveImage.classList.remove("wps-ic-lazy-image");

        adaptiveImage.removeAttribute('data-srcset');

        srcSetAPI = '';
        if (typeof adaptiveImage.srcset !== 'undefined' && adaptiveImage.srcset != '') {
            srcSetAPI = newApiURL = adaptiveImage.srcset;

            if (jsDebug) {
                console.log('Image has srcset');
                console.log(adaptiveImage.srcset);
                console.log(newApiURL);
            }

            newApiURL = SetupNewApiURL(newApiURL, 0, adaptiveImage);

            adaptiveImage.srcset = newApiURL;
        }
        else if (typeof adaptiveImage.dataset.srcset !== 'undefined' && adaptiveImage.dataset.srcset != '') {
            srcSetAPI = newApiURL = adaptiveImage.dataset.srcset;
            if (jsDebug) {
                console.log('Image does not have srcset');
                console.log(newApiURL);
            }

            newApiURL = SetupNewApiURL(newApiURL, 0, adaptiveImage);

            adaptiveImage.srcset = newApiURL;
        }


    });

}

document.addEventListener("DOMContentLoaded", function () {
    removeElementorInvisible();
    runAdaptive();

    // Start observing the entire document body for changes
    //wpcObserver.observe(document.body, { childList: true, subtree: true });
});

const wpcObserver = new MutationObserver(function (mutationsList) {
    // Iterate over each mutation
    for (var i = 0; i < mutationsList.length; i++) {
        console.log('running observer');
        var mutation = mutationsList[i];

        // Check if nodes were added
        if (
            mutation.type === 'childList' &&
            mutation.addedNodes.length > 0 &&
            mutation.addedNodes[0].tagName &&
            mutation.addedNodes[0].tagName.toLowerCase() === 'img'
        ) {
            // Process the added nodes
            for (var j = 0; j < mutation.addedNodes.length; j++) {
                var node = mutation.addedNodes[j];

                // Check if the added node is an image
                if (node.tagName && node.tagName.toLowerCase() === 'img') {
                    adaptiveImage = node;
                    /**
                     * Setup Image SRC only if srcset is empty
                     */
                    if ((typeof adaptiveImage.dataset.src !== 'undefined' && adaptiveImage.dataset.src != '')) {
                        newApiURL = adaptiveImage.dataset.src;

                        newApiURL = SetupNewApiURL(newApiURL, imgWidth, adaptiveImage);

                        adaptiveImage.src = newApiURL;
                        if (typeof adaptiveImage.dataset.srcset !== 'undefined' && adaptiveImage.dataset.src != '') {
                            adaptiveImage.srcset = adaptiveImage.dataset.srcset;
                        }
                    }
                    else if (typeof adaptiveImage.src !== 'undefined' && adaptiveImage.src != '') {
                        newApiURL = adaptiveImage.src;

                        newApiURL = SetupNewApiURL(newApiURL, imgWidth, adaptiveImage);

                        adaptiveImage.src = newApiURL;
                        if (typeof adaptiveImage.dataset.srcset !== 'undefined' && adaptiveImage.dataset.src != '') {
                            adaptiveImage.srcset = adaptiveImage.dataset.srcset;
                        }
                    }

                    adaptiveImage.classList.add("ic-fade-in");
                    adaptiveImage.classList.remove("wps-ic-lazy-image");

                    adaptiveImage.removeAttribute('data-srcset');

                    srcSetAPI = '';
                    if (typeof adaptiveImage.srcset !== 'undefined' && adaptiveImage.srcset != '') {
                        srcSetAPI = newApiURL = adaptiveImage.srcset;

                        if (jsDebug) {
                            console.log('Image has srcset');
                            console.log(adaptiveImage.srcset);
                            console.log(newApiURL);
                        }

                        newApiURL = SetupNewApiURL(newApiURL, 0, adaptiveImage);

                        adaptiveImage.srcset = newApiURL;
                    }
                    else if (typeof adaptiveImage.dataset.srcset !== 'undefined' && adaptiveImage.dataset.srcset != '') {
                        srcSetAPI = newApiURL = adaptiveImage.dataset.srcset;
                        if (jsDebug) {
                            console.log('Image does not have srcset');
                            console.log(newApiURL);
                        }

                        newApiURL = SetupNewApiURL(newApiURL, 0, adaptiveImage);

                        adaptiveImage.srcset = newApiURL;
                    }
                }
            }
        }
    }
});

// if ('undefined' !== typeof jQuery) {
//     // jQuery(document).on('elementor/popup/show', function () {
//     //     runAdaptive();
//     // });
// }