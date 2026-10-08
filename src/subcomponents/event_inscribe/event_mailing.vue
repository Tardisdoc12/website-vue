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
                @click="sendEmailToUsers()"
            >
                Send Email
            </button>
        </div>
    </div>
</template>

<script>
import MailingTemplate from '@/subcomponents/unitary_elements/mailing_template.vue'
import { sendCustomEmail } from '@/javascript/api/api_mailing'


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
                message: "",
                attachments: null,
            }
        }
    },

    methods: {
        async sendEmailToUsers() {
            const formData = new FormData()

            this.email.to.forEach(addr => formData.append('to[]', addr))
            formData.append('subject', this.email.subject)
            formData.append('message', this.email.message)

            if (this.email.attachments) {
                formData.append('attachments', this.email.attachments)
            }

            const response = await sendCustomEmail(formData);
            if (response?.data?.success){
                console.log("Email sent successfully:", response.data);
            }
            else {
                console.error("Failed to send email!");
            }
            this.$emit("cancel", this.email);
        }
    },
    components: {
        MailingTemplate
    }
}
</script>