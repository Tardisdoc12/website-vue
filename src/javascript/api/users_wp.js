import api from "./api.js"
import { URLS } from "@/javascript/constants/urls.js"

const url_connect = URLS.url_connect

export default {
    async create_user(body) {
        try {
            const response = await api.post("/register/", body)
            return response
        } catch (err) {
            throw err
        }
    },

    async update_user(user) {
        try {
            const response = await api.post(`/user/update`, user)
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
    },

    async request_reset_password(email) {
        const response = await api.post("/psswd/reset",{"email": email})
        return response
    },

    async verify_reset(key,login) {
        const response = await api.post("/check-reset-key",{"key": key, "login": login})
        return response
    },
    
    async reset_password(key, login, password) {
        const response = await api.post("/password",{"key": key, "login": login, "password": password})
        return response
    }
}