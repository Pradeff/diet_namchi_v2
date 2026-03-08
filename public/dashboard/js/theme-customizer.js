class ThemeCustomizer {
    constructor() {
        this.elements = {};
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => this.init());
        } else {
            this.init();
        }
    }

    init() {
        this.bindElements();
        this.bindEvents();
        this.loadPreferences();
        this.updateThemeIcon();
        this.setupKeyboardShortcuts();
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => this.handleSystemThemeChange(e));
        }
    }

    bindElements() {
        this.elements = {
            customizerBtn: document.getElementById('themeCustomizerBtn'),
            customizer: document.getElementById('themeCustomizer'),
            closeBtn: document.getElementById('closeCustomizer'),
            resetBtn: document.getElementById('resetCustomizer'),
            colorOptions: document.querySelectorAll('.color-option'),
            themeRadios: document.querySelectorAll('input[name="themeMode"]'),
            reducedMotion: document.getElementById('reducedMotion'),
            roundedCorners: document.getElementById('roundedCorners'),
            boxedLayout: document.getElementById('boxedLayout'),
            compactSidebar: document.getElementById('compactSidebar'),
            stickyHeader: document.getElementById('stickyHeader'),
            themeToggleBtn: document.getElementById('themeToggle')
        };
    }

    bindEvents() {
        if (this.elements.customizerBtn)
            this.elements.customizerBtn.addEventListener('click', () => this.toggleCustomizer());
        if (this.elements.closeBtn)
            this.elements.closeBtn.addEventListener('click', () => this.closeCustomizer());
        document.addEventListener('click', (e) => this.handleOutsideClick(e));
        this.elements.themeRadios.forEach(radio => {
            radio.addEventListener('change', () => this.handleThemeChange(radio));
        });
        this.elements.colorOptions.forEach(option => {
            option.addEventListener('click', () => this.handleColorChange(option, { silent: false }));
        });
        if (this.elements.reducedMotion)
            this.elements.reducedMotion.addEventListener('change', (e) => this.handleReducedMotion(e));
        if (this.elements.roundedCorners)
            this.elements.roundedCorners.addEventListener('change', (e) => this.handleRoundedCorners(e));
        if (this.elements.boxedLayout)
            this.elements.boxedLayout.addEventListener('change', (e) => this.handleBoxedLayout(e));
        if (this.elements.compactSidebar)
            this.elements.compactSidebar.addEventListener('change', (e) => this.handleCompactSidebar(e));
        if (this.elements.stickyHeader)
            this.elements.stickyHeader.addEventListener('change', (e) => this.handleStickyHeader(e));
        if (this.elements.resetBtn)
            this.elements.resetBtn.addEventListener('click', () => this.handleReset());
        const fs = document.getElementById('fullscreenToggle');
        if (fs)
            fs.addEventListener('click', () => this.toggleFullscreen());
        document.addEventListener('change', (e) => {
            if (e.target.id === 'importSettings' && e.target.files[0]) {
                this.importSettings(e.target.files);
            }
        });
    }

    toggleCustomizer() {
        if (!this.elements.customizer) return;
        this.elements.customizer.classList.toggle('open');
        document.body.style.overflow = this.elements.customizer.classList.contains('open') ? 'hidden' : '';
    }

    closeCustomizer() {
        if (!this.elements.customizer) return;
        this.elements.customizer.classList.remove('open');
        document.body.style.overflow = '';
    }

    handleOutsideClick(e) {
        if (!this.elements.customizer || !this.elements.customizerBtn) return;
        if (
            this.elements.customizer.classList.contains('open') &&
            !this.elements.customizer.contains(e.target) &&
            !this.elements.customizerBtn.contains(e.target)
        ) {
            this.closeCustomizer();
        }
    }

    handleThemeChange(radio) {
        if (radio.value === 'auto') {
            document.documentElement.removeAttribute('data-theme');
            localStorage.removeItem('theme');
            this.handleSystemThemeChange(window.matchMedia('(prefers-color-scheme: dark)'));
        } else {
            document.documentElement.setAttribute('data-theme', radio.value);
            localStorage.setItem('theme', radio.value);
        }
        this.updateThemeIcon();
        this.showToast('Theme Updated', `Switched to ${radio.value} mode`);
    }

    handleColorChange(option, { silent = false } = {}) {
        const color = option.dataset.color;
        const rgb = this.hexToRgb(color);
        const rootStyles = [
            ['--theme-color', color],
            ['--theme-color_rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`],
            ['--theme-color-hover', this.shadeColor(color, -15)],
            ['--theme-color-light', `${color}1A`],
            ['--theme-color-subtle', `${color}0D`],
            ['--bs-primary', color],
            ['--bs-primary-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`]
        ];
        rootStyles.forEach(([property, value]) => {
            document.documentElement.style.setProperty(property, value);
        });
        this.elements.colorOptions.forEach(opt => opt.classList.remove('active'));
        if (option.classList && option.classList.add) option.classList.add('active');
        if (this.elements.customizerBtn) {
            this.elements.customizerBtn.style.backgroundColor = color;
            this.elements.customizerBtn.style.boxShadow = `0 8px 25px ${color}40`;
        }
        localStorage.setItem('primaryColor', color);
        if (!silent) {
            this.showToast('Color Updated', 'Primary color changed successfully');
        }
    }

    handleReducedMotion(e) {
        const isChecked = e.target.checked;
        if (isChecked) {
            document.documentElement.classList.add('reduced-motion');
            document.documentElement.style.setProperty('--animation-duration', '0.01ms');
        } else {
            document.documentElement.classList.remove('reduced-motion');
            document.documentElement.style.removeProperty('--animation-duration');
        }
        localStorage.setItem('reducedMotion', isChecked);
    }

    handleRoundedCorners(e) {
        const isChecked = e.target.checked;
        if (isChecked) {
            document.documentElement.classList.remove('flat-corners');
        } else {
            document.documentElement.classList.add('flat-corners');
        }
        localStorage.setItem('roundedCorners', isChecked);
    }

    handleBoxedLayout(e) {
        const isChecked = e.target.checked;
        const wrapper = document.querySelector('.dashboard-wrapper');
        if (wrapper) wrapper.classList.toggle('boxed-layout', isChecked);
        localStorage.setItem('boxedLayout', isChecked);
    }

    handleCompactSidebar(e) {
        const isChecked = e.target.checked;
        const sidebar = document.querySelector('.side_sidebar');
        if (sidebar) sidebar.classList.toggle('compact', isChecked);
        localStorage.setItem('compactSidebar', isChecked);
    }

    handleStickyHeader(e) {
        const isChecked = e.target.checked;
        const header = document.querySelector('.header');
        if (header) header.classList.toggle('sticky-top', isChecked);
        localStorage.setItem('stickyHeader', isChecked);
    }

    handleReset() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Reset Theme Settings?',
                text: 'This will reset all theme customizations to default values. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3ca955',
                cancelButtonColor: '#b63755',
                confirmButtonText: 'Yes, Reset All',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                customClass: {
                    popup: 'swal-theme-reset',
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    this.performReset();
                    Swal.fire({
                        title: 'Settings Reset!',
                        text: 'All theme settings have been reset to default values.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'swal-success-reset'
                        }
                    });
                }
            });
        } else {
            if (confirm('Reset Theme Settings? All theme customizations will be lost.')) {
                this.performReset();
            }
        }
    }

    performReset() {
        document.documentElement.removeAttribute('data-theme');
        localStorage.removeItem('theme');
        if (this.elements.themeRadios[2]) {
            this.elements.themeRadios[2].checked = true;
        }
        // Reset color
        const defaultColor = '#066FD1';
        const defaultRgb = this.hexToRgb(defaultColor);
        const resetStyles = [
            ['--theme-color', defaultColor],
            ['--theme-color_rgb', `${defaultRgb.r}, ${defaultRgb.g}, ${defaultRgb.b}`],
            ['--theme-color-hover', this.shadeColor(defaultColor, -15)],
            ['--theme-color-light', `${defaultColor}1A`],
            ['--theme-color-subtle', `${defaultColor}0D`],
            ['--bs-primary', defaultColor],
            ['--bs-primary-rgb', `${defaultRgb.r}, ${defaultRgb.g}, ${defaultRgb.b}`]
        ];
        resetStyles.forEach(([property, value]) => {
            document.documentElement.style.setProperty(property, value);
        });
        this.elements.colorOptions.forEach(opt => opt.classList.remove('active'));
        const defaultOpt = document.querySelector('.color-option[data-color="#066FD1"]');
        if (defaultOpt) defaultOpt.classList.add('active');
        localStorage.removeItem('primaryColor');
        // Reset UI options
        const uiDefaults = [
            [this.elements.reducedMotion, true],
            [this.elements.roundedCorners, true],
            [this.elements.boxedLayout, false],
            [this.elements.compactSidebar, false],
            [this.elements.stickyHeader, false]
        ];
        uiDefaults.forEach(([element, defaultValue]) => {
            if (element) {
                element.checked = defaultValue;
                element.dispatchEvent(new Event('change'));
            }
        });
        if (this.elements.customizerBtn) {
            this.elements.customizerBtn.style.backgroundColor = '';
            this.elements.customizerBtn.style.boxShadow = '';
        }
        [
            'theme', 'primaryColor', 'reducedMotion', 'roundedCorners',
            'boxedLayout', 'compactSidebar', 'stickyHeader'
        ].forEach(key => localStorage.removeItem(key));
        this.updateThemeIcon();
    }

    loadPreferences() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            const themeRadio = document.querySelector(`input[name="themeMode"][value="${savedTheme}"]`);
            if (themeRadio) themeRadio.checked = true;
        } else {
            this.handleSystemThemeChange(window.matchMedia('(prefers-color-scheme: dark)'));
            const autoRadio = document.getElementById('customizerAuto');
            if (autoRadio) autoRadio.checked = true;
        }
        const savedColor = localStorage.getItem('primaryColor');
        if (savedColor) {
            const activeOption = Array.from(this.elements.colorOptions).find(opt => opt.dataset.color === savedColor);
            if (activeOption) {
                this.handleColorChange(activeOption, { silent: true });
            } else {
                const mockOption = { dataset: { color: savedColor }, classList: { add: () => {}, remove: () => {} } };
                this.handleColorChange(mockOption, { silent: true });
            }
        }
        const uiPreferences = [
            ['reducedMotion', this.elements.reducedMotion, true],
            ['roundedCorners', this.elements.roundedCorners, true],
            ['boxedLayout', this.elements.boxedLayout, false],
            ['compactSidebar', this.elements.compactSidebar, false],
            ['stickyHeader', this.elements.stickyHeader, false]
        ];
        uiPreferences.forEach(([key, element, defaultValue]) => {
            if (element) {
                const saved = localStorage.getItem(key);
                element.checked = saved !== null ? saved === 'true' : defaultValue;
                element.dispatchEvent(new Event('change'));
            }
        });
    }

    updateThemeIcon() {
        if (!this.elements.themeToggleBtn) return;
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const icon = this.elements.themeToggleBtn.querySelector('i');
        if (icon) icon.className = currentTheme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    }

    handleSystemThemeChange(e) {
        if (!localStorage.getItem('theme')) {
            document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            this.updateThemeIcon();
        }
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
                e.preventDefault();
                this.toggleCustomizer();
            }
            if (e.key === 'Escape' &&
                this.elements.customizer &&
                this.elements.customizer.classList.contains('open')
            ) {
                this.closeCustomizer();
            }
        });
    }

    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.error(`Error attempting to enable fullscreen: ${err.message}`);
            });
        } else {
            if (document.exitFullscreen) document.exitFullscreen();
        }
    }

    showToast(title, message) {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                customClass: { popup: 'swal-toast-custom' },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({ icon: 'success', title: title, text: message });
        } else if (typeof iziToast !== 'undefined') {
            iziToast.success({ title, message, position: 'bottomRight', timeout: 2000, progressBar: false });
        } else {
            console.log(`${title}: ${message}`);
        }
    }

    hexToRgb(hex) {
        let h = hex.replace('#', '');
        if (h.length === 3) h = h[0]+h+h[1]+h[1]+h[2]+h[2];
        const r = parseInt(h.slice(0, 2), 16);
        const g = parseInt(h.slice(2, 4), 16);
        const b = parseInt(h.slice(4, 6), 16);
        return { r, g, b };
    }

    shadeColor(color, percent) {
        let h = color.replace('#', '');
        if (h.length === 3) h = h+h+h[1]+h[1]+h[2]+h[2];
        let R = parseInt(h.substring(0,2), 16);
        let G = parseInt(h.substring(2,4), 16);
        let B = parseInt(h.substring(4,6), 16);
        R = parseInt(R * (100 + percent) / 100); R = (R < 255) ? R : 255;
        G = parseInt(G * (100 + percent) / 100); G = (G < 255) ? G : 255;
        B = parseInt(B * (100 + percent) / 100); B = (B < 255) ? B : 255;
        const RR = (R.toString(16).length === 1) ? "0"+R.toString(16) : R.toString(16);
        const GG = (G.toString(16).length === 1) ? "0"+G.toString(16) : G.toString(16);
        const BB = (B.toString(16).length === 1) ? "0"+B.toString(16) : B.toString(16);
        return "#" + RR + GG + BB;
    }

    exportSettings() {
        const settings = {
            theme: localStorage.getItem('theme'),
            primaryColor: localStorage.getItem('primaryColor'),
            reducedMotion: localStorage.getItem('reducedMotion') === 'true',
            roundedCorners: localStorage.getItem('roundedCorners') === 'true',
            boxedLayout: localStorage.getItem('boxedLayout') === 'true',
            compactSidebar: localStorage.getItem('compactSidebar') === 'true',
            stickyHeader: localStorage.getItem('stickyHeader') === 'true',
            exportDate: new Date().toISOString()
        };
        const blob = new Blob([JSON.stringify(settings, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'theme-settings.json';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        this.showToast('Settings Exported', 'Your theme settings have been exported successfully');
    }

    importSettings(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const settings = JSON.parse(e.target.result);
                Object.keys(settings).forEach(key => {
                    if (key !== 'exportDate' && settings[key] !== null) {
                        localStorage.setItem(key, settings[key]);
                    }
                });
                this.loadPreferences();
                this.showToast('Settings Imported', 'Your theme settings have been imported successfully');
            } catch (error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Import Failed',
                        text: 'The selected file is not a valid theme settings file.',
                        icon: 'error'
                    });
                } else {
                    console.error('Failed to import settings:', error);
                }
            }
        };
        reader.readAsText(file);
    }

    addCustomColor(color) {
        if (!this.isValidHex(color)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Invalid Color',
                    text: 'Please enter a valid hex color code (e.g., #FF5733)',
                    icon: 'error'
                });
            }
            return false;
        }
        const mockOption = { dataset: { color: color }, classList: { add: () => {}, remove: () => {} } };
        this.handleColorChange(mockOption, { silent: false });
        this.showToast('Custom Color Applied', `Applied color: ${color}`);
        return true;
    }

    isValidHex(color) {
        return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color);
    }

    applyPreset(presetName) {
        const presets = {
            'default': {
                theme: null,
                primaryColor: '#066FD1',
                reducedMotion: true,
                roundedCorners: true,
                boxedLayout: false,
                compactSidebar: false,
                stickyHeader: false
            },
            'minimal': {
                theme: 'light',
                primaryColor: '#2D3748',
                reducedMotion: true,
                roundedCorners: false,
                boxedLayout: true,
                compactSidebar: true,
                stickyHeader: true
            },
            'dark-professional': {
                theme: 'dark',
                primaryColor: '#4A90E2',
                reducedMotion: false,
                roundedCorners: true,
                boxedLayout: false,
                compactSidebar: false,
                stickyHeader: true
            },
            'vibrant': {
                theme: 'light',
                primaryColor: '#E53E3E',
                reducedMotion: false,
                roundedCorners: true,
                boxedLayout: false,
                compactSidebar: false,
                stickyHeader: false
            }
        };
        const preset = presets[presetName];
        if (!preset) return;
        Object.keys(preset).forEach(key => {
            if (preset[key] !== null) {
                localStorage.setItem(key, preset[key]);
            } else {
                localStorage.removeItem(key);
            }
        });
        this.loadPreferences();
        this.showToast('Preset Applied', `Applied ${presetName} theme preset`);
    }
}

window.themeCustomizer = new ThemeCustomizer();
window.exportThemeSettings = () => window.themeCustomizer.exportSettings();
window.applyThemePreset = (preset) => window.themeCustomizer.applyPreset(preset);
window.addCustomThemeColor = (color) => window.themeCustomizer.addCustomColor(color);
