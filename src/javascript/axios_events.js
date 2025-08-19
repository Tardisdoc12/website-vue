import api from "./api.js"

const API_URL = 'https://localhost:8000/events/';

function conversion_to_bdd(datas) {
    return {
        title: datas['title'],
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
    console.log(datas.users)
    return {
        id: datas["id"],
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
        const response = await api.get(API_URL);
        return response.data.map(e => conversion_from_bdd(e));
    },

    // Récupérer un event par ID
    async getEvent(id) {
        response = await api.get(`${API_URL}${id}/`);
        if (response.data) {
            return conversion_from_bdd(response.data)
        }
        return response.data
    },

    // Créer un nouvel event
    async createEvent(eventData) {
        return await api.post(API_URL+"create/", conversion_to_bdd(eventData));
    },

    // Mettre à jour un event
    async updateEvent(id, eventData) {
        return await api.put(`${API_URL}${id}/`, eventData);
    },

    // Supprimer un event
    async deleteEvent(id) {
        return await api.delete(`${API_URL}${id}/`);
    }
};