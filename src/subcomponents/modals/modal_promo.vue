<template>
    <ModalComponent
        :title="'Code de Promotion'"
        :width="'80%'"
        @changeBool="handleChangeBool"
    >
        <div
            style="margin-left: 20px; margin-right: 20px;margin-top: 10px; margin-bottom: 10px;"
        >
            <div class="flex flex-col gap-1" style="margin-bottom:10px;">
                <label class="block font-medium">Code de Promotion :</label>
                <input v-model="promoCode" placeholder="Enter promo code"
                class="w-full border p-1 rounded"
                />
            </div>
            <div v-if="loading">Checking promo code...</div>
            <p v-if="error">{{ message }}</p>
            <p v-else-if="message">{{ message }}</p>
            <div style="margin-top:10px;margin-bottom:10px;" class="flex justify-center gap-3">
                <button v-if="!loading" @click="checkPromo(promoCode, eventId)" :disabled="loading" class="button-validate" >Check Promo</button>
                <button @click="DoNotPromo()" class="button-base" >Je n'ai pas de code de promotion</button>
            </div>
        </div>
    </ModalComponent>
</template>

<script>
import { verifyPromo } from '@/javascript/api/api_promo'
import ModalComponent from '@/subcomponents/unitary_elements/modalComponent.vue';

export default {
    name: 'ModalPromo',
    signals: ['promoVerified', 'cancel'],
    props:{
        eventId: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            promoCode: '',
            loading: false,
            error: false,
            message: ''
        };
    },
    methods: {
        handleChangeBool() {
            this.$emit('cancel');
        },
        DoNotPromo() {
            this.$emit('promoVerified', {promo_type:'percent', promo_value:0})
            this.$emit('cancel');
        },

        async checkPromo(promoCode, eventId) {
            try {
                this.loading = true;
                this.error = false;
                this.message = '';
                const result = await verifyPromo(promoCode, eventId);
                console.log('Promo verification result:', result);
                if (result.success) {
                    this.$emit('promoVerified', result.promo);
                }
                else {
                    this.error = true;
                }
                this.message = result.message || 'Invalid promo code';
            } catch (error) {
                console.error('Error verifying promo code:', error);
                this.message = 'Error verifying promo code';
            } finally {
                this.loading = false;
            }
        }
    },
    components: {
        ModalComponent
    }
}
</script>