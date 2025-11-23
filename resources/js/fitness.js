/**
 * Fitness Template JavaScript
 * Handles fitness/yoga template-specific interactions
 */

// Initialize AOS (Animate On Scroll)
if (typeof AOS !== 'undefined') {
    AOS.init({
        duration: 800,
        easing: 'slide'
    });
}

// Wait for DOM and jQuery to be ready
document.addEventListener('DOMContentLoaded', function() {
    initFitnessTemplate();
});

function initFitnessTemplate() {
    // Initialize any vanilla JS functionality here
    // Most functionality is handled by jQuery plugins below
}

// If jQuery is available, initialize additional features
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        "use strict";

        // Stellar.js for parallax effects
        if (typeof $.fn.stellar !== 'undefined') {
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

        // Scrollax initialization
        if (typeof $.Scrollax !== 'undefined') {
            $.Scrollax();
        }

        // Full height elements
        var fullHeight = function() {
            $('.js-fullheight').css('height', $(window).height());
            $(window).resize(function(){
                $('.js-fullheight').css('height', $(window).height());
            });
        };
        fullHeight();

        // Loader
        var loader = function() {
            setTimeout(function() { 
                if($('#ftco-loader').length > 0) {
                    $('#ftco-loader').removeClass('show');
                }
            }, 1);
        };
        loader();

        // Carousel initialization
        var carousel = function() {
            if (typeof $.fn.owlCarousel !== 'undefined') {
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
                        0: {
                            items: 1
                        },
                        600: {
                            items: 1
                        },
                        1000: {
                            items: 1
                        }
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
                        0: {
                            items: 1
                        },
                        600: {
                            items: 3
                        },
                        1000: {
                            items: 3
                        }
                    }
                });
            }
        };
        carousel();

        // Dropdown hover
        $('nav .dropdown').hover(function(){
            var $this = $(this);
            $this.addClass('show');
            $this.find('> a').attr('aria-expanded', true);
            $this.find('.dropdown-menu').addClass('show');
        }, function(){
            var $this = $(this);
            $this.removeClass('show');
            $this.find('> a').attr('aria-expanded', false);
            $this.find('.dropdown-menu').removeClass('show');
        });

        // Magnific Popup for gallery
        if (typeof $.fn.magnificPopup !== 'undefined') {
            $('.image-popup').magnificPopup({
                type: 'image',
                closeOnContentClick: true,
                closeBtnInside: false,
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
        }

        // Counter animation
        if (typeof $.fn.animateNumber !== 'undefined') {
            $('.number').each(function() {
                var $this = $(this),
                    countTo = $this.attr('data-number');
                
                $({ countNum: $this.text()}).animate({
                    countNum: countTo
                }, {
                    duration: 2000,
                    easing: 'linear',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });
        }

        // Date and time pickers
        if (typeof $.fn.datepicker !== 'undefined') {
            $('.date').datepicker();
        }
        if (typeof $.fn.timepicker !== 'undefined') {
            $('.time').timepicker();
        }
    });
}

