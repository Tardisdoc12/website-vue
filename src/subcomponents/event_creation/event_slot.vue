<template>
    <!-- limité dans le nombre de place -->
    <div class="flex items-center space-x-2" style="margin-bottom:10px;">
        <label class="font-medium" style="padding: 2px;">Nombre de place limité pour les adhérents :</label>
        <input type="checkbox" v-model="isChecked"/>
    </div>

    <!-- Nombre de places -->
    <div v-if="isChecked" style="margin-bottom:10px;">
        <label class="block font-medium">Nombre de places pour les adhérents</label>
        <input v-model.number="subscribeSlotsCount" type="number" min="1" class="w-full border p-1 rounded" required />
    </div>

    <!-- Nombre de places -->
    <div style="margin-bottom:10px;">
        <label class="block font-medium">Nombre de places pour les non-adhérents</label>
        <input v-model.number="nonsubscribeSlotsCount" type="number" min="0" class="w-full border p-1 rounded" required />
    </div>

    <template v-if="canAttente">
        <!-- Liste d'attentes -->
        <div style="margin-bottom: 10px;display: flex; align-items: center; gap: 8px;">
            <label class="block font-medium">Ajouter une liste d'attente?</label>
            <input type="checkbox" v-model="isCheckedAttente"/>
        </div>

        <div style="margin-bottom: 10px;" v-if="isCheckedAttente">
            <label class="block font-medium">Nombre de place dans la liste d'attente</label>
            <input type="number" v-model.number="attenteSlotsCount" min="0" class="w-full border p-1 rounded" required />
        </div>
    </template>

</template>

<script>
export default {
    name: "EventSlot",

    signals: [
        'update:subscribePlace',
        'update:nonsubscribePlace',
        'update:attentePlace',
        'next',
        'previous',
        'create'
    ],

    props: {
        isFree: {
            type: Boolean,
            default: false
        },

        canAttente: {
            type: Boolean,
            default: false
        },

        subscribePlace: {
            type: Number,
            default: 1
        },
        nonsubscribePlace: {
            type: Number,
            default: 0
        },

        attentePlace: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            subscribeSlots: this.subscribePlace,
            nonsubscribeSlots: this.nonsubscribePlace,
            attenteSlots: this.attentePlace,

            isChecked: false,
            isCheckedAttente: false,
        }
    },

    computed: {
        subscribeSlotsCount: {
            get() {
                return this.subscribeSlots;
            },
            set(value) {
                this.subscribeSlots = value;
                
                if (!this.isChecked){
                    this.subscribePlace = -1;
                }
                this.$emit('update:subscribePlace', value);
            }
        },
        nonsubscribeSlotsCount: {
            get() {
                return this.nonsubscribeSlots;
            },
            set(value) {
                this.nonsubscribeSlots = value;
                this.$emit('update:nonsubscribePlace', value);
            }
        },
        attenteSlotsCount: {
            get() {
                return this.attenteSlots;
            },
            set(value) {
                this.attenteSlots = value;
                this.$emit('update:attentePlace', value);
            }
        }
    },
}
</script>