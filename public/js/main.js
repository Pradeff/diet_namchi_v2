function toggleMobileSubmenu(element) {
    event.preventDefault();
    event.stopPropagation();
    const submenu = element.nextElementSibling;
    const icon = element.querySelector(".dropdown-icon");

    submenu.classList.toggle("show");
    icon.classList.toggle("rotate");
}

document.querySelectorAll(".mobile-nav li a:not(.dropdown-toggle-mobile)").forEach((link) => {
    link.addEventListener("click", function () {
        const offcanvasEl = document.getElementById("mobileMenu");
        const offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl);
        if (offcanvasInstance) offcanvasInstance.hide();
    });
});

Fancybox.bind("[data-fancybox='gallery']", {});

AOS.init({
    duration: 800,
    easing: "ease-in-out",
    once: true,
    offset: 100
});

$(".hero-slider").slick({
    dots: true,
    infinite: true,
    speed: 1000,
    fade: true,
    cssEase: "linear",
    autoplay: true,
    autoplaySpeed: 5000,
    arrows: true,
    prevArrow:
        "<button type='button' class='slick-prev'><i class='bi bi-arrow-left'></i></button>",
    nextArrow:
        "<button type='button' class='slick-next'><i class='bi bi-arrow-right'></i></button>",
    responsive: [
        {
            breakpoint: 768,
            settings: {
                arrows: false
            }
        }
    ]
});


const counters = document.querySelectorAll(".stat-number");
const speed = 200;

function animateCounters() {
    counters.forEach((counter) => {
        const target = +counter.getAttribute("data-target");
        const count = +counter.innerText;
        const inc = target / speed;

        if (count < target) {
            counter.innerText = Math.ceil(count + inc);
            setTimeout(animateCounters, 20);
        } else {
            counter.innerText = target;
        }
    });
}

const observerOptions = {
    threshold: 0.5
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            animateCounters();
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

const statsStrip = document.querySelector(".stats-section");
if (statsStrip) observer.observe(statsStrip);

const backToTop = $("#backToTop");
$(window).on("scroll", function () {
    if ($(this).scrollTop() > 300) {
        backToTop.addClass("active");
    } else {
        backToTop.removeClass("active");
    }
});

backToTop.on("click", function () {
    $("html, body").animate({scrollTop: 0}, 800);
    return false;
});
