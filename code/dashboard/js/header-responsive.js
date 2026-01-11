
document.addEventListener('DOMContentLoaded', function() {
    updatePageTitle();

    setupUserContactMenu();
    window.addEventListener('resize', function() {
        updatePageTitle();
    });
});

function updatePageTitle() {
    const pageTitle = document.querySelector('.page-title');
    if (!pageTitle) return;

    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
        const text = pageTitle.textContent;

        if (text.includes('Mein Dashboard')) {
            pageTitle.textContent = 'Dashboard';
        } else if (text.includes('Meine Favoriten')) {
            pageTitle.textContent = 'Favoriten';
        }
    } else {
        const text = pageTitle.textContent;

        if (text === 'Dashboard') {
            pageTitle.textContent = 'Mein Dashboard';
        } else if (text === 'Favoriten') {
            pageTitle.textContent = 'Meine Favoriten';
        }
    }
}

// Erstelle User-Info Menu für Mobile
function setupUserContactMenu() {
    const isMobile = window.innerWidth <= 768;
    const userInfo = document.querySelector('.user-info');

    if (!userInfo) return;

    // Nur auf Mobile ein Menu erstellen
    if (!isMobile) {
        return;
    }

    // Prüfe ob Button bereits existiert
    if (document.querySelector('.user-contact-btn')) {
        return;
    }

    // Erstelle Kontakt-Button
    const contactBtn = document.createElement('button');
    contactBtn.className = 'user-contact-btn';
    contactBtn.innerHTML = '<i class="fas fa-user-circle"></i>';
    contactBtn.title = 'Mein Konto';

    // Erstelle User-Info Menu
    const userName = userInfo.querySelector('.user-name');
    const userRole = userInfo.querySelector('.user-role');
    const logoutLink = document.querySelector('#logout-btn');

    const menu = document.createElement('div');
    menu.className = 'user-info-menu';
    menu.innerHTML = `
        <h3>Mein Konto</h3>
        ${userName ? `<span class="user-name">${userName.textContent}</span>` : ''}
        ${userRole ? `<span class="user-role">${userRole.textContent}</span>` : ''}
        ${logoutLink ? `<a href="${logoutLink.href}"><i class="fas fa-sign-out-alt"></i> ${logoutLink.textContent}</a>` : ''}
    `;

    // Füge Button und Menu zur Header hinzu
    const loginStatus = document.querySelector('.login-status');
    if (loginStatus) {
        loginStatus.insertBefore(contactBtn, loginStatus.firstChild);
        loginStatus.appendChild(menu);

        // Event-Listener für Button
        contactBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('show');
        });

        // Schließe Menu wenn außerhalb geklickt wird
        document.addEventListener('click', function(e) {
            if (!contactBtn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        // Schließe Menu beim Klick auf einen Link
        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                menu.classList.remove('show');
            });
        });
    }
}

// Schließe Menu beim Resize
window.addEventListener('resize', function() {
    const menu = document.querySelector('.user-info-menu');
    if (menu) {
        menu.classList.remove('show');
    }
});

