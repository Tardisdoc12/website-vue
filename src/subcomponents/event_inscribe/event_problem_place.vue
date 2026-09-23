<template>
    <div style="margin-top: 10px;margin-left: 10px;margin-right:10px;margin-bottom: 10px;">
        <p>Il y a un problème avec les inscriptions</p>
        <p>{{`l'utilisateur ${participantProblemData.participant_problem.firstName} ${participantProblemData.participant_problem.lastName} n'a pas pu s'inscrire correctement.`}}</p>
        <p>Vous pouvez soit annuler l'inscription, soit continuer sans cet utilisateur.</p>
        <div style="display:flex; gap:8px; justify-content:center;">
            <button
                type="button"
                class="button-cancel"
                @click="CancelInscription"
            >
                {{ "Annuler l'inscription" }}
            </button>

            <button
                type="button"
                class="button-base"
                @click="ContinueInscription"
            >
                {{ "Continuer sans cet utilisateur" }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    signals: [
        'update:participantProblem',
        'cancelSignal',
        'continueSignal'
    ],

    props: {
        participantProblem: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            participantProblemData: this.participantProblem
        }
    },

    computed: {
        participantProblemComputed: {
            get() {
                return this.participantProblemData
            },
            set(value) {
                this.participantProblemData = value
                this.$emit('update:participantProblem', value)
            }
        }
    },

    methods: {
        CancelInscription() {
            this.$emit('cancelSignal')
        },

        ContinueInscription() {
            const participantToRemove = this.participantProblemData.participant_problem
            const participantsListWithoutProblem = this.participantProblem.all_participants.filter(
                participant => participant.firstName != participantToRemove.firstName &&
                participant.lastName != participantToRemove.lastName &&
                participant.email != participantToRemove.email
            )
            this.participantProblemComputed = participantsListWithoutProblem.map(participant => ({ ...participant }))
            this.$emit('continueSignal')
        }
    }
}
</script>