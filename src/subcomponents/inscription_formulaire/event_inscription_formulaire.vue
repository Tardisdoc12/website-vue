<template>
    <OngletsComponents
        :OngletList="onglets"
        :steps="steps"
        @update:steps="steps = $event"
    />

    <InformationsPersonnellesComponent
        v-if="steps === 0"
        v-model:informationsPersonnelles="informationsPersonnelles"
    />

    <InformationsComplementairesComponent
        v-if="steps === 1 && (specialsFields.length > 0 || isEncadrantComp)"
        v-model:wantsEncadrant="wantsEncadrantData"
        :isEncadrantComp="isEncadrantComp"
        :specialsFields="specialsFields"
        v-model:specialsFieldsParticipant="specialsFieldsParticipant"
        :roles="currentParticipant.roles"
    />
</template>

<script>
import InformationsPersonnellesComponent from '@/subcomponents/inscription_formulaire/informations_personnelles.vue';
import OngletsComponents from '@/subcomponents/unitary_elements/onglets_components.vue';
import InformationsComplementairesComponent from '@/subcomponents/inscription_formulaire/informations_complementaires.vue';

import { isEncadrant } from '@/javascript/constants/roles';

export default {
    name: 'EventInscriptionFormulaire',

    signals: [
        'update:currentParticipant'
    ],

    props: {
        currentParticipant: {
            type: Object,
            required: true
        },

        eventCategorie: {
            type: String,
            required: true
        }
    },

    data() {
        const categorie= this.$settings.categories.find(
            cat => cat.nom.toLowerCase() === this.eventCategorie.toLowerCase()
        )
        return {
            steps: 0,
            infoParticipant: this.currentParticipant,
            onglets: [
                'Informations Personnelles',
                'Informations Complémentaires'
            ],
            isEncadrantComp: isEncadrant(this.currentParticipant.roles || ['non_adherent']),
            specialsFields: categorie.champs_speciaux,
        };
    },

    computed: {
        participant: {
            get() {
                return this.infoParticipant;
            },
            set(value) {
                this.infoParticipant = value;
                this.$emit('update:currentParticipant', value);
            }
        },
        wantsEncadrantData: {
            get() {
                return this.participant.wantsEncadrant;
            },
            set(value) {
                this.participant.wantsEncadrant = value;
                this.$emit('update:currentParticipant', this.infoParticipant);
            }
        },
        informationsPersonnelles: {
            get() {
                let informations = {
                    'firstName': this.participant.firstName,
                    'lastName': this.participant.lastName,
                    'email': this.participant.email,
                    'phone': this.participant.phone,
                    'wantsEncadrant': this.participant.wantsEncadrant,
                    'specialsFields': {},
                    'roles': this.participant.roles || ['non_adherent']
                };
                return informations;
            },
            set(value) {
                this.participant.firstName = value.firstName;
                this.participant.lastName = value.lastName;
                this.participant.email = value.email;
                this.participant.phone = value.phone;
                this.participant.wantsEncadrant = value.wantsEncadrant;
                this.$emit('update:currentParticipant', this.infoParticipant);
            }
        },

        specialsFieldsParticipant: {
            get() {
                return this.infoParticipant.specialField;
            },
            set(value) {
                const specialField = { ...value };
                this.$emit('update:currentParticipant', { ...this.infoParticipant, specialField });
            }
        }
    },

    components: {
        OngletsComponents,
        InformationsPersonnellesComponent,
        InformationsComplementairesComponent,
    }
}
</script>