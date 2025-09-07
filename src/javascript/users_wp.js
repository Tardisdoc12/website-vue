import api from "./api.js"

const url_connect = "https://mps-moto.fr/wp-json/jwt-auth/v1/token"

export default {
    async create_user(body) {
        try {
            const response = await api.post("/register/", body)
            return response
        } catch (err) {
            throw err
        }
    },

    async get_users() {
        const response = await api.get('/users/')
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

    async connect_user(user_id) {
        const response = await api.post("/connect",{"user_id": user_id})
        return response
    }
}