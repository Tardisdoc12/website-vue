import api from '@/javascript/api/api'

export const verifyPromo = async (promoCode, eventId) => {
    try {
        const response = await api.post('/verify-promo', { promo_code: promoCode, event_id: eventId });
        return response.data;
    } catch (error) {
        console.error('Error verifying promo code:', error);
        throw error;
    }
};