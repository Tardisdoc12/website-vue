<template>
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

    <div v-if="Boolean(+canAdherentPayement)" class="flex flex-col gap-1" style="margin-bottom:10px;margin-top:10px;">
        <label class="block font-medium">
            Montant du payement pour les Adhérents <span style="color:darkred">*</span>
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

    <div v-if="Boolean(+canNonAdherentPayement)" class="flex flex-col gap-1" style="margin-bottom:10px;">
        <label class="block font-medium">
            Montant du payement pour les Non-Adhérents <span style="color:darkred">*</span>
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
</template>

<script>

export default {
    name: "EventPayement",

    signals: [
        'update:EventPayementTitle',
        'update:EventsPayementAdherent',
        'update:EventsPayementNonAdherent',
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
        console.log("Initializing data with the payement flags:", this.canAdherentPayement, this.canNonAdherentPayement);
        return {
            isChangedTitle: this.EventPayementTitle !== "",
            payementTitle: this.EventPayementTitle,
            payementAmountAdherent: this.EventsPayementAdherent / 100,
            payementAmountNonAdherent: this.EventsPayementNonAdherent / 100
        };
    },

    computed: {
        payementAmountAdherentComp: {
            get() { return this.payementAmountAdherent },
            set(val) {
                this.payementAmountAdherent = val;
                this.$emit('update:EventsPayementAdherent', val * 100);
            }
        },
        payementAmountNonAdherentComp: {
            get() { return this.payementAmountNonAdherent },
            set(val) {
                this.payementAmountNonAdherent = val;
                this.$emit('update:EventsPayementNonAdherent', val * 100);
            }
        },

        payementTitleComp: {
            get() { return this.payementTitle },
            set(val) {
                this.payementTitle = val;
                this.$emit('update:EventPayementTitle', val);
            }
        }
    },
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