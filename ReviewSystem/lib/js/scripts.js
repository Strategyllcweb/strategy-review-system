const locationsNames = [
    '1',
    '2',
    '3',
    '4',
    '5'
];

let stars = [];
let reviewPages = [];
let currentPage;
let rating;
let prev;
let next;
let googleReviewSection;
let facebookReviewSection;
let yelpReviewSection;
let selectedLocation;
let prefilled = false;

function prefillLocation( location ) {
    let locationSelect = document.getElementById('content__location--select');
    locationSelect.value = location.replace(/\//g, '');
}

function fillFormLocation() {
    if ( feedbackFormId ) {
        document.getElementById('input_' + feedbackFormId + '_1').value = selectedLocation;
    }
}

function fillFormRating() {
    if ( feedbackFormId ) {
        document.getElementById('input_' + feedbackFormId + '_2').value = rating;
    }
}

function changePage( newPage ) {
    if ( currentPage ) {
        hideElement( currentPage );
    }
    showElement( newPage );
    currentPage = newPage;
}

function hideElement( element ) {
    element.style.display = 'none';
}

function showElement( element ) {
    element.style.display = 'block';
}

async function concealElement( element ) {
    element.style.visibility = 'hidden';
}

async function revealElement( element ) {
    element.style.visibility = 'visible';
}

function clearStars() {
    for ( let i = 0; i < stars.length; i++ ) {
        stars[i].checked = false;
    }
}

function fillStars( num ) {
    for ( let i = 0; i < num; i++ ) {
        stars[i].checked = true;
    }
    rating = num;
}

async function handleNext() {
    let page = parseInt(currentPage.dataset.value);
    switch (page) {
        case 1:
            if(rating && 0 < rating) {
                await revealElement(next);
            }
            await revealElement(prev);
            changePage(reviewPages[1]);
            break;
        case 2:
            await revealElement(prev);
            await concealElement(next);
            if ( rating > 3 ) {
                changeVendorLinks();
                changePage(reviewPages[2]);
            }
            else if ( document.getElementById('gform_fields_' + feedbackFormId) ) {
                fillFormLocation();
                fillFormRating();
                changePage(reviewPages[3]);
            }
            else {
                await concealElement(prev);
                changePage(reviewPages[4]);
            }
            break;
        default:
            return;
    }
}

async function handlePrev() {
    let page = parseInt(currentPage.dataset.value);
    switch (page) {
        case 2:
            if ( !prefilled ) {
                await concealElement(prev);
                await revealElement(next);
                changePage(reviewPages[0]);
            }
            break;
        case 3:
            if ( prefilled ) {
                await concealElement(prev);
            }
            await revealElement(next);
            changePage(reviewPages[1]);
            break;
        case 4:
            if ( prefilled ) {
                await concealElement(prev);
            }
            await revealElement(next);
            changePage(reviewPages[1]);
            break;
        default:
            return;
    }
}

function handleDataLayer({rating, location, vendor}) {
    if ( undefined !== rating && null !== rating && undefined !== location && null !== location ) {
        window.dataLayer = window.dataLayer || [];
        if ( undefined !== vendor && null !== vendor ) {
            window.dataLayer.push({
                'event': 'reviewSystemVendorClick',
                'location': location,
                'rating': rating,
                'vendor': vendor
            });
        }
        else {
            window.dataLayer.push({
                'event': 'reviewSystemFeedbackSubmit',
                'location': location,
                'rating': rating
            });
        }
    }
}

function changeVendorLinks() {
    if ( undefined !== locationReviewLinks && null !== locationReviewLinks ) {
        let googleLink = googleReviewSection.querySelector('a');
        let facebookLink = facebookReviewSection.querySelector('a');
        let yelpLink = yelpReviewSection.querySelector('a');

        if ( null !== locationReviewLinks[selectedLocation].google && '' !== locationReviewLinks[selectedLocation].google.trim() ) {
            googleLink.href = locationReviewLinks[selectedLocation].google;
            showElement(googleReviewSection);
        }
        else {
            hideElement(googleReviewSection);
            googleLink.href = '';
        }

        if ( null !== locationReviewLinks[selectedLocation].facebook && '' !== locationReviewLinks[selectedLocation].facebook.trim() ) {
            facebookLink.href = locationReviewLinks[selectedLocation].facebook;
            showElement(facebookReviewSection);
        }
        else {
            hideElement(facebookReviewSection);
            facebookLink.href = '';
        }

        if ( null !== locationReviewLinks[selectedLocation].yelp && '' !== locationReviewLinks[selectedLocation].yelp.trim() ) {
            yelpLink.href = locationReviewLinks[selectedLocation].yelp;
            showElement(yelpReviewSection);
        }
        else {
            hideElement(yelpReviewSection);
            yelpLink.href = '';
        }
    }
}

function initReviewSystem() {

    reviewPages = document.getElementsByClassName('review-content__page');
    stars = document.getElementsByClassName('review-star');
    prev = document.getElementById('review-prev-btn');
    next = document.getElementById('review-next-btn');
    googleReviewSection = document.getElementById('review-google');
    facebookReviewSection = document.getElementById('review-facebook');
    yelpReviewSection = document.getElementById('review-yelp');

    hideElement(googleReviewSection);
    hideElement(facebookReviewSection);
    hideElement(yelpReviewSection);

    let urlParams = new URLSearchParams( window.location.search );
    let pathname = window.location.pathname.replace(/\//g, '');

    // If on a locations page, prefill location
    if ( locationsNames.includes( pathname ) ) {
        selectedLocation = pathname;
    }
    else if ( urlParams.has('location') && locationsNames.includes( urlParams.get('location') ) ) {
        selectedLocation = urlParams.get('location');
    }

    if ( selectedLocation ) {
        prefillLocation(selectedLocation);
        prefilled = true;
        currentPage = reviewPages[1];
    }
    else {
        currentPage = reviewPages[0];
    }

    showElement(currentPage);
}

jQuery(document).ready(function () {

    if ( document.getElementById('review-modal') ) {
        MicroModal.init();
    }

    if ( document.getElementById('review-content') ) {
        initReviewSystem();
    }

    jQuery('#review-prev-btn').on('click', function() {
        handlePrev();
    });

    jQuery('#review-next-btn').on('click', function() {
        handleNext();
    });

    jQuery('#content__location--select').change(function() {
        selectedLocation = this.value;
        changeVendorLinks();
        revealElement(next);
    });

    jQuery('.review-star ~ label').on('click', function() {
        let starNum = parseInt(document.getElementById(this.getAttribute('for')).value);
        clearStars();
        fillStars(starNum);
        revealElement(next);
    });

    // Form Submit Click
    jQuery(document).on('gform_confirmation_loaded', function(event, formId) {
        if (formId === feedbackFormId) {
            hideElement(document.getElementById('review-prev-btn').parentElement);
            changePage(reviewPages[4]);
            handleDataLayer({
                location: selectedLocation,
                rating: rating
            });
        }
    })

    // Review Vendor Link Click
    jQuery('.review-vendor__link').on('click', function() {
        handleDataLayer({
            vendor: this.getAttribute('data-vendor'), 
            location: selectedLocation, 
            rating: rating
        });
    });

    // Modal Open on Button Click
    jQuery('.review-modal-open').on('click', function() {
        MicroModal.show('review-modal');
    });
});