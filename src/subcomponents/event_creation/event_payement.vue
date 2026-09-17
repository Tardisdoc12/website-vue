<template>
    <form @submit.prevent="handleSubmit">
        <p class="help-text">Cette option permet de modifier le nom du paiement affiché aux utilisateurs.</p>
        <p class="help-text">Si cette option est désactivée, le nom par défaut (titre - Date) sera utilisé.</p>
        <div style="display: flex; align-items: center; gap: 8px;">
            <label class="block font-medium">Changer le nom du payement</label>
            <input type="checkbox" v-model="isChangedTitle"/>
        </div>
        
        <div v-if="isChangedTitle" class="flex flex-col gap-1" style="margin-top:10px;">
            <label class="block font-medium">
                Titre du payement
            </label>
            <input
                v-model="payementTitleComp"
                type="text"
                class="w-full border p-1 rounded"
                required
            />
        </div>

        <div v-if="canAdherentPayement" class="flex flex-col gap-1" style="margin-bottom:10px;margin-top:10px;">
            <label class="block font-medium">
                Montant du payement pour les Adhérents
            </label>
            <input
                v-model="payementAmountAdherentComp"
                type="number"
                class="w-full border p-1 rounded"
                min="0"
                step="0.01"
                placeholder="--,--€"
                required
            />
        </div>

        <div v-if="canNonAdherentPayement" class="flex flex-col gap-1" style="margin-bottom:10px;">
            <label class="block font-medium">
                Montant du payement pour les Non-Adhérents
            </label>
            <input
                v-model="payementAmountNonAdherentComp"
                type="number"
                class="w-full border p-1 rounded"
                min="0"
                step="0.01"
                placeholder="--,--€"
                required
            />
        </div>

        
        <div style="margin-top:10px;margin-bottom:10px;" class="flex justify-center gap-3">
            <button
                type="button"
                class="appearance-none button-base"
                @click="handlePrevious"
            >
                Précédent
            </button>

            <button
                type="submit"
                class="appearance-none button-base"
                :style="{
                    '--btn-bg':'var(--validate-color)',
                    '--btn-hover-bg':'var(--validate-color-hover)'
                }"
            >
                Créer
            </button>
        </div>
    </form>
</template>

<script>

export default {
    name: "EventPayement",

    signals: [
        'update:payementTitle',
        'update:payementAmountAdherent',
        'update:payementAmountNonAdherent',
        'previous',
        'create'
    ],

    props: {
        EventsPayementAdherent: {
            type: Number,
            default: 0
        },
        EventsPayementNonAdherent: {
            type: Number,
            default: 0
        },

        EventPayementTitle: {
            type: String,
            default: ""
        },

        canAdherentPayement: {
            type: Boolean,
            default: false
        },

        canNonAdherentPayement: {
            type: Boolean,
            default: true
        }
    },
    
    data() {
        return {
            isChangedTitle: false,
            payementTitle: this.EventPayementTitle,
            payementAmountAdherent: this.EventsPayementAdherent,
            payementAmountNonAdherent: this.EventsPayementNonAdherent
        };
    },

    computed: {
        payementAmountAdherentComp: {
            get() { return this.payementAmountAdherent },
            set(val) {
                this.payementAmountAdherent = val;
                this.$emit('update:payementAmountAdherent', val * 100);
            }
        },
        payementAmountNonAdherentComp: {
            get() { return this.payementAmountNonAdherent },
            set(val) {
                this.payementAmountNonAdherent = val;
                this.$emit('update:payementAmountNonAdherent', val * 100);
            }
        },

        payementTitleComp: {
            get() { return this.payementTitle },
            set(val) {
                this.payementTitle = val;
                this.$emit('update:payementTitle', val);
            }
        }
    },

    methods: {
        handlePrevious() {
            this.$emit("previous");
        },
        handleSubmit() {
            this.$emit("create")
        }
    }
};

</script>

<style scoped>
.help-text {
    font-size: 0.8rem;
    color: #666;
    margin: 2px 0;
    line-height: 1.3;
}
</style>