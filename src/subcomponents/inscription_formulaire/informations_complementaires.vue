<template>
    <div v-if="isEncadrantComp" style="margin-bottom:10px;">
        <label class="block font-medium">Souhaitez-vous encadrer? <span style="color:red">*</span></label>
        <select v-model="wantsEncadrantData" class="w-full border p-1 rounded" required>
            <option disabled value="">-- Choisir --</option>
            <option :value="1">Je viens encadrer</option>
            <option :value="0">Je ne viens pas encadrer</option>
        </select>
    </div>

    <SpecialFieldPipelineComponent
        v-model:specialsFieldsParticipant="specialsFieldsParticipantData"
        :specialsFieldsSpecificity="specialsFields"
        :roles="roles"
    />
</template>

<script>
import SpecialFieldPipelineComponent from '@/subcomponents/special_field.vue/special_field_pipeline.vue'

export default {
    name: 'InformationsComplementairesComponent',

    signals: [
        'update:wantsEncadrant',
        'update:specialsFieldsParticipant'
    ],

    props: {
        wantsEncadrant: {
            type: Boolean,
            required: true
        },
        isEncadrantComp: {
            type: Boolean,
            required: true
        },
        specialsFields: {
            type: Array,
            required: true
        },
        specialsFieldsParticipant: {
            type: Object,
            required: true
        },
        roles: {
            type: Array,
            required: true
        }
    },

    watch: {
        wantsEncadrantData(newValue) {
            this.$emit('update:wantsEncadrant', newValue);
        },
        specialsFieldsParticipantData(newValue) {
            this.$emit('update:specialsFieldsParticipant', {...newValue});
        }
    },

    data() {
        return {
            wantsEncadrantData: this.wantsEncadrant,
            specialsFieldsParticipantData: this.specialsFieldsParticipant,
        };
    },

    components: {
        SpecialFieldPipelineComponent
    }
}

</script>