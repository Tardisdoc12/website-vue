<template>
    <!-- On affiche l'ancien systeme pour les events qui ne sont pas encore passer -->
    <div v-if="getBilleterieURL">
        <p style="margin-bottom: 10px; margin-top: 10px; margin-left: 10px;margin-right: 10px;">
            {{  "Votre inscription ne sera validée qu'après paiement. Merci de remplir le formulaire ci-dessous pour procéder au paiement." }}
        </p>
        <iframe class="center-helloasso" :src="getBilleterieURL" style="border: none; width: 100%; height: 700px;">
        </iframe>
    </div>

    <!-- Nouveau système de paiement -->
    <div v-else>
        <div v-if="loading" class="status-pending">
            <p>⏳ Vous allez être redirigé vers le paiement. Soyez patient...</p>
            <p class="small">
                {{ "Si vous n'êtes pas redirigé automatiquement, "}}
                <template v-if="redirectUrl">
                    <a :href="redirectUrl">cliquez ici pour procéder au paiement</a>
                </template>
                <template v-else>
                    <span>Veuillez contacter un membre de l'organisation pour obtenir de l'aide.</span>
                </template>
            </p>
        </div>
        <div v-else-if="error" class="status-error">
            <p>❌ Impossible de générer le lien de paiement.</p>
            <p class="small">Veuillez contacter un membre de l'organisation.</p>
        </div>
        <div v-else-if="!loading && !error" class="status-ready">
            <p>✅ Le lien de paiement est prêt. <a :href="redirectUrl">Cliquez ici pour procéder au paiement</a></p>
        </div>
    </div>

</template>

<script>
import apiPayment from '@/javascript/api/api_payement';

export default {
    name: "EventPayement",

    props: {
        Date: {
            type: String,
            required: true,
        },

        inscription_id: {
            type: Array,
            required: true,
        },

        totalAmountPrice: {
            type: Number,
            required: true,
        },

        payement_title: {
            type: String,
            required: true,
        },

        event_id: {
            type: String,
            required: true,
        },

        user: {
            type: Object,
            required: true,
        }
    },

    async mounted(){
        console.log("Mounted EventPayement component")
        if (this.getBilleterieURL !== '') {
            return;
        }

        const data_to_send = { 
            inscription_id: this.inscription_id,
            initialAmount: this.totalAmountPrice,
            totalAmount: this.totalAmountPrice,
            itemName: this.payement_title,
            event_id: this.event_id,
            firstName: this.user.firstName,
            lastName: this.user.lastName,
            email: this.user.email,
        }
        console.log("Data to send for payment intent:", data_to_send)
        try {
            const result = await apiPayment.createPaymentIntent(data_to_send);
            console.log("Result from createPaymentIntent:", result);
            if (result?.data?.redirectUrl) {
                this.redirectUrl = result.data.redirectUrl;
                this.loading = false;
                window.location.href = this.redirectUrl;
            } else {
                this.loading = false;
                this.error = true;
            }
        } catch (err) {
            this.loading = false;
            this.error = true;
            console.error('Erreur création paiement :', err);
        }
    },

    data() {
        return {
            title: this.payement_title || "Règlement",
            loading: true,
            error: false,
            redirectUrl: '',
            dates :{
                "8_27_2026" : "stage-de-perfectionnement-27-septembre-2026/widget",
                "9_11_2026" : "stage-de-perfectionnement-11-octobre-2026/widget",
            },
        }
    },

    computed: {
        getBilleterieURL() {
            const match = Object.entries(this.dates).find(([key]) => {
                const [month, day, year] = key.split("_").map(Number);
                return this.compareDate(this.Date, month, day, year);
            });

            const base_url_billeterie = "https://www.helloasso.com/associations/mps-moto/evenements/";

            return match ? base_url_billeterie + match[1] : '';
        }
    },

    methods:{
        compareDate(eventDate, month, day, year) {
            const eventDateF = new Date(eventDate);
            const targetDate = new Date(year, month, day);

            eventDateF.setHours(0, 0, 0, 0);
            targetDate.setHours(0, 0, 0, 0);
            const isSameDay = eventDateF.getTime() === targetDate.getTime();

            return isSameDay;
        }
    }
}

</script>

<style>
.center-helloasso {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>