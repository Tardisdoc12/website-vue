import api from '@/javascript/api/api'

export async function sendCustomEmail(emailData) {
    return await api.post('/send-custom-email', emailData);
}