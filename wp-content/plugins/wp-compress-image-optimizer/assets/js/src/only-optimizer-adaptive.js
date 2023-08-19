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