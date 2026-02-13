import { Couleurs } from "@/javascript/constants/colors";
import { Events } from "@/javascript/constants/events_type.js"

export default {
        colorBg(categorie) {
            if (categorie === Events.seance) {
                return [Couleurs.seance_main, Couleurs.seance_second]
            }
            if (categorie === Events.balade) {
                return [Couleurs.ballade_main, Couleurs.ballade_second]
            }
            if (categorie === Events.stage) {
                return [Couleurs.stage_main, Couleurs.stage_second]
            }
        },
}