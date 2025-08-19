import api from "./api.js"

const url = "http://localhost:8000"

export default {
    async create_user(body) {
        const response = await api.post(url+"/register/", body)
        return response.data
    },

    async get_users() {
        const response = await api.get(url+"/users/")
        return response.data
    }
}