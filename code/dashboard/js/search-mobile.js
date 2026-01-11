// Mobile Search-Funktion
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.getElementById('search-input');
    const searchButton = searchForm ? searchForm.querySelector('button') : null;

    if (!searchButton || !searchInput) return;

    // Überprüfe ob Mobile-Ansicht
    function isMobileView() {
        return window.innerWidth <= 768;
    }

    // Button-Click Handler - verhindere Standard-Verhalten auf Mobile
    searchButton.addEventListener('click', function(e) {
        if (isMobileView()) {
            e.preventDefault();
            e.stopPropagation();
            // Input fokussieren - das fahrt die Form aus
            searchInput.focus();
        }
    });

    // Button-Mousedown auch abfangen für bessere Kompatibilität
    searchButton.addEventListener('mousedown', function(e) {
        if (isMobileView()) {
            e.preventDefault();
        }
    });

    // Button-Touchstart auch abfangen für Touch-Geräte
    searchButton.addEventListener('touchstart', function(e) {
        if (isMobileView()) {
            e.preventDefault();
            e.stopPropagation();
            searchInput.focus();
        }
    });

    // Input-Focus-Handler - stelle sicher, dass das Feld sichtbar wird
    searchInput.addEventListener('focus', function(e) {
        if (isMobileView()) {
            // Verzögerter Scroll, um sicherzustellen, dass das Input-Feld sichtbar ist
            setTimeout(function() {
                searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
    });

    // Wenn Enter gedrückt wird in der Input, submit erlauben (für beide Ansichten)
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchForm.submit();
        }
    });

    // Auf Window-Resize prüfen und Button-Type anpassen
    window.addEventListener('resize', function() {
        if (isMobileView()) {
            searchButton.type = 'button';
        } else {
            searchButton.type = 'submit';
        }
    });

    // Initial setzen
    if (isMobileView()) {
        searchButton.type = 'button';
    }
});

