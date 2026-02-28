import api from '@/javascript/api/api'

export default{
    async update_notes(user_id,is_personal, notes){
        const response = await api.put(
            '/notes',
            {
                'is_personal':is_personal,
                'notes':notes,
                'user_id': user_id
            }
        )
        return response
    },

    async add_notes(data) {
        const response = await api.post(
            '/notes',
            data
        )
        return response
    },

    async get_notes_by_user(user_id){
        const response = await api.get(
            `/notes`,
            {
                params: {
                    user_id: user_id
                }
            }
        )
        return response
    },
}