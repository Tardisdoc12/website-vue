import api from "./api.js"

export default {
    async create_inscrit(event_id, userForm, isAddAdmin = false) {
        const champs_speciaux = userForm.specialField || userForm.champs_speciaux || {}

        const response = await api.post(`/subscribe/`, {
            "event_id": event_id,
            "user": userForm,
            "champs_speciaux": champs_speciaux,  // ← à la racine, comme attendu par le PHP
            "isAddAdmin": isAddAdmin
        })
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