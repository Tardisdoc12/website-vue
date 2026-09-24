<template>
    <div class="flex flex-col gap-1">
        <label class="block font-medium">Prénom<span style="color:red">*</span></label>
        <input v-model="firstName" type="text" class="w-full border p-1 rounded" required />
    </div>
    <div class="flex flex-col gap-1">
        <label class="block font-medium">Nom<span style="color:red">*</span></label>
        <input v-model="lastName" type="text" class="w-full border p-1 rounded" required />
    </div>
    <div class="flex flex-col gap-1">
        <label class="block font-medium">E-mail <span style="color:red">*</span></label>
        <input v-model="email" type="email" class="w-full border p-1 rounded" required />
    </div>
    <div class="flex flex-col gap-1">
        <label class="block font-medium">Téléphone <span style="color:red">*</span></label>
        <input v-model="phone" type="tel" class="w-full border p-1 rounded" pattern="[0-9]{10}" required />
    </div>
</template>

<script>

export default {
    name: 'InformationsPersonnellesComponent',

    signals: [
        'update:informationsPersonnelles'
    ],

    props: {
        informationsPersonnelles: {
            type: Object,
            required: true
        },
    },

    watch: {
        firstName(newValue) {
            this.$emit('update:informationsPersonnelles', { ...this.informationsPersonnelles, firstName: newValue });
        },
        lastName(newValue) {
            this.$emit('update:informationsPersonnelles', { ...this.informationsPersonnelles, lastName: newValue });
        },
        email(newValue) {
            this.$emit('update:informationsPersonnelles', { ...this.informationsPersonnelles, email: newValue });
        },
        phone(newValue) {
            this.$emit('update:informationsPersonnelles', { ...this.informationsPersonnelles, phone: newValue });
        },
        wantsEncadrant(newValue) {
            this.$emit('update:informationsPersonnelles', { ...this.informationsPersonnelles, wantsEncadrant: newValue });
        },
        isEncadrantComp(newValue) {
            this.$emit('update:informationsPersonnelles', { ...this.informationsPersonnelles, isEncadrantComp: newValue });
        },
    },

    data() {
        
        return {
            firstName: this.informationsPersonnelles.firstName,
            lastName: this.informationsPersonnelles.lastName,
            email: this.informationsPersonnelles.email,
            phone: this.informationsPersonnelles.phone,
            wantsEncadrant: this.informationsPersonnelles.wantsEncadrant,
            isEncadrantComp: this.informationsPersonnelles.isEncadrantComp
        };
    },
}
</script>