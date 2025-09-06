<template>
    <div class="modal">
        <div class="modal-content">
            <!-- Entete -->
            <div class="encadre">
                <span>
                    <label style="color:rgb(255, 255, 255);"> {{ title }} </label>
                </span>
                <button class="btn-close" @click="Cancel">
                    <font-awesome-icon icon="fa-solid fa-xmark" />
                </button>
            </div>

            <!-- contenu passée -->
            <slot></slot>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        title: {
            type: String,
            required: true,
        },
        isCancel: {
            type: Boolean,
            required: true
        }
    },

    data() {
        return {
            cancelBool: this.isCancel,
        }
    },

    methods: {
        Cancel() {
            this.$emit("changeBool", !this.cancelBool)
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
    inset: 0px;

    z-index: 2;
    background-color: rgba(100,100,100,0.9); /* derrière la modal */

    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    flex-basis: 600px;
    background: #fff;
    border-radius: 8px;
    padding: 0;
    overflow: hidden;  
}
</style>