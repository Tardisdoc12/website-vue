<template>
    <p style="margin-bottom: 10px; margin-top: 10px; margin-left: 10px;margin-right: 10px;">{{  "Votre inscription ne sera validée qu'après paiement. Merci de remplir le formulaire ci-dessous pour procéder au paiement." }}</p>
    <iframe class="center-helloasso" :src="getBilleterieURL" style="border: none; width: 100%; height: 700px;">
    </iframe>
</template>

<script>
export default {
    props: {
        Date: {
            type: String,
            required: true,
        },
        billeterie_url: {
            type: String,
            required: false,
            default: "",
        }
    },

    data() {
        return {
            isOpen: false,
            title: "Règlement",
            dates :{
                "1_22_2026" : "stage-reprise-de-guidon/widget",
                "2_8_2026" : "stage-reprise-de-guidon-8-mars/widget",
                "2_22_2026" : "stage-reprise-de-guidon-22-mars/widget",
            },
            baseUrl : "https://www.helloasso.com/associations/mps-moto/evenements/",
            fallback : "inscription-seance/widget",
        }
    },

    computed: {
        getBilleterieURL() {
            if (this.billeterie_url && this.billeterie_url !== "") {
                const url = this.billeterie_url.endsWith('/widget')
                    ? this.billeterie_url
                    : this.billeterie_url + '/widget';
                return url;
            }

            const match = Object.entries(this.dates).find(([key]) => {
                const [month, day, year] = key.split("_").map(Number);
                return this.compareDate(this.Date, month, day, year);
            });

            return this.baseUrl + (match ? match[1] : this.fallback);
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