
function initColorPickers() {
    jQuery('.mps-tools-color-picker').not('.wp-color-picker').wpColorPicker();
}

jQuery(document).ready(initColorPickers);

// Après ajout d'une catégorie, ré-exécute l'init
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('mps-tools-add-category')) {
        setTimeout(initColorPickers, 0);
    }
});