<template>
    <div>
        <!-- Indicateur d'étapes -->
        <div class="steps-indicator">
            <div
                v-for="(p, index) in participants"
                :key="index"
                class="step-dot"
                :class="{ active: currentStep === index, done: currentStep > index }"
                @click="currentStep > index ? currentStep = index : null"
            >
                <span v-if="currentStep > index">✓</span>
                <span v-else>{{ index + 1 }}</span>
            </div>
        </div>

        <!-- Supprimer le participant courant (jamais sur l'étape 0) -->
        <div v-if="currentStep > 0" style="text-align:right; margin: 0 20px;">
            <button type="button" class="button-remove" @click="removeParticipant(currentStep)">
                ✕ Retirer ce participant
            </button>
        </div>

        <!-- Étape courante -->
        <div style="margin-left: 20px; margin-right: 20px; margin-top: 10px;">

            <!-- Titre de l'étape -->
            <p class="step-title">
                <span v-if="currentStep === 0">Votre inscription</span>
                <span v-else>Participant {{ currentStep + 1 }}</span>
            </p>

            <!-- ───── ÉTAPE 0 : formulaire principal (toi) ───── -->
            <template v-if="currentStep === 0">
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">Prénom et Nom <span style="color:red">*</span></label>
                    <input v-model="participants[0].name" type="text" class="w-full border p-1 rounded" required />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">E-mail <span style="color:red">*</span></label>
                    <input v-model="participants[0].email" type="email" class="w-full border p-1 rounded" required />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">Téléphone <span style="color:red">*</span></label>
                    <input v-model="participants[0].phone" type="tel" class="w-full border p-1 rounded" pattern="[0-9]{10}" required />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">Moto/Cylindré <span style="color:red">*</span></label>
                    <input v-model="participants[0].bike" type="text" class="w-full border p-1 rounded" required />
                </div>
                <div v-if="isEncadrantComp" style="margin-bottom:10px;">
                    <label class="block font-medium">Souhaitez-vous encadrer? <span style="color:red">*</span></label>
                    <select v-model="participants[0].wantsEncadrant" class="w-full border p-1 rounded" required>
                        <option disabled value="">-- Choisir --</option>
                        <option :value="1">Je viens encadrer</option>
                        <option :value="0">Je ne viens pas encadrer</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1" v-if="!isAdherent">
                    <label class="block font-medium">Quelle est votre expérience à moto? <span style="color:red">*</span></label>
                    <textarea v-model="participants[0].experience" class="w-full border p-1 rounded" rows="3" required></textarea>
                </div>
                <div v-if="isSeance" class="flex flex-col gap-1">
                    <label class="block font-medium">Souhaitez-vous travailler un thème particulier?</label>
                    <textarea v-model="participants[0].goal" class="w-full border p-1 rounded" rows="3"></textarea>
                </div>
            </template>

            <!-- ───── ÉTAPES 1+ : participants supplémentaires ───── -->
            <template v-else>

                <!-- 1. A-t-il un compte ? -->
                <div class="flex flex-col gap-1">
                    <label class="block font-medium">Ce participant a-t-il un compte ? <span style="color:red">*</span></label>
                    <div style="display:flex; gap:10px;">
                        <button
                            type="button"
                            class="toggle-btn"
                            :class="{ 'toggle-btn--active': participants[currentStep].hasAccount === true }"
                            @click="setHasAccount(currentStep, true)"
                        >Oui</button>
                        <button
                            type="button"
                            class="toggle-btn"
                            :class="{ 'toggle-btn--active': participants[currentStep].hasAccount === false }"
                            @click="setHasAccount(currentStep, false)"
                        >Non</button>
                    </div>
                </div>

                <!-- 2a. OUI → recherche du compte -->
                <template v-if="participants[currentStep].hasAccount === true">
                    <div class="flex flex-col gap-1">
                        <label class="block font-medium">Email ou téléphone</label>
                        <div style="display:flex; gap:8px;">
                            <input
                                v-model="participants[currentStep].searchQuery"
                                type="text"
                                class="w-full border p-1 rounded"
                                placeholder="ex: jean@mail.com ou 0612345678"
                                @input="participants[currentStep].searchResult = null"
                            />
                            <button type="button" class="button-base" style="white-space:nowrap;" @click="searchParticipant(currentStep)">
                                Rechercher
                            </button>
                        </div>
                    </div>

                    <!-- Compte trouvé -->
                    <div v-if="participants[currentStep].searchResult === 'found'" class="found-card">
                        ✓ {{ participants[currentStep].name }} — {{ participants[currentStep].bike }}
                    </div>
                    <!-- Compte non trouvé malgré recherche -->
                    <p v-if="participants[currentStep].searchResult === 'not_found'" class="not-found-msg">
                        Aucun compte trouvé pour cette recherche.
                    </p>

                    <!-- Expérience + goal après compte trouvé -->
                    <template v-if="participants[currentStep].searchResult === 'found'">
                        <div class="flex flex-col gap-1" v-if="!isAdherentParticipant(currentStep)">
                            <label class="block font-medium">Expérience à moto <span style="color:red">*</span></label>
                            <textarea v-model="participants[currentStep].experience" class="w-full border p-1 rounded" rows="3"></textarea>
                        </div>
                        <div v-if="isSeance" class="flex flex-col gap-1">
                            <label class="block font-medium">Thème particulier?</label>
                            <textarea v-model="participants[currentStep].goal" class="w-full border p-1 rounded" rows="2"></textarea>
                        </div>
                    </template>
                </template>

                <!-- 2b. NON → formulaire classique -->
                <template v-if="participants[currentStep].hasAccount === false">
                    <div class="flex flex-col gap-1">
                        <label class="block font-medium">Prénom et Nom <span style="color:red">*</span></label>
                        <input v-model="participants[currentStep].name" type="text" class="w-full border p-1 rounded" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="block font-medium">E-mail <span style="color:red">*</span></label>
                        <input v-model="participants[currentStep].email" type="email" class="w-full border p-1 rounded" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="block font-medium">Téléphone <span style="color:red">*</span></label>
                        <input v-model="participants[currentStep].phone" type="tel" class="w-full border p-1 rounded" pattern="[0-9]{10}" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="block font-medium">Moto/Cylindré <span style="color:red">*</span></label>
                        <input v-model="participants[currentStep].bike" type="text" class="w-full border p-1 rounded" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="block font-medium">Expérience à moto <span style="color:red">*</span></label>
                        <textarea v-model="participants[currentStep].experience" class="w-full border p-1 rounded" rows="3"></textarea>
                    </div>
                    <div v-if="isSeance" class="flex flex-col gap-1">
                        <label class="block font-medium">Thème particulier?</label>
                        <textarea v-model="participants[currentStep].goal" class="w-full border p-1 rounded" rows="2"></textarea>
                    </div>
                </template>

            </template>
        </div>

        <!-- ───── Actions ───── -->
        <div style="margin: 16px 20px 10px; display:flex; flex-direction:column; gap:8px;">

            <!-- Bouton Étape suivante / Terminer -->
            <div style="display:flex; gap:8px; justify-content:center;">
                <button
                    v-if="currentStep > 0"
                    type="button"
                    class="button-base button-secondary"
                    @click="currentStep--"
                >
                    ← Retour
                </button>

                <button
                    v-if="!isLastStep"
                    type="button"
                    class="button-base"
                    :disabled="!isCurrentStepValid"
                    @click="nextStep"
                >
                    Suivant →
                </button>

                <button
                    v-if="isLastStep"
                    type="button"
                    class="button-base"
                    :disabled="!isCurrentStepValid || isSubmitting"
                    @click="handleSubmit"
                >
                    {{ isSubmitting ? 'Inscription...' : 'Confirmer l\'inscription' }}
                </button>
            </div>

            <!-- Ajouter un participant -->
            <div style="text-align:center;" v-if="isLastStep && participants.length < maxParticipants">
                <button type="button" class="button-add" @click="addParticipant">
                    + Ajouter un participant
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import inscritAPI from "@/javascript/api/axios_inscription"
import { Events } from "@/javascript/constants/events_type"
import { Couleurs } from "@/javascript/constants/colors"
import { isEncadrant } from "@/javascript/constants/roles"

function emptyParticipant() {
    return {
        name: "",
        email: "",
        phone: "",
        bike: "",
        experience: "",
        goal: "",
        wantsEncadrant: null,
        isAdherent: null,
        hasAccount: null,
        roles: ["non_adherent"],
        searchQuery: "",
        searchResult: null, // null | 'found' | 'not_found'
    }
}

export default {
    props: {
        event:  { type: Object, required: true },
        user:   { type: Object, required: true },
        isAttente: { type: Boolean, required: false, default: false },
    },

    data() {
        return {
            Events,
            Couleurs,
            currentStep: 0,
            maxParticipants: 3,
            isSubmitting: false,
            participants: [
                {
                    ...emptyParticipant(),
                    name:  this.user?.firstName && this.user?.lastName
                                ? `${this.user.firstName} ${this.user.lastName}` : "",
                    phone: this.user?.telephone ?? "",
                    bike:  this.user?.moto ?? "",
                    isAdherent: this.user?.roles ? (!this.user.roles.includes("non_adherent") ? 1 : 0) : 0,
                    roles: this.user?.roles ?? ["non_adherent"],
                    email: this.user?.email ?? "",
                    isAttente: this.isAttente,
                }
            ]
        }
    },

    watch: {
        user: {
            immediate: true,
            deep: true,
            handler(newUser) {
                if (newUser) {
                    this.participants[0] = {
                        ...this.participants[0],
                        name:  newUser.firstName && newUser.lastName
                                    ? `${newUser.firstName} ${newUser.lastName}` : "",
                        phone: newUser.telephone ?? "",
                        bike:  newUser.moto ?? "",
                        email: newUser.email ?? "",
                        roles: newUser.roles ?? ["non_adherent"],
                        isAttente: this.isAttente,
                    }
                }
            }
        }
    },

    computed: {
        isLastStep() {
            return this.currentStep === this.participants.length - 1
        },

        isEncadrantComp() {
            return isEncadrant(this.user?.roles ?? [])
        },

        isSeance() {
            return this.event.categorie === Events.seance
        },

        isAdherent() {
            return this.user?.roles ? !this.user.roles.includes("non_adherent") : false
        },

        isCurrentStepValid() {
            const p = this.participants[this.currentStep]
            if (this.currentStep === 0) {
                const base = p.name && p.email && p.phone && p.bike
                const encadrant = !this.isEncadrantComp || p.wantsEncadrant !== null
                return base && encadrant
            }
            // Étape participant supplémentaire
            if (p.hasAccount === null) return false
            if (p.hasAccount === true) {
                if (p.searchResult !== 'found') return false
                const exp = this.isAdherentParticipant(this.currentStep) ? true : !!p.experience
                return exp
            }
            // hasAccount === false → formulaire classique
            return !!(p.name && p.email && p.phone && p.bike && p.experience)
        }
    },

    methods: {
        addParticipant() {
            this.participants.push(emptyParticipant())
            this.currentStep = this.participants.length - 1
        },

        removeParticipant(index) {
            this.participants.splice(index, 1)
            this.currentStep = Math.min(this.currentStep, this.participants.length - 1)
        },

        setHasAccount(index, value) {
            // Reset les champs liés si on change d'avis
            this.participants[index].hasAccount = value
            this.participants[index].searchQuery = ""
            this.participants[index].searchResult = null
            this.participants[index].name = ""
            this.participants[index].email = ""
            this.participants[index].phone = ""
            this.participants[index].bike = ""
            this.participants[index].experience = ""
            this.participants[index].goal = ""
        },

        isAdherentParticipant(index) {
            const roles = this.participants[index].roles ?? []
            return !roles.includes("non_adherent")
        },

        nextStep() {
            if (this.isCurrentStepValid) this.currentStep++
        },

        async searchParticipant(index) {
            const query = this.participants[index].searchQuery?.trim()
            if (!query) return
            try {
                // Remplace par ton API réelle
                const res = await inscritAPI.find_user(query)
                if (res?.data?.user) {
                    const u = res.data.user
                    this.participants[index] = {
                        ...this.participants[index],
                        name:  `${u.firstName} ${u.lastName}`,
                        email: u.email,
                        phone: u.telephone,
                        bike:  u.moto,
                        roles: u.roles ?? ["non_adherent"],
                        searchResult: 'found'
                    }
                } else {
                    this.participants[index].searchResult = 'not_found'
                }
            } catch {
                this.participants[index].searchResult = 'not_found'
            }
        },

        async handleSubmit() {
            this.isSubmitting = true
            try {
                for (const participant of this.participants) {
                    participant.status = "inscrit"
                    if (participant.isAttente) {
                        participant.status = "attente"
                    }
                    const res = await inscritAPI.create_inscrit(this.event.event_id, participant)
                    if (!res?.data?.success) throw new Error("Échec pour " + participant.name)
                }
                this.$emit('inscrit', this.participants)
            } catch (err) {
                console.error("❌ Erreur inscription:", err)
            } finally {
                this.isSubmitting = false
            }
        }
    }
}
</script>

<style scoped>
.steps-indicator {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 12px 0 4px;
}

.step-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    border: 2px solid #ccc;
    color: #aaa;
    cursor: default;
    transition: all 0.2s;
}

