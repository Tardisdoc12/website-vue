import api from "@/javascript/api/api"
import axios from "axios"
import { URLS } from "@/javascript/constants/urls.js"

const UPLOAD_URL = URLS.media_url

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
                "X-WP-Nonce": window.vueAppData.nonce
            }
            }
        )

        return res
    }
}