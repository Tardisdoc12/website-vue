<template>
  <div>
    <div v-if="isOnePerson" style="margin-bottom: 1rem;">
        <label class="block font-medium">Destinataire(s) :</label>

        <div class="multiselect" ref="multiselect">
            <button
            type="button"
            class="multiselect-btn"
            :class="{ active: open }"
            @click="open = !open"
            >
            <span v-if="!to.length" class="placeholder">Choisir des destinataires…</span>

            <span v-else class="tags">
                <span v-for="mail in to" :key="mail" class="tag">
                {{ mail }}
                <span class="tag-remove" @click.stop="removeEmail(mail)">×</span>
                </span>
            </span>

            <span class="arrow" :class="{ up: open }">▾</span>
            </button>

            <div v-if="open" class="multiselect-options">
            <label v-for="userEmail in usersEmails" :key="userEmail" class="option">
                <input type="checkbox" v-model="to" :value="userEmail" />
                <span>{{ userEmail }}</span>
            </label>

            <div v-if="!usersEmails.length" class="empty">Aucun email disponible</div>
            </div>
        </div>
    </div>

    <div v-else-if="isAllPerson" style="margin-bottom: 1rem;">
      <label class="block font-medium">Tous les destinataires</label>
    </div>

    <div v-else style="margin-bottom: 1rem;">
      <label class="block font-medium">Aucun destinataire</label>
    </div>

    <div style="margin-bottom: 1rem;">
      <label class="block font-medium">Modèles :</label>
      <select v-model="selectedTemplate" class="w-full border p-1 rounded">
        <option v-for="template in templates" :key="template" :value="template">
          {{ template.name_template }}
        </option>
      </select>
    </div>

    <div style="margin-bottom: 1rem;">
      <label class="block font-medium">
        Sujet :
        <input
            type="text"
            v-model="subject"
            class="w-full border p-1 rounded"
        />
      </label>
    </div>

    <div style="margin-bottom: 1rem;">
        <label class="block font-medium">
            Message :
        </label>
        <p v-if="!eventId">
            <span v-pre>Vous pouvez utiliser les balises suivantes : {{user_firstName}}, {{user_lastName}} et {{date}}</span>
        </p>
        <p v-else>
            <span v-pre>Vous pouvez utiliser les balises suivantes : {{user_firstName}}, {{user_lastName}}, {{date}}, {{event_date}}, {{event_place}} et {{event_name}}</span>
        </p>
        <textarea
            v-model="message"
            class="w-full border p-1 rounded"
        ></textarea>
    </div>

    <div style="margin-bottom: 1rem;">
        <label>{{ "Fichier à joindre (PDF/JPG/PNG)" }}</label>
        <div class="border border-gray-300 rounded-lg p-3 flex items-center justify-between">
            <input
                type="file"
                accept="application/pdf,image/png,image/jpeg"
                @change="handleFileUpload"
                @cancel.stop
            />
        </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "MailingTemplate",

  props: {
    isOnePerson: { type: Boolean, default: false },
    isAllPerson: { type: Boolean, default: true },
    email: {
      type: Object,
      default: () => ({ to: [], subject: '', message: '', attachments: null })
    },
    templates: {
      type: Array,
      default: () => []
    },
    usersEmails: { type: Array, default: () => [] },
    eventId: {
        type: Number,
        required: false
    }
  },

  emits: ["update:email"],

  data() {
    return { open: false };
  },

  computed: {
    to: {
      get() { return this.email.to ?? []; },
      set(value) { this.update('to', value); }
    },
    subject: {
      get() { return this.email.subject ?? ''; },
      set(value) { this.update('subject', value); }
    },
    message: {
      get() { return this.email.message ?? ''; },
      set(value) { this.update('message', value); }
    },

    attachments: {
      get() { return this.email.attachments ?? null; },
      set(value) { this.update('attachments', value); }
    },

    selectedTemplate: {
      get() { return this.email.selectedTemplate ?? null; },
      set(value) {
        this.updateMany({
          selectedTemplate: value,
          subject: value?.objet ?? '',
          attachments: this.email.attachments ?? null,
          message: value?.template ?? ''
        });
      }
    }
  },

  methods: {
    handleFileUpload(event) {
        this.attachments = event.target.files[0]
    },
    update(field, value) {
        this.$emit('update:email', { ...this.email, [field]: value });
    },
    updateMany(fields) {
        this.$emit('update:email', { ...this.email, ...fields });
    },
    removeEmail(mail) {
        this.to = this.to.filter(m => m !== mail);
    },
    onClickOutside(e) {
        const el = this.$refs.multiselect;
        if (el && !el.contains(e.target)) this.open = false;
    }
    },

    mounted() {
        document.addEventListener('click', this.onClickOutside);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside);
    }
};
</script>

<style scoped>
.multiselect {
  position: relative;
  width: 100%;
  max-width: 400px;
  margin-top: 4px;
}

.multiselect-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  width: 100%;
  min-height: 40px;
  padding: 6px 12px;
  text-align: left;
  background: #fff;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  cursor: pointer;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.multiselect-btn:hover {
  border-color: #9ca3af;
}

.multiselect-btn.active {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.placeholder {
  color: #9ca3af;
}

.tags {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  flex: 1;
}

.tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  font-size: 0.85rem;
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  border-radius: 999px;
}

.tag-remove {
  cursor: pointer;
  font-weight: bold;
  line-height: 1;
}

.tag-remove:hover {
  color: #dc2626;
}

.arrow {
  color: #6b7280;
  transition: transform 0.2s;
}

.arrow.up {
  transform: rotate(180deg);
}

.multiselect-options {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  max-height: 220px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
              0 4px 6px -4px rgba(0, 0, 0, 0.1);
  z-index: 20;
}

.option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  cursor: pointer;
}

.option:hover {
  background: #f3f4f6;
}

.option input {
  accent-color: #3b82f6;
  width: 16px;
  height: 16px;
}

.empty {
  padding: 12px;
  color: #9ca3af;
  text-align: center;
}
</style>