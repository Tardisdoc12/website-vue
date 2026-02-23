<template>
    <h1 class="text-2xl font-bold mb-4">{{ Title }}</h1>
    <p>Cette page est en cours de développement. Elle permettra d'afficher les informations de suivi de l'utilisateur, telles que les progrès réalisés, les objectifs atteints, et d'autres données pertinentes pour le suivi de sa progression.</p>
    <button
        @click="Cancel"
        class="appearance-none button-base"
    >
        {{ "Retour à mon profil" }}
    </button>
</template>

<script>

export default{
    emits:["cancelSignal"],

    props:{
        user:{
            type: Object,
            required: false,
            default: null
        }
    },

    data() {
        return {

        }
    },

    computed:{
        Title() {
            if(this.user){
                return "Fiche de Suivi"
            }
            return "Error"
        }
    },

    methods:{
        Cancel() {
            this.$emit("cancelSignal")
        }
    }
}
</script>