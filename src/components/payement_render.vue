<template>
    <div class="payment-check">
        <div v-if="state === 'loading'" class="status-loading">
            <div class="spinner"></div>
            <p>Vérification de votre paiement en cours...</p>
        </div>

        <div v-else-if="state === 'success'" class="status-success">
            <p>✅ Votre paiement a bien été validé ! Votre inscription est confirmée.</p>
        </div>

        <div v-else-if="state === 'pending'" class="status-pending">
            <p>⏳ Votre paiement est en cours de traitement.</p>
            <p class="small">Cette vérification peut prendre quelques minutes. Vous recevrez une confirmation même si vous quittez cette page.</p>
        </div>

        <div v-else-if="state === 'error'" class="status-error">
            <p>❌ Une erreur est survenue lors de la vérification de votre paiement.</p>
            <p v-if="errorMessage" class="small">{{ errorMessage }}</p>
            <p class="small">Référence : {{ checkoutIntentId }}</p>
        </div>
    </div>
</template>

<script>
import apiPayement from '@/javascript/api/api_payement'

export default {
    name: 'PaymentCheck',

    data() {
        return {
            state: 'loading',
            errorMessage: '',
            checkoutIntentId: null,
            retryCount: 0,
            maxRetries: 5,
            retryDelayMs: 3000,
        }
    },

    mounted() {
        const params = new URLSearchParams(window.location.search)
        this.checkoutIntentId = params.get('checkoutIntentId')

        // "code" est présent aussi (ex: code=succeeded), mais on ne s'y fie jamais
        // pour la validation réelle — uniquement checkoutIntentId sert à vérifier auprès du serveur.

        if (!this.checkoutIntentId) {
            this.state = 'error'
            this.errorMessage = 'Référence de paiement manquante dans l\'URL.'
            return
        }

        this.checkPayment()
    },

    methods: {
        async checkPayment() {
            try {
                const data = await apiPayement.checkPayment(this.checkoutIntentId)
                const response = { ok: data.success !== undefined }

                if (!response.ok) {
                    this.state = 'error'
                    this.errorMessage = data.message || 'Erreur inconnue.'
                    return
                }

                if (data.success) {
                    this.state = 'success'
                    return
                }

                this.retryOrGiveUp()

            } catch (err) {
                this.state = 'error'
                this.errorMessage = 'Impossible de contacter le serveur.'
            }
        },

        retryOrGiveUp() {
            this.retryCount++

            if (this.retryCount >= this.maxRetries) {
                this.state = 'pending'
                return
            }

            this.state = 'pending'
            setTimeout(() => this.checkPayment(), this.retryDelayMs)
        },
    },
}
</script>

<style scoped>
.payment-check { padding: 24px; text-align: center; }
.status-success p { color: #2e7d32; }
.status-error p { color: #c62828; }
.status-pending p { color: #ef6c00; }
.small { font-size: 0.85rem; color: #777; }
</style>