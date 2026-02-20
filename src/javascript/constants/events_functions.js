import { Couleurs } from "@/javascript/constants/colors";
import { Events } from "@/javascript/constants/events_type.js"
import { jwtDecode } from "jwt-decode"
import api from "@/javascript/api/users_wp.js"

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

        async isUserConnected() {
            const token = sessionStorage.getItem("mps_moto")
            if (token) {
                const decoded = jwtDecode(token)
                const user_id = decoded.data.user.id
                const user_info = await api.get_user(user_id)
                const user = {...user_info.user}
                return user
            }
            return {}
        },     
}