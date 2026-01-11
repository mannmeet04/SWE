
document.addEventListener('DOMContentLoaded', function() {
    initializeAccessibility();
});

function initializeAccessibility() {
    const fontSizeStored = localStorage.getItem('a11y-font-size');
    const darkModeStored = localStorage.getItem('a11y-dark-mode');

    if (fontSizeStored) {
        setFontSize(parseInt(fontSizeStored));
    }

    if (darkModeStored === 'true') {
        enableDarkMode();
    }

    const a11yButton = document.getElementById('accessibility-btn');
    const a11yMenu = document.getElementById('accessibility-menu');

    if (a11yButton && a11yMenu) {
        a11yButton.addEventListener('click', function(e) {
            e.stopPropagation();
            a11yMenu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!a11yMenu.contains(e.target) && !a11yButton.contains(e.target)) {
                a11yMenu.classList.remove('show');
            }
        });
    }

    const fontSizeSlider = document.getElementById('font-size-slider');
    if (fontSizeSlider) {
        fontSizeSlider.addEventListener('input', function(e) {
            setFontSize(parseInt(e.target.value));
            localStorage.setItem('a11y-font-size', e.target.value);
        });
    }

    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                enableDarkMode();
                localStorage.setItem('a11y-dark-mode', 'true');
            } else {
                disableDarkMode();
                localStorage.setItem('a11y-dark-mode', 'false');
            }
        });
    }
}

function setFontSize(size) {
    // Größe zwischen 80-150% mit 10% Schritten
    const percentage = 80 + (size * 10);
    const factor = percentage / 100;

    document.documentElement.style.fontSize = (factor * 16) + 'px';

    document.documentElement.style.setProperty('--fach-max-width', (200 * factor) + 'px');
    document.documentElement.style.setProperty('--gap-spacing', (30 * factor) + 'px');

    const slider = document.getElementById('font-size-slider');
    if (slider) {
        slider.value = size;
    }

    const display = document.getElementById('font-size-display');
    if (display) {
        display.textContent = percentage + '%';
    }
}

function enableDarkMode() {
    document.body.classList.add('dark-mode');

    const toggle = document.getElementById('dark-mode-toggle');
    if (toggle) {
        toggle.checked = true;
    }
}

function disableDarkMode() {
    document.body.classList.remove('dark-mode');

    const toggle = document.getElementById('dark-mode-toggle');
    if (toggle) {
        toggle.checked = false;
    }
}

function resetAccessibilitySettings() {
    setFontSize(3); // 110% als Standard
    disableDarkMode();
    localStorage.removeItem('a11y-font-size');
    localStorage.removeItem('a11y-dark-mode');
}

