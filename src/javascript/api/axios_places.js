import api from '@/javascript/api/api'

export default{
    async get_places(){
        const response = await api.get(
            '/places'
        )
        return response
    },

    async add_place(data) {
        const response = await api.post(
            '/places',
            data
        )
        return response
    },
}