<template>
    <div class="modal">
        <div :style="ModalContent">
            <!-- Entete -->
            <div class="encadre">
                <span>
                    <label style="color:var(--writing-modale-title-color);"> {{ title }} </label>
                </span>
                <button 
                    class="appearance-none button-base" 
                    :style="{ 
                        '--btn-bg': 'var(--cancel-color)', 
                        '--btn-hover-bg': 'var(--cancel-hover-color)',
                        '--btn-color': 'var(--writing-modale-title-color)',
                        '--btn-hover-color': 'var(--writing-modale-title-color)'
                    }"
                    @click="Cancel"
                >
                    <font-awesome-icon icon="fa-solid fa-xmark" />
                </button>
            </div>
            <div :style="{
                'background-color': 'var(--modale-body-background-color)',
                'color': 'var(--writing-modale-body-color)'
            }">
                <!-- contenu passée -->
                <slot></slot>
            </div>
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
        width:{
            type: String,
            required: false,
            default: null
        }
    },

    computed:{
        ModalContent(){
            let modal_content= {
                'margin': 'auto',
                'background': '#fff',
                'border-radius': '8px',
                'padding': '0',
                'max-height': '90vh',
                'overflow-y': 'auto',
            }
            if(this.width) {
                return {
                    ...modal_content,
                    'width': this.width
                }
            }
            return modal_content
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
    background-color: var(--modale-header-background-color);
    color: var(--writing-modale-title-color);
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

    background-color: var(--cancel-color);
    color: var(--writing-modale-title-color);
    cursor: pointer;
    border-radius: 4px;

    padding: 4px;                /* padding adaptable */
    aspect-ratio: 1 / 1;
}

.encadre .btn-close:hover {
  background-color: var(--cancel-hover-color); /* couleur au survol */
  color: var(--writing-modale-title-color);
}

.modal {
    position: fixed;
    inset: 0;
    z-index: 2;

    background-color: var(--modale-overlay-color);

    
    display: flex;              /* ✅ flex pour centrer */
    align-items: center;        /* ✅ centrage vertical */
    justify-content: center;      /* plus de flex centering */
    overflow-y: auto;     /* scroll sur toute la page si besoin */
    padding: 2rem;        /* espace autour de la fenêtre */
}
</style>