<template>
    <div v-for="(value, key) in specialsFieldsData" :key="key">
        <div
            v-if="specificitySpecialFieldsToDraw[key].type_field === 'checkbox'"
            class="flex items-center gap-2"
            style="margin-top:10px;"
        >
            <label class="font-medium">
                {{ key }}
                <span v-if="specificitySpecialFieldsToDraw[key].isMandatory" style="color:red">*</span>
                <a 
                    v-if="specificitySpecialFieldsToDraw[key].document_choice !== '0'"
                    style="color:blue"
                    :href="specificitySpecialFieldsToDraw[key].document_choice"
                >
                    Voir le document
                </a>
            </label>

            <input
                v-model="specialsFieldsData[key]"
                type="checkbox"
                class="w-5 h-5"
                :required="specificitySpecialFieldsToDraw[key].isMandatory"
            />
        </div>

        <div v-else>
            <label class="block font-medium">
                {{ key }}
                <span v-if="specificitySpecialFieldsToDraw[key].isMandatory" style="color:red">*</span>
            </label>

            <input
                v-model="specialsFieldsData[key]"
                :type="specificitySpecialFieldsToDraw[key].type_field"
                class="w-full border p-1 rounded"
                :required="specificitySpecialFieldsToDraw[key].isMandatory"
            />
        </div>
    </div>
</template>

<script>
import { isAdherent } from '@/javascript/constants/roles'

export default {
    name: 'SpecialFieldPipelineComponent',

    signals: [
        'update:specialsFieldsParticipant'
    ],

    props: {
        specialsFieldsParticipant: {
            type: Object,
            required: true
        },

        specialsFieldsSpecificity: {
            type: Array,
            required: true
        },

        roles: {
            type: Array,
            required: true
        }
    },

    data() {
        let specificitySpecialFieldsToDraw = {}
        const participantIsAdherentValue = isAdherent(this.roles);
        Object.entries(this.specialsFieldsSpecificity).forEach(
            ([key, value]) => {
                specificitySpecialFieldsToDraw[value.nom] = {};
                if (value.affichage_formulaire === 'nobody') {
                    return
                }
                else if (value.affichage_formulaire === 'only_adherents' && participantIsAdherentValue) {
                    specificitySpecialFieldsToDraw[value.nom].isDrawable = true;
                }
                else if(value.affichage_formulaire === 'only_non_adherents' && !participantIsAdherentValue) {
                    specificitySpecialFieldsToDraw[value.nom].isDrawable = true;
                }
                else {
                    specificitySpecialFieldsToDraw[value.nom].isDrawable = true;
                }

                if (value.mandatory_response === "everybody") {
                    specificitySpecialFieldsToDraw[value.nom].isMandatory = true;
                }
                else if (value.mandatory_response === "only_adherents" && participantIsAdherentValue) {
                    specificitySpecialFieldsToDraw[value.nom].isMandatory = true;
                }
                else if (value.mandatory_response === "only_non_adherents" && !participantIsAdherentValue) {
                    specificitySpecialFieldsToDraw[value.nom].isMandatory = true;
                }
                specificitySpecialFieldsToDraw[value.nom].document_choice = value.document_choice;

                specificitySpecialFieldsToDraw[value.nom].type_field = value.type_field;
            }
        )
        return {
            specificitySpecialFieldsToDraw: specificitySpecialFieldsToDraw,
            specialsFieldsData : this.specialsFieldsParticipant,
        };
    },

    watch: {
        specialsFieldsData: {
            deep: true,
            handler(newValue) {
                this.$emit('update:specialsFieldsParticipant', {...newValue});
            }
        }
    },

    methods: {
        getID(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
    }
}
</script>