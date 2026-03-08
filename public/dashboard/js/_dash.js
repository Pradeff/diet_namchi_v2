// Declare the $ variable before using it
/*const $ = require("jquery")
const bootstrap = require("bootstrap") */

$(document).ready(() => {


    // Theme toggle
    $("#themeToggle").click(function () {
        const html = $("html")
        const currentTheme = html.attr("data-theme")
        const newTheme = currentTheme === "light" ? "dark" : "light"

        html.attr("data-theme", newTheme)

        // Update icon with smooth transition
        const icon = $(this).find("i")
        icon.css("transform", "scale(0)")

        setTimeout(() => {
            if (newTheme === "dark") {
                icon.removeClass("bi-sun-fill").addClass("bi-moon-fill")
            } else {
                icon.removeClass("bi-moon-fill").addClass("bi-sun-fill")
            }
            icon.css("transform", "scale(1)")
        }, 150)

        // Save theme preference (using window object instead of localStorage)
        window.themeState = { theme: newTheme }
    })

    // Load saved theme
    const savedTheme = window.themeState?.theme || "light"
    $("html").attr("data-theme", savedTheme)
    if (savedTheme === "dark") {
        $("#themeToggle i").removeClass("bi-sun-fill").addClass("bi-moon-fill")
    }
    else {
        $("#themeToggle i").removeClass("bi-moon-fill").addClass("bi-sun-fill")
    }
})
