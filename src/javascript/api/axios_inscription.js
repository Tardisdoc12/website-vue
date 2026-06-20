import api from "./api.js"

export default {
    async create_inscrit(event_id, userForm) {
        const response = await api.post(`/subscribe/`, {"event_id":event_id,"user":userForm})
        return response
    },

    async change_status_inscrit(event_id, user_id) {
        const response = await api.post(`/subscribe/${event_id}/${user_id}`)
        return response
    },

    async find_user(query) {
        const response = await api.get(`/users/search`, { params: { q: query } })
        return response
    },

    async delete_inscrit(event_id, user_id) {
        const response = await api.delete(`/subscribe/${user_id}/${event_id}`)
        return response
    }
}