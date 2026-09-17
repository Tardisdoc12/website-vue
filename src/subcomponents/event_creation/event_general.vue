<template>
    <!-- Titre -->
    <div class="flex flex-col gap-1" style="margin-bottom:10px;">
        <label class="block font-medium">Titre <span style="color:darkred">*</span></label>
        <input v-model="Title" type="text" class="w-full border p-1 rounded" required />
    </div>

    <!-- Date de début -->
    <div style="margin-bottom:10px;">
        <label class="block font-medium">Date et heure de début <span style="color:darkred">*</span></label>
        <input v-model="StartDate" type="datetime-local" class="w-full border p-1 rounded" required />
    </div>

    <!-- Date de fin -->
    <div style="margin-bottom:10px;">
        <label class="block font-medium">Date et heure de fin</label>
        <input v-model="EndDate" type="datetime-local" class="w-full border p-1 rounded" />
    </div>

    <!-- Description -->
    <div class="rte-wrap">
        <label class="block font-medium">Description</label>
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
                    <div class="rte-color-btn" :style="{ background: Color }">
                        <input type="color" v-model="Color" />
                    </div>
                </div>
            </div>
            <EditorContent :editor="editor" class="rte-content" />
        </div>
    </div>
</template>

<script>
import { EditorContent } from '@tiptap/vue-3'

export default {
    name: "EventGeneral",

    emits: [
        'update:titleForm',
        'update:startDateForm',
        'update:endDateForm',
        'update:currentColor',
        'next',
        'cancel'
    ],

    props: {
        titleForm: { type: String, required: true },
        startDateForm: { type: String, required: true },
        endDateForm: { type: String, required: false, default: '' },
        editor: { type: Object, required: true },
        currentColor: { type: String, required: true }
    },

    computed: {
        Title: {
            get() { return this.titleForm },
            set(val) { this.$emit('update:titleForm', val) }
        },
        StartDate: {
            get() { return this.startDateForm },
            set(val) { this.$emit('update:startDateForm', val) }
        },
        EndDate: {
            get() { return this.endDateForm },
            set(val) { this.$emit('update:endDateForm', val) }
        },
        Color: {
            get() { return this.currentColor },
            set(val) {
                this.$emit('update:currentColor', val)
                if (this.editor) {
                    this.editor.chain().focus().setColor(val).run()
                }
            }
        }
    },

    methods: {
        handleSubmit() {
            this.$emit('next')
        },
        Cancel() {
            this.$emit('cancel')
        }
    },

    components: {
        EditorContent
    }
}
</script>

<style>
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