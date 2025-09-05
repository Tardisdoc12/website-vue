import api from "./api.js"

const url = "https://mps-moto.fr/wp-json/wp/v2/users"

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
        const response = await api.get(`${url}/${id}`)
        return response.data
    },

    async verify_connexion(email, password) {
        const body = {
            username: email,
            password: password,
        }
        const response = await api.post(`${url}/connect/`, JSON.stringify(body))
        return response.data
    },
}