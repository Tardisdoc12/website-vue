import api from '@/javascript/api/api'

export default{
    async add_conseils(file_id, user_id){
        const response = await api.post('/conseils', {'file_id': file_id, 'user_id': user_id})
        return response
    },

    async get_conseils() {
        const response = await api.get(
            '/conseils'
        )
        return response
    },

    async get_conseils_by_user(user_id){
        const response = await api.get(
            `/conseils/${user_id}`
        )
        return response
    },

    async delete_conseils(file_id) {
        const response = await api.delete(
            '/conseils',
            {
                data: {
                    file_id: file_id
                }
            }
        )
        return response
    },

    async delete_conseils_for_user(user_id, file_id) {
        const response = await api.delete(
            `/conseils/${user_id}/${file_id}`
        )
        return response
    },

    async delete_conseils_from_file(file_id) {
        const response = await api.delete(`/conseils/${file_id}`)
        return response
    }
}