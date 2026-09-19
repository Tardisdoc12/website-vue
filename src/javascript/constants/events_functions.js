function hexToRgb(hex) {
    // Supprime le # s'il est présent
    hex = hex.replace('#', '');

    // Gère le format court #rgb (ex: #f00 -> #ff0000)
    if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
    }

    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);

    return { r, g, b };
}

export default {
    get_color_events_by_categorie(categorie) {
        let categories = MPS_TOOLS_SETTINGS.categories
        let categorie_found = categories.find(cat => cat.nom.toLowerCase() === categorie.toLowerCase())
        if (categorie_found) {
            const { r, g, b } = hexToRgb(categorie_found.couleur);
            const main_color = `rgb(${r}, ${g}, ${b}, 1)`
            const second_color = `rgb(${r}, ${g}, ${b}, 0.2)`
            return [main_color, second_color]
        }
        return [null, null]
    },    
}