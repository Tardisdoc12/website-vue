<template>
    <div class="modal">
        <div class="modal-content">
            <!-- Entete -->
            <div class="encadre">
                <span>
                    <label style="color:rgb(255, 255, 255);"> {{ title }} </label>
                </span>
                <button class="appearance-none button-base" :style="{ '--btn-bg': Couleurs.main_red, '--btn-hover-bg': Couleurs.dark_red }" @click="Cancel">
                    <font-awesome-icon icon="fa-solid fa-xmark" />
                </button>
            </div>

            <!-- contenu passée -->
            <slot></slot>
        </div>
    </div>
</template>

<script>
import { Couleurs } from '@/javascript/constants/colors'

export default {
    props: {
        title: {
            type: String,
            required: true,
        }
    },

    data() {
        return {
            Couleurs
        }
    },

    methods: {
        Cancel() {
            this.$emit("changeBool")
        }
    }
}
</script>

<style>
.encadre {
    background-color: rgba(75, 76, 78, 0.84); 
    color: rgb(255, 255, 255);
    padding: 0.5rem 1rem;
    position: relative;  
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    display: flex;                /* 🔥 active flex */
    align-items: center;          /* centre verticalement */
    justify-content: space-between;
}

.encadre .btn-close {
    all: unset;                  /* reset tous les styles hérités */
    display: flex;               /* pour centrer l’icône */
    align-items: center;
    justify-content: center;

    background-color: rgba(192, 23, 23, 0.8);
    color: white;
    cursor: pointer;
    border-radius: 4px;

    padding: 4px;                /* padding adaptable */
    aspect-ratio: 1 / 1;
}

.encadre .btn-close:hover {
  background-color: rgba(255, 0, 0, 0.8); /* couleur au survol */
}

.modal {
    position: fixed;
    inset: 0;
    z-index: 2;

    background-color: rgba(100, 100, 100, 0.9);

    
    display: flex;              /* ✅ flex pour centrer */
    align-items: center;        /* ✅ centrage vertical */
    justify-content: center;      /* plus de flex centering */
    overflow-y: auto;     /* scroll sur toute la page si besoin */
    padding: 2rem;        /* espace autour de la fenêtre */
}

.modal-content {
    margin: auto;
    max-width: 600px;
    background: #fff;
    border-radius: 8px;
    padding: 0;
}
</style>