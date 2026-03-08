(function() {
    try {
        var color = localStorage.getItem('primaryColor');
        if (color) {
            // Set main theme color CSS variables immediately, matching your theme-customizer.js logic
            var rgb = color.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/) ? [
                parseInt(color.slice(1,3).length === 2 ? color.slice(1,3) : color.slice(1,2).repeat(2), 16),
                parseInt(color.slice(3,5).length === 2 ? color.slice(3,5) : color.slice(2,3).repeat(2), 16),
                parseInt(color.slice(5,7).length === 2 ? color.slice(5,7) : color.slice(3,4).repeat(2), 16)
            ] : [6, 111, 209];
            document.documentElement.style.setProperty('--theme-color', color);
            document.documentElement.style.setProperty('--theme-color_rgb', rgb.join(', '));
            // Optionally handle shadeColor / lighter/darker
            function shadeColor(hex, percent) {
                let h = hex.replace('#', '');
                if (h.length === 3) h = h[0]+h+h[1]+h[1]+h[2]+h[2];
                let R = parseInt(h.substring(0,2), 16);
                let G = parseInt(h.substring(2,4), 16);
                let B = parseInt(h.substring(4,6), 16);
                R = parseInt(R * (100 + percent) / 100); R = (R < 255) ? R : 255;
                G = parseInt(G * (100 + percent) / 100); G = (G < 255) ? G : 255;
                B = parseInt(B * (100 + percent) / 100); B = (B < 255) ? B : 255;
                const RR = (R.toString(16).length === 1) ? "0" + R.toString(16) : R.toString(16);
                const GG = (G.toString(16).length === 1) ? "0" + G.toString(16) : G.toString(16);
                const BB = (B.toString(16).length === 1) ? "0" + B.toString(16) : B.toString(16);
                return "#" + RR + GG + BB;
            }
            document.documentElement.style.setProperty('--theme-color-hover', shadeColor(color, -15));
            document.documentElement.style.setProperty('--theme-color-light', color + '1A');
            document.documentElement.style.setProperty('--theme-color-subtle', color + '0D');
            document.documentElement.style.setProperty('--bs-primary', color);
            document.documentElement.style.setProperty('--bs-primary-rgb', rgb.join(', '));
        }
    } catch(e){}
})();
