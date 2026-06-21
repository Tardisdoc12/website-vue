import api from '@/javascript/api/api'

export default{
    async get_billeteries() {
        const response = await api.get(
            '/billeterie'
        )
        return response
    },
}