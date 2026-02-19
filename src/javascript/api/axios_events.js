import api from "./api.js"

function conversion_to_bdd(datas) {
    return {
        title: datas['title'],
        post_id: datas['post_id'],
        start_date: datas['startDate'],
        end_date: datas['endDate'],
        description: datas['description'],
        place: datas['place'],
        category: datas['categorie'],
        subscribe_places: datas['subscribePlace'],
        nonsubscribe_places: datas['nonsubscribePlace'],
    }
}

function conversion_from_bdd(datas) {
    return {
        id: datas["id"],
        post_id: datas['post_id'],
        url_post: datas['url_post'],
        title: datas['title'],
        startDate: datas['start_date'],
        endDate: datas['end_date'],
        description: datas['description'],
        place: datas['place'],
        categorie: datas['category'],
        subscribePlace: datas['subscribe_places'],
        nonsubscribePlace: datas['nonsubscribe_places'],
        users: datas?.["users"] ?? null
    }
}

export default {
    // Récupérer tous les events
    async getAllEvents() {
        // await initApi();
        const response = await api.get("/events/");
        const events = Array.from(Object.values({...response.data.events}))
        return events.map(e => conversion_from_bdd(e));
    },

    // Récupérer un event par ID
    async getEvent(id) {
        const response = await api.get(`/events/${id}/`);
        if (response.data) {
            return conversion_from_bdd(response.data)
        }
        return response.data
    },

    // Récupérer un event par post Id
    async getEventByPostId(id) {
        const response = await api.get(`/events/post/${id}/`);
        if (response.data) {
            return conversion_from_bdd(response.data)
        }
        return response.data
    },

    // Récupérer un event par l'userID et l'email
    async getEventUser(user_id, email) {
        const response = await api.post(`/events/${user_id}`,{"email": email})
        return response.data
    },

    // Créer un nouvel event
    async createEvent(eventData) {
        return await api.post("/events/", conversion_to_bdd(eventData));
    },

    // Mettre à jour un event
    async updateEvent(id, eventData) {
        return await api.put(`/events/${id}/`, conversion_to_bdd(eventData));
    },

    // Supprimer un event
    async deleteEvent(id) {
        return await api.delete(`events/${id}/`);
    }
};