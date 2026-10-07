<template>
    <div>
        <MailingTemplate
            :isOnePerson="true"
            v-model:email="email"
            :usersEmails="emailList"
            :templates="templates"
            :style="{
                padding: '1rem'
            }"
        />

        <div class="flex justify-center gap-3" style="margin-right:10px;margin-bottom:10px;">
            <button
                class="button-cancel"
                @click="() => $emit('cancel')"
            >
                Annuler
            </button>
            
            <button
                class="button-confirm"
                @click="sendEmailToUsers"
            >
                Send Email
            </button>
        </div>
    </div>
</template>

<script>
import MailingTemplate from '@/subcomponents/unitary_elements/mailing_template.vue'

export default {
    name: "EventMailing",

    signals: [
        "cancel",
    ],

    props: {
        emailList: {
            type: Array,
            required: true
        },
        templates: {
            type: Array,
            required: true
        }
    },

    data() {
        return {
            email: {
                to: [],
                subject: "",
                message: ""
            }
        }
    },

    methods: {
        sendEmailToUsers() {
            console.log("Sending email to users:", this.email);
            this.$emit("cancel", this.email);
        }
    },
    components: {
        MailingTemplate
    }
}
</script>