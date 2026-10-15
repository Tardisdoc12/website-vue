import api from '@/javascript/api/api'

export const verifyPromo = async (promoCode, eventId, eventCategorie) => {
    try {
        const response = await api.post('/verify-promo', { promo_code: promoCode, event_id: eventId, event_categorie: eventCategorie });
        return response.data;
    } catch (error) {
        console.error('Error verifying promo code:', error);
        throw error;
    }
};