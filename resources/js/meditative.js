/**
 * Meditative Template JavaScript
 * Based on the original meditative-master template
 * Includes all necessary JavaScript dependencies and initialization
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize meditative template features
    initMeditativeTemplate();
});

// Import jQuery and dependencies (these will be loaded via CDN or bundled)
function initMeditativeTemplate() {
    // Check if jQuery is available
    if (typeof jQuery === 'undefined') {
        console.warn('jQuery is required for meditative template. Loading from CDN...');
        loadScript('https://code.jquery.com/jquery-3.2.1.min.js', function() {
            loadMeditativeScripts();
        });
    } else {
        loadMeditativeScripts();
    }
}

function loadScriptsSequentially(scripts, index, callback) {
    if (index >= scripts.length) {
        callback();
        return;
    }
    
    const scriptItem = scripts[index];
    const src = typeof scriptItem === 'string' ? scriptItem : scriptItem.src;
    
    loadScript(src, function() {
        loadScriptsSequentially(scripts, index + 1, callback);
    });
}

function loadScript(src, callback) {
    // Check if script is already loaded
    const existingScript = document.querySelector(`script[src="${src}"]`);
    if (existingScript) {
        callback();
        return;
    }
    
    const script = document.createElement('script');
    script.src = src;
    script.onload = callback;
    script.onerror = function() {
        console.warn('Failed to load script:', src);
        callback(); // Continue even if script fails
    };
    document.head.appendChild(script);
}

function loadMeditativeScripts() {
    // If scripts are already loaded, just initialize
    if (window.AOS && window.jQuery && window.jQuery.fn.owlCarousel) {
        initializeMeditativeTemplate();
        return;
    }
    
    // Load scripts dynamically - scripts are loaded via script tags in the blade template
    // So we just need to wait for them to load and then initialize
    const checkScripts = setInterval(function() {
        if (window.jQuery && window.AOS) {
            clearInterval(checkScripts);
            initializeMeditativeTemplate();
        }
    }, 100);
    
    // Timeout after 10 seconds
    setTimeout(function() {
        clearInterval(checkScripts);
        if (window.jQuery) {
            initializeMeditativeTemplate();
        } else {
            console.warn('Meditative template scripts failed to load');
        }
    }, 10000);
}

function initializeMeditativeTemplate() {
    const $ = window.jQuery;
    
    if (!$) {
        console.error('jQuery is required for meditative template');
        return;
    }
    
    // Initialize AOS (Animate On Scroll)
    if (window.AOS) {
        AOS.init({
            duration: 800,
            easing: 'slide'
        });
    }
    
    // Initialize Stellar.js for parallax
    if ($.fn.stellar) {
        $(window).stellar({
            responsive: true,
            parallaxBackgrounds: true,
            parallaxElements: true,
            horizontalScrolling: false,
            hideDistantElements: false,
            scrollProperty: 'scroll',
            horizontalOffset: 0,
            verticalOffset: 0
        });
    }
    
    // Initialize Scrollax
    if ($.Scrollax) {
        $.Scrollax();
    }
    
    // Full height elements
    function fullHeight() {
        $('.js-fullheight').css('height', $(window).height());
        $(window).resize(function(){
            $('.js-fullheight').css('height', $(window).height());
        });
    }
    fullHeight();
    
    // Loader removed - no longer needed
    
    // Initialize carousels
    if ($.fn.owlCarousel) {
        $('.home-slider').owlCarousel({
            loop: true,
            autoplay: true,
            margin: 0,
            animateOut: 'fadeOut',
            animateIn: 'fadeIn',
            nav: false,
            autoplayHoverPause: false,
            items: 1,
            navText: ["<span class='ion-md-arrow-back'></span>","<span class='ion-chevron-right'></span>"],
            responsive: {
                0: { items: 1 },
                600: { items: 1 },
                1000: { items: 1 }
            }
        });
        
        $('.carousel-testimony').owlCarousel({
            center: true,
            loop: true,
            items: 1,
            margin: 30,
            stagePadding: 0,
            nav: false,
            navText: ['<span class="ion-ios-arrow-back">', '<span class="ion-ios-arrow-forward">'],
            responsive: {
                0: { items: 1 },
                600: { items: 3 },
                1000: { items: 3 }
            }
        });
    }
    
    // Navbar scroll effect
    var scrollWindow = function() {
        $(window).scroll(function(){
            var $w = $(this),
                st = $w.scrollTop(),
                navbar = $('.ftco_navbar'),
                sd = $('.js-scroll-wrap');
            
            if (st > 150) {
                if (!navbar.hasClass('scrolled')) {
                    navbar.addClass('scrolled');
                }
            } 
            if (st < 150) {
                if (navbar.hasClass('scrolled')) {
                    navbar.removeClass('scrolled sleep');
                }
            } 
            if (st > 350) {
                if (!navbar.hasClass('awake')) {
                    navbar.addClass('awake');
                }
                if(sd.length > 0) {
                    sd.addClass('sleep');
                }
            }
            if (st < 350) {
                if (navbar.hasClass('awake')) {
                    navbar.removeClass('awake');
                    navbar.addClass('sleep');
                }
                if(sd.length > 0) {
                    sd.removeClass('sleep');
                }
            }
        });
    };
    scrollWindow();
    
    // Counter animation
    if ($.fn.animateNumber && $.fn.waypoint) {
        $('#section-counter').waypoint(function(direction) {
            if(direction === 'down' && !$(this.element).hasClass('ftco-animated')) {
                var comma_separator_number_step = $.animateNumber.numberStepFactories.separator(',');
                $('.number').each(function(){
                    var $this = $(this),
                        num = $this.data('number');
                    $this.animateNumber({
                        number: num,
                        numberStep: comma_separator_number_step
                    }, 7000);
                });
            }
        }, { offset: '95%' });
    }
    
    // Content waypoint animations
    if ($.fn.waypoint) {
        var i = 0;
        $('.ftco-animate').waypoint(function(direction) {
            if(direction === 'down' && !$(this.element).hasClass('ftco-animated')) {
                i++;
                $(this.element).addClass('item-animate');
                setTimeout(function(){
                    $('body .ftco-animate.item-animate').each(function(k){
                        var el = $(this);
                        setTimeout(function() {
                            var effect = el.data('animate-effect');
                            if (effect === 'fadeIn') {
                                el.addClass('fadeIn ftco-animated');
                            } else if (effect === 'fadeInLeft') {
                                el.addClass('fadeInLeft ftco-animated');
                            } else if (effect === 'fadeInRight') {
                                el.addClass('fadeInRight ftco-animated');
                            } else {
                                el.addClass('fadeInUp ftco-animated');
                            }
                            el.removeClass('item-animate');
                        }, k * 50, 'easeInOutExpo');
                    });
                }, 100);
            }
        }, { offset: '95%' });
    }
    
    // Magnific Popup
    if ($.fn.magnificPopup) {
        $('.image-popup').magnificPopup({
            type: 'image',
            closeOnContentClick: true,
            closeBtnInside: true,
            fixedContentPos: true,
            mainClass: 'mfp-no-margins mfp-with-zoom',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [0,1]
            },
            image: {
                verticalFit: true
            },
            zoom: {
                enabled: true,
                duration: 300
            }
        });
        
        $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
            disableOn: 700,
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
        });
    }
    
    // Date and time pickers
    if ($.fn.datepicker) {
        $('.appointment_date').datepicker({
            'format': 'm/d/yyyy',
            'autoclose': true
        });
    }
    
    if ($.fn.timepicker) {
        $('.appointment_time').timepicker();
    }
}

// Helper function to get asset URL
function asset(path) {
    // In Laravel, use the asset() helper via a global variable
    if (window.Laravel && window.Laravel.asset) {
        return window.Laravel.asset(path);
    }
    return '/' + path.replace(/^\//, '');
}
