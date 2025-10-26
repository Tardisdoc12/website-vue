import api from "@/javascript/api"

const UPLOAD_URL = "https://mps-moto.fr/wp-json/wp/v2/media"

export default {
    async upload_file(file) {
        const res = await api.post(UPLOAD_URL, file)
        return res
    }
}