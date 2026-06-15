<template>
     <form @submit.prevent="handleSubmit" class="space-y-4">
        <div style="margin-left: 20px; margin-right: 20px;margin-top: 10px;">
            <!-- Titre -->
            <div class="flex flex-col gap-1" style="margin-bottom:10px;">
                <label class="block font-medium">
                    Titre
                </label>
                <input
                    v-model="titleForm"
                    type="text"
                    class="w-full border p-1 rounded"
                    required
                />
            </div>

            <!-- Date de début -->
            <div style="margin-bottom:10px;">
                <label class="block font-medium">
                    Date et heure de début
                </label>
                <input
                    v-model="startDateForm"
                    type="datetime-local"
                    class="w-full border p-1 rounded"
                    required 
                />
            </div>

            <!-- Date de fin -->
            <div style="margin-bottom:10px;">
                <label class="block font-medium">Date et heure de fin</label>
                <input v-model="endDateForm" type="datetime-local" class="w-full border p-1 rounded"/>
            </div>

            <!-- Description -->
            <div class="rte-wrap">
                <label class="rte-label">Description</label>
                <div class="rte-box">
                    <div class="rte-toolbar" v-if="editor">
                        <button type="button" class="rte-btn" @click="editor.chain().focus().toggleBold().run()" :class="{ active: editor.isActive('bold') }">
                        <b>G</b>
                        </button>
                        <button type="button" class="rte-btn" @click="editor.chain().focus().toggleItalic().run()" :class="{ active: editor.isActive('italic') }">
                        <i>I</i>
                        </button>
                        <button type="button" class="rte-btn" @click="editor.chain().focus().toggleUnderline().run()" :class="{ active: editor.isActive('underline') }">
                        <u>S</u>
                        </button>
                        <div class="rte-sep"></div>
                        <div class="rte-color-wrap">
                        <div class="rte-color-btn" :style="{ background: currentColor }">
                            <input type="color" v-model="currentColor" @input="editor.chain().focus().setColor(currentColor).run()" />
                        </div>
                        </div>
                    </div>
                    <EditorContent :editor="editor" class="rte-content" />
                </div>
            </div>

            <!-- Catégorie -->
            <div style="margin-bottom:10px;">
                <label class="block font-medium">Catégorie</label>
                <select
                    v-model="categorieForm"
                    class="w-full border p-1 rounded"
                    required
                >
                    <option disabled value="">-- Choisir une catégorie --</option>
                    <option :value="Events.seance">Seance</option>
                    <option :value="Events.stage">Stage</option>
                    <option :value="Events.balade">Balade</option>
                </select>
            </div>

            <!-- Lien billetterie -->
            <div style="margin-bottom:10px;" v-if="categorieForm === Events.stage">
                <label class="block font-medium">Lien billetterie (optionnel)</label>
                <input
                    v-model="billeterieForm"
                    type="url"
                    class="w-full border p-1 rounded"
                    placeholder="https://example.com/billetterie"
                />
            </div>

            <!-- Liste d'attentes -->
            <div style="margin-bottom: 10px;" v-if="categorieForm !== Events.balade">
                <label class="block font-medium">Ajouter une liste d'attente?</label>
                <input type="checkbox" v-model="isCheckedAttente"/>
            </div>

            <div style="margin-bottom: 10px;" v-if="categorieForm !== Events.balade && isCheckedAttente">
                <label class="block font-medium">Nombre de place dans la liste d'attente</label>
                <input type="number" v-model.number="attentePlaceForm" min="0" class="w-full border p-1 rounded" required />
            </div>

            <!-- Lieu -->
            <div style="margin-bottom:10px;">
                <label class="block font-medium">Lieu</label>
                <input
                    v-model="placeForm"
                    type="text"
                    class="w-full border p-1 rounded"
                    required
                />
            </div>

            <!-- limité dans le nombre de place -->
            <div class="flex items-center space-x-2" style="margin-bottom:10px;">
                <label class="font-medium" style="padding: 2px;">Nombre de place limité pour les adhérents :</label>
                <input type="checkbox" v-model="isChecked"/>
            </div>

            <!-- Nombre de places -->
            <div v-if="isChecked" style="margin-bottom:10px;">
                <label class="block font-medium">Nombre de places pour les adhérents</label>
                <input v-model.number="subscribePlaceForm" type="number" min="1" class="w-full border p-1 rounded" required />
            </div>

            <!-- Nombre de places -->
            <div style="margin-bottom:10px;">
                <label class="block font-medium">Nombre de places pour les non-adhérents</label>
                <input v-model.number="nonsubscribePlaceForm" type="number" min="0" class="w-full border p-1 rounded" required />
            </div>

            <!-- Bouton -->
            <div class="flex justify-center items-center" style="margin-bottom:10px;">
                <button type="submit" class="appearance-none button-base" >
                    {{ buttonName }}
                </button>
            </div>
        </div>
    </form>
