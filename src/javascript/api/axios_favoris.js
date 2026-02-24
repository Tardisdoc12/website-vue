import api from '@/javascript/api/api'

export default{
    async add_favoris(file_id){
        const response = await api.post('/favoris', {'file_id': file_id})
        return response
    },

    async get_favoris() {
        const response = await api.get(
            '/favoris'
        )
        return response
    },

    async delete_favoris(file_id) {
        const response = await api.delete(
            '/favoris',
            {
                data: {
                    file_id: file_id
                }
            }
        )
        return response
    }
}