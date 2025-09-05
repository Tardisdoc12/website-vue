import api from "./api.js"

const url = "https://mps-moto.fr/wp-json/wp/v2/users"
const url_connect = "https://mps-moto.fr/wp-json/jwt-auth/v1/token"

export default {
    async create_user(body) {
        const response = await api.post(url, body)
        return response.data
    },

    async get_users() {
        const response = await api.get(url)
        return response.data
    },

    async get_user(id) {
        const response = await api.get(`users/${id}`)
        return response.data
    },

    async verify_connexion(email, password) {
        const body = {
            username: email,
            password: password,
        }
        const response = await api.post(url_connect, body)
        return response.data
    },
}