</template>

<script>
import eventsService from '@/javascript/api/axios_events.js';
import { Events } from "@/javascript/constants/events_type.js"
import { Couleurs } from "@/javascript/constants/colors"
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { Color } from '@tiptap/extension-color'
import { TextStyle } from '@tiptap/extension-text-style'
import { Underline } from '@tiptap/extension-underline'

export default {
    props: {
        onSuccess: {
            type: Function,
            default: null,
        },
        eventSelected: {
            type: Object,
            default: null,
        }
    },

    mounted() {
        if (this.eventSelected) {
            this.event = {...this.eventSelected}
            this.isUpdate = true
        }
       this.editor = new Editor({
            content: this.event.description || '<p>Écris ton texte ici...</p>',
            extensions: [StarterKit, TextStyle, Color, Underline],
        })
    },

    beforeUnmount() {
        if (this.editor) {
            this.editor.destroy()
        }
    },

    data() {
        return {
            Couleurs,
            editor: null,
            event: {
                title: '',
                startDate: '',
                endDate: '',
                description: '',
                place: '',
                categorie: '',
                subscribePlace: 1,
                nonsubscribePlace: 0,
                attentePlace: 0,
                billeterie_url: '',
            },
            isChecked:false || this?.eventSelected?.subscribePlace >= 0,
            isCheckedAttente: false || this?.eventSelected?.attentePlace > 0,
            Events: Events,
            isUpdate:false,
            currentColor: Couleurs.main_blue,
        }
    },

    computed: {
        buttonName() {
            return this.isUpdate ? "Modifier l'évènement" : "Créer l'événement"
        },

        titleName() {
            return this.isUpdate ? "Modification d'évènement" : "Création d'évènement"
        },

        descriptionForm: {
            get() {
                return this.editor.getHTML()
            },
            set(newValue) {
                this.editor.commands.setContent(newValue)
            }
        },

        billeterieForm: {
            get() {
                return this.event.billeterie_url
            },
            set(newValue) {
                this.event.billeterie_url = newValue
            }
        },

        titleForm: {
            get() {
                return this.event.title
            },
            set(newValue) {
                this.event.title = newValue
            }
        },

        attentePlaceForm: {
            get() {
                return this.event.attentePlace
            },
            set(newValue) {
                this.event.attentePlace = newValue
            }
        },


        startDateForm: {
            get() {
                return this.event.startDate
            },
            set(newValue) {
                this.event.startDate = newValue
            }
        },

        endDateForm: {
            get() {
                return this.event.endDate
            },
            set(newValue) {
                this.event.endDate = newValue
            }
        },

        placeForm: {
            get() {
                return this.event.place
            },
            set(newValue) {
                this.event.place = newValue
            }
        },

        subscribePlaceForm: {
            get() {
                return this.event.subscribePlace
            },
            set(newValue) {
                this.event.subscribePlace = newValue
            }
        },

        categorieForm: {
            get() {
                return this.event.categorie
            },
            set(newValue) {
                this.event.categorie = newValue
            }
        },

        nonsubscribePlaceForm: {
            get() {
                return this.event.nonsubscribePlace
            },
            set(newValue) {
                this.event.nonsubscribePlace = newValue
            }
        }
    },

    methods: {
        addRange() {
            this.cloneDates.push({ start_date: '', end_date: '' });
        },

        removeRange(index) {
            this.cloneDates.splice(index, 1);
        },

        async createEvent(event) {
            if (event.endDate !== '') {
                if (new Date(event.startDate) >= new Date(event.endDate)) {
                    alert("La date de fin doit être après la date de début.")
                    return
                }
            }
            else {
                event.endDate = null;
            }
            event.description = this.editor.getHTML()

            const response = await eventsService.createEvent(event)
            return response;
        },

        async handleSubmit() {
            if (!this.isChecked){
                this.subscribePlaceForm = -1
            }
            
            if (!this.isUpdate){
                
                const response = await this.createEvent(this.event)
                if(response?.data?.id){
                    const event = {
                        ...this.event,
                        id : response.data.id,
                        post_id : response.data.post_id,
                        users: [],
                    }
                    this.$emit("createEvents", event)
                }
                this.event = {
                    title: '',
                    startDate: '',
                    endDate: '',
                    description: this.event.description, // ← on garde l'instance existante
                    place: '',
                    categorie: '',
                    subscribePlace: 1,
                    nonsubscribePlace: 0,
                    attentePlace: 0,
                    billeterie_url: '',
                }
                if (this.onSuccess) {
                    await this.onSuccess()
                }
            }
            else {
                const payload = {
                    title:              this.event.title,
                    startDate:          this.event.startDate,
                    endDate:            this.event.endDate,
                    description:        this.editor.getHTML(),
                    place:              this.event.place,
                    categorie:          this.event.categorie,
                    subscribePlace:     this.event.subscribePlace,
                    nonsubscribePlace:  this.event.nonsubscribePlace,
                    attentePlace:       this.event.attentePlace,
                    billeterie_url:     this.event.billeterie_url,
                }

                const response = await eventsService.updateEvent(this.event.event_id, payload)
                alert("Évènement modifié avec succés !")
                this.$emit('cancelSignal', !this.isOpen)
            }
        }
    },

    components: {
        EditorContent,
    }
}
</script>

