import api from '@/javascript/api/api'


export default {
    async checkPayment(checkoutIntentId) {
        return await api.get(
            `check_payment?checkoutIntentId=${encodeURIComponent(checkoutIntentId)}`
        )
    },

    async createPaymentIntent(data) {
        return await api.post(
            `create_payements`,
            data
        )
    }
}