.step-dot.active {
    border-color: #2d5c7f;
    color: #2d5c7f;
    background: #eaf2fa;
}

.step-dot.done {
    border-color: #2d5c7f;
    background: #2d5c7f;
    color: white;
    cursor: pointer;
}

.step-title {
    font-weight: 600;
    font-size: 15px;
    margin: 8px 0 12px;
    color: #333;
}

.found-card {
    margin-top: 8px;
    padding: 10px 14px;
    background: #edf7ed;
    border: 1px solid #a3d9a5;
    border-radius: 6px;
    color: #2e7d32;
    font-weight: 500;
}

.not-found-msg {
    margin: 8px 0 10px;
    font-size: 13px;
    color: #888;
    font-style: italic;
}

.button-base {
    padding: 8px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}

.button-base:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.button-secondary {
    background: #f0f0f0;
    color: #444;
    border: 1px solid #ccc;
}

.toggle-btn {
    flex: 1;
    padding: 8px;
    border: 2px solid #ccc;
    border-radius: 6px;
    background: none;
    color: #555;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-btn--active {
    border-color: #2d5c7f;
    background: #eaf2fa;
    color: #2d5c7f;
}

.button-remove {
    background: none;
    border: none;
    color: #c0392b;
    font-size: 12px;
    cursor: pointer;
    padding: 2px 0;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.button-remove:hover {
    opacity: 1;
}

.button-add {
    background: none;
    border: 1px dashed #aaa;
    color: #666;
    padding: 6px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.button-add:hover {
    border-color: #2d5c7f;
    color: #2d5c7f;
}

.flex { display: flex; }
.flex-col { flex-direction: column; }
.gap-1 { gap: 6px; margin-bottom: 10px; }
.block { display: block; }
.font-medium { font-weight: 500; }
.w-full { width: 100%; }
.border { border: 1px solid #d1d5db; }
.p-1 { padding: 6px 8px; }
.rounded { border-radius: 4px; }
.items-center { align-items: center; }
.justify-center { justify-content: center; }
</style>