<style>
.rte-wrap { margin-bottom: 10px; }
.rte-label { font-size: 13px; color: #666; font-weight: 500; margin-bottom: 8px; display: block; }
.rte-box { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; }
.rte-toolbar { display: flex; align-items: center; gap: 2px; padding: 6px 8px; border-bottom: 1px solid #eee; background: #f9f9f9; }
.rte-btn { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; border: 1px solid transparent; border-radius: 6px; background: transparent; cursor: pointer; color: #555; font-size: 14px; }
.rte-btn:hover { background: #fff; border-color: #ddd; }
.rte-btn.active { background: #fff; border-color: #bbb; color: #111; }
.rte-sep { width: 1px; height: 20px; background: #e0e0e0; margin: 0 4px; }
.rte-color-btn { width: 22px; height: 22px; border-radius: 50%; border: 2px solid #ccc; cursor: pointer; position: relative; overflow: hidden; }
.rte-color-btn input[type=color] { position: absolute; inset: -4px; opacity: 0; cursor: pointer; width: 30px; height: 30px; }
.rte-content :deep(.ProseMirror) { min-height: 140px; padding: 12px 14px; font-size: 15px; line-height: 1.6; outline: none; }
.rte-content :deep(.ProseMirror p.is-editor-empty:first-child::before) { content: 'Écris ta description ici...'; color: #aaa; pointer-events: none; float: left; height: 0; }

</style>
