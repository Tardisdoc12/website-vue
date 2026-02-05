import api from "@/javascript/api"
import axios from "axios"

const UPLOAD_URL = "https://mps-moto.fr/wp-json/wp/v2/media"

export default {
    async upload_file(file) {
        const res = await api.post(UPLOAD_URL, file)
        return res
    },
    async delete_file(mediaId) {
        if (!mediaId) {
            throw new Error("ID du média manquant")
        }
        const res = await axios.delete(
            `${UPLOAD_URL}/${mediaId}`,
            {
            withCredentials: true,
            params: {
                force: true
            },
            headers: {
                "X-WP-Nonce": window.wpApiSettings.nonce
            }
            }
        )

        return res
    }
}