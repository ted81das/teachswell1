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