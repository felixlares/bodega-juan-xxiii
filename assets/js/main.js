document.addEventListener('DOMContentLoaded', () => {
    // Menu Mobile Toggle
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = menuToggle.querySelector('i');
            if(navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Compartir Web Share API
    window.shareProduct = function(url, title, text) {
        if (navigator.share) {
            navigator.share({
                title: title,
                text: text,
                url: url,
            })
            .then(() => console.log('Successfully shared'))
            .catch((error) => console.log('Error sharing', error));
        } else {
            // Fallback (copiar al portapapeles)
            navigator.clipboard.writeText(url).then(() => {
                alert('¡Enlace copiado al portapapeles!');
            }).catch(() => {
                prompt("Copia este enlace para compartir:", url);
            });
        }
    };
});
