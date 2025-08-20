import api from "./api.js"

const url = "https://localhost:8000"

export default {
    async create_user(body) {
        const response = await api.post(url+"/register/", body)
        return response.data
    },

    async get_users() {
        const response = await api.get(url+"/users/")
        return response.data
    },

    async get_user(id) {
        const response = await api.get(`${url}/users/${id}`)
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