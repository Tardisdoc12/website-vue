import axios from 'axios';

const API_URL = 'http://localhost:8000/events/inscrit';

export default {
    async create_inscrit(event_id, userForm) {
        const response = await axios.post(`${API_URL}/create/`, {"event_id":event_id,"user":userForm})
        return response
    }
}