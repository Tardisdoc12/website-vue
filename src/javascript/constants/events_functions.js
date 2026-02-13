import { Couleur } from "@/javascript/constants/colors";

export default {
        colorBg(categorie) {
            if (categorie === seance) {
                return [Couleur.seance_main, Couleur.seance_second]
            }
            if (categorie === balade) {
                return [Couleur.ballade_main, Couleur.ballade_second]
            }
            if (categorie === stage) {
                return [Couleur.stage_main, Couleur.stage_second]
            }
        },
}