/* ══════════════════════════════════════════════════════════════════════
   main.js  —  DIET Namchi
   All initialisations are wrapped in initPage() so they run on both:
     • Initial hard load  (DOMContentLoaded / document.ready)
     • HTMX soft swap     (dietPageLoaded custom event)
══════════════════════════════════════════════════════════════════════ */

/* ── 1. Mobile submenu accordion ──────────────────────────────────── */
function toggleMobileSubmenu(element) {
    event.preventDefault();
    event.stopPropagation();

    const submenu = element.nextElementSibling;
    const icon    = element.querySelector('.dropdown-icon');

    // Close all other open submenus
    document.querySelectorAll('.mobile-submenu.show').forEach(function (s) {
        if (s !== submenu) s.classList.remove('show');
    });
    document.querySelectorAll('.dropdown-icon.rotate').forEach(function (i) {
        if (i !== icon) i.classList.remove('rotate');
    });

    submenu.classList.toggle('show');
    icon.classList.toggle('rotate');
}

/* ── 2. Close offcanvas when a mobile nav link is clicked ─────────── */
function bindMobileNavClose() {
    document.querySelectorAll('.mobile-nav li a:not(.dropdown-toggle-mobile)').forEach(function (link) {
        link.addEventListener('click', function () {
            var offcanvasEl       = document.getElementById('mobileMenu');
            var offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (offcanvasInstance) offcanvasInstance.hide();
        });
    });
}

/* ── 3. Fancybox ──────────────────────────────────────────────────── */
function initFancybox() {
    if (typeof Fancybox === 'undefined') return;
    // Destroy previous bindings to avoid duplicate handlers
    Fancybox.destroy();
    Fancybox.bind("[data-fancybox='gallery']", {});
}

/* ── 4. AOS ───────────────────────────────────────────────────────── */
function initAOS() {
    if (typeof AOS === 'undefined') return;
    AOS.init({
        duration : 800,
        easing   : 'ease-in-out',
        once     : true,
        offset   : 100
    });
    // Re-refresh so newly injected elements pick up their animations
    AOS.refresh();
}

/* ── 5. Slick sliders ─────────────────────────────────────────────── */
function initSliders() {
    if (typeof $.fn.slick === 'undefined') return;

    // Helper: safely destroy a slick instance before re-init
    function safeInit($el, options) {
        if (!$el.length) return;
        if ($el.hasClass('slick-initialized')) {
            try { $el.slick('unslick'); } catch (e) {}
        }
        $el.slick(options);
    }

    // Hero slider
    safeInit($('.hero-slider'), {
        dots          : true,
        infinite      : true,
        speed         : 1000,
        fade          : true,
        cssEase       : 'linear',
        autoplay      : true,
        autoplaySpeed : 5000,
        arrows        : true,
        prevArrow     : "<button type='button' class='slick-prev'><i class='bi bi-arrow-left'></i></button>",
        nextArrow     : "<button type='button' class='slick-next'><i class='bi bi-arrow-right'></i></button>",
        responsive    : [
            { breakpoint: 768, settings: { arrows: false } }
        ]
    });

    // Homepage staff slider
    safeInit($('.hs-slider'), {
        slidesToShow   : 5,
        slidesToScroll : 1,
        autoplay       : true,
        autoplaySpeed  : 3200,
        arrows         : true,
        dots           : true,
        infinite       : true,
        pauseOnHover   : true,
        prevArrow     : "<button type='button' class='slick-prev'><i class='bi bi-arrow-left'></i></button>",
        nextArrow     : "<button type='button' class='slick-next'><i class='bi bi-arrow-right'></i></button>",
        responsive     : [
            { breakpoint: 1200, settings: { slidesToShow: 3 } },
            { breakpoint: 992,  settings: { slidesToShow: 2 } },
            { breakpoint: 576,  settings: { slidesToShow: 2, arrows: false } }
        ]
    });

    // Vertical pagination slider for notices (2 items visible)
    safeInit($('.notices-slider'), {
        dots          : true,
        infinite      : true,
        speed         : 800,
        slidesToShow  : 2,
        slidesToScroll: 1,
        cssEase       : 'ease',
        autoplay      : true,
        autoplaySpeed : 4000,
        arrows        : true,
        prevArrow     : "<button type='button' class='slick-prev'><i class='bi bi-arrow-left'></i></button>",
        nextArrow     : "<button type='button' class='slick-next'><i class='bi bi-arrow-right'></i></button>",
        responsive    : [
            {
                breakpoint: 992,
                settings: {
                    slidesToShow: 1,
                    arrows: false
                }
            }
        ]
    });
}



/* ── 6. Stats counter ─────────────────────────────────────────────── */
function initCounters() {
    var counters = document.querySelectorAll('.stat-number');
    if (!counters.length) return;

    var speed = 200;

    function animateCounters() {
        counters.forEach(function (counter) {
            var target = +counter.getAttribute('data-target');
            var count  = +counter.innerText;
            var inc    = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(animateCounters, 20);
            } else {
                counter.innerText = target;
            }
        });
    }

    var statsStrip = document.querySelector('.stats-section');
    if (!statsStrip) return;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    observer.observe(statsStrip);
}

/* ── 7. Back-to-top button ────────────────────────────────────────── */
function initBackToTop() {
    var backToTop = $('#backToTop');
    if (!backToTop.length) return;

    // Remove old scroll listener before adding a new one (prevents stacking)
    $(window).off('scroll.backToTop').on('scroll.backToTop', function () {
        if ($(this).scrollTop() > 300) {
            backToTop.addClass('active');
        } else {
            backToTop.removeClass('active');
        }
    });

    backToTop.off('click.backToTop').on('click.backToTop', function () {
        $('html, body').animate({ scrollTop: 0 }, 800);
        return false;
    });
}

/* ══════════════════════════════════════════════════════════════════════
   initPage()  —  called on every page load (hard or HTMX swap)
══════════════════════════════════════════════════════════════════════ */
function initPage() {
    initFancybox();
    initAOS();
    initSliders();
    initCounters();
    initBackToTop();
    bindMobileNavClose();
}

/* ══════════════════════════════════════════════════════════════════════
   Bootstrap
   • Hard load  →  jQuery ready
   • HTMX swap  →  dietPageLoaded custom event (fired in base.html.twig)
══════════════════════════════════════════════════════════════════════ */
$(document).ready(function () {
    initPage();
});

$(document).on('dietPageLoaded', function () {
    initPage();
});
