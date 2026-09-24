<template>
    <div>
        <!-- Indicateur d'étapes -->
        <div class="steps-indicator">
            <div
                v-for="(p, index) in participants"
                :key="index"
                class="step-dot"
                :class="{ active: currentStep === index, done: currentStep > index }"
                @click="currentStep = index"
            >
                <span v-if="currentStep > index">✓</span>
                <span v-else>{{ index + 1 }}</span>
            </div>
            <div
                class="step-dot"
                v-if="!isFullStep"
                :class="{done: true}"
                @click="addParticipant"
            >+</div>
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
                <InscriptionFormulaireComponent
                    :key="'step-' + currentStep"
                    v-model:currentParticipant="participants[currentStep]"
                    :eventCategorie="event.categorie"
                />
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
                            <button
                                type="button"
                                class="button-base"
                                style="white-space:nowrap;"
                                @click="searchParticipant(currentStep)" 
                                :disabled="isSearching"
                            >
                                {{ isSearching ? 'Recherche en cours...' : 'Rechercher' }}
                            </button>
                        </div>
                    </div>

                    <!-- Compte trouvé -->
                    <div v-if="participants[currentStep].searchResult === 'found'" class="found-card">
                        ✓ {{ participants[currentStep].name }} — {{ participants[currentStep].phone }}
                    </div>
                    <!-- Compte non trouvé malgré recherche -->
                    <p v-if="participants[currentStep].searchResult === 'not_found'" class="not-found-msg">
                        Aucun compte trouvé pour cette recherche.
                    </p>

                    <!-- Expérience + champs spéciaux après compte trouvé -->
                    <template v-if="participants[currentStep].searchResult === 'found'">
                        <InscriptionFormulaireComponent
                            v-model:currentParticipant="participants[currentStep]"
                            :eventCategorie="event.categorie"
                            :key="'step-' + currentStep"
                        />
                    </template>
                </template>

                <!-- 2b. NON → formulaire classique -->
                <template v-if="participants[currentStep].hasAccount === false">
                    <InscriptionFormulaireComponent
                        v-model:currentParticipant="participants[currentStep]"
                        :eventCategorie="event.categorie"
                        :key="'step-' + currentStep"
                    />
                </template>

            </template>
        </div>

        <!-- ───── Actions ───── -->
        <div style="margin: 16px 20px 10px; display:flex; flex-direction:column; gap:8px;">

            <!-- Bouton Étape suivante / Terminer -->
            <div style="display:flex; gap:8px; justify-content:center;">
                <button
                    type="button"
                    class="button-base"
                    :disabled="!isAllStepValid || isSubmitting"
                    @click="handleSubmit"
                >
                    {{ isSubmitting ? 'Inscription...' : 'Confirmer l\'inscription' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import inscritAPI from "@/javascript/api/axios_inscription"
import { isEncadrant, isNonAdherent } from "@/javascript/constants/roles"
import InscriptionFormulaireComponent from "@/subcomponents/inscription_formulaire/event_inscription_formulaire.vue"
import { computed } from 'vue'

function emptyParticipant() {
    return {
        firstName: "",
        lastName: "",
        email: "",
        phone: "",
        experience: "",
        wantsEncadrant: null,
        isAdherent: null,
        hasAccount: null,
        roles: ["non_adherent"],
        searchQuery: "",
        searchResult: null, // null | 'found' | 'not_found'
        specialField: {},
        wantsCash: false,
    }
}

export default {
    signals:[
        'inscrit',
        'created_inscriptionId',
        'no_places_available'
    ],

    props: {
        event:  { type: Object, required: true },
        user:   { type: Object, required: true },
        isAttente: { type: Boolean, required: false, default: false },
        participantProblem: { type: Object, required: false, default: null },
    },

    data() {
        return {
            isSearching: false,
            isCashAllowed: Boolean(Number(MPS_TOOLS_SETTINGS.isCashAllowed)),
            currentStep: this.participantProblem ? this.participantProblem.length - 1 : 0,
            maxParticipants: 3,
            isSubmitting: false,
            participants: this.participantProblem ? this.participantProblem.map(p => ({...p })) : [
                {
                    ...emptyParticipant(),
                    firstName: this.user?.firstName ?? "",
                    lastName: this.user?.lastName ?? "",
                    phone: this.user?.telephone ?? "",
                    isAdherent: this.user?.roles ? (!this.user.roles.includes("non_adherent") ? 1 : 0) : 0,
                    roles: this.user?.roles ?? ["non_adherent"],
                    email: this.user?.email ?? "",
                    isAttente: this.isAttente,
                    wantsCash: false,
                }
            ]
        }
    },

    watch: {
        getSpecialFields: {
            immediate: true,
            handler(champs) {
                this.participants.forEach(participant => {
                    champs.forEach(champ => {
                        if (!(champ.nom in participant.specialField)) {
                            participant.specialField[champ.nom] = ''
                        }
                    })
                })
            }
        },
        user: {
            immediate: true,
            deep: true,
            handler(newUser) {
                if (this.participantProblem) return;
                if (newUser) {
                    this.participants[0] = {
                        ...this.participants[0],
                        firstName: newUser.firstName ?? "",
                        lastName: newUser.lastName ?? "",
                        phone: newUser.telephone ?? "",
                        email: newUser.email ?? "",
                        roles: newUser.roles ?? ["non_adherent"],
                        isAttente: this.isAttente,
                        wantsCash: false,
                    }
                }
            }
        }
    },

    computed: {
        isFullStep() {
            return this.participants.length >= 3
        },
        mustResponseSpecialField() {
            const isAdherent = !isNonAdherent(this.participants[this.currentStep]?.roles ?? ["non_adherent"])
            const specialFields = this.getSpecialFields
            const listSpecialFields = specialFields.filter(field => {
                return (
                    (isAdherent && field.mandatory_response === "only_adherent" ) 
                    ||
                    (!isAdherent && field.mandatory_response === "only_non_adherent")
                    ||
                    (field.mandatory_response === "everybody")
                )
            })
            return listSpecialFields
        },

        isLastStep() {
            return this.currentStep === this.participants.length - 1
        },

        isEncadrantComp() {
            return isEncadrant(this.user?.roles ?? [])
        },

        getSpecialFields() {
            const found = this.$settings.categories?.find(cat => cat.nom.toLowerCase() === this.event.categorie.toLowerCase())
            return found?.champs_speciaux ?? []
        },

        isAdherent() {
            return this.user?.roles ? !this.user.roles.includes("non_adherent") : false
        },
        
        isAllStepValid() {
            let isAllParticipantsValid = true
            this.participants.forEach((participant, index) => {
                const p = participant

                const specialFieldsValid = this.mustResponseSpecialField.every(field => {
                    const value = p.specialField?.[field.nom]
                    const isValid = value !== undefined && value !== null && String(value).trim() !== ''
                    return isValid
                })

                if (this.currentStep === 0) {
                    const base = p.firstName && p.lastName && p.email && p.phone
                    const encadrant = !this.isEncadrantComp || p.wantsEncadrant !== null
                    isAllParticipantsValid = isAllParticipantsValid && base && encadrant && specialFieldsValid
                    return
                }

                if (p.hasAccount === null) isAllParticipantsValid = false
                if (p.hasAccount === true) {
                    if (p.searchResult !== 'found') isAllParticipantsValid = false
                    const exp = this.isAdherentParticipant(index) ? true : !!p.experience
                    isAllParticipantsValid = isAllParticipantsValid && exp && specialFieldsValid
                    return
                }

                isAllParticipantsValid = isAllParticipantsValid && (!!(p.firstName && p.lastName && p.email && p.phone && p.experience) && specialFieldsValid)
            })
            return isAllParticipantsValid
        }
    },

    methods: {
        addParticipant() {
            const newParticipant = emptyParticipant()
            this.getSpecialFields.forEach(champ => {
                if (!(champ.nom in newParticipant.specialField)) {
                    newParticipant.specialField[champ.nom] = ''
                }
            })
            this.participants.push(newParticipant)
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
            this.participants[index].firstName = ""
            this.participants[index].lastName = ""
            this.participants[index].email = ""
            this.participants[index].phone = ""
            this.participants[index].experience = ""
            this.getSpecialFields.forEach(champ => {
                this.participants[index].specialField[champ.nom] = ''
            })
            this.participants[index].wantsCash = false
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
            this.isSearching = true
            try {
                // Remplace par ton API réelle
                const res = await inscritAPI.find_user(query)
                if (res?.data?.user) {
                    const u = res.data.user
                    this.participants[index] = {
                        ...this.participants[index],
                        firstName: u.firstName,
                        lastName: u.lastName,
                        email: u.email,
                        phone: u.telephone,
                        roles: u.roles ?? ["non_adherent"],
                        searchResult: 'found',
                        wantsCash: false,
                    }
                } else {
                    this.participants[index].searchResult = 'not_found'
                }
            } catch {
                this.participants[index].searchResult = 'not_found'
            } finally {
                this.isSearching = false
            }
        },

        verify_payement_status(participant) {
            const categorie = this.event.categorie
            const found = this.$settings.categories.find(c => c.nom.toLowerCase() === categorie.toLowerCase())


            const isNonAdherent = participant.roles?.includes("non_adherent")

            if (participant.wantsCash) return 'cash'

            //toujours paiement
            if (found && Boolean(+found.adherent_payant) && Boolean(+found.non_adherent_payant)) {
                return 'pending'
            }

            // Categorie seulement payante pour un non-adhérent ce qui est le cas
            if (found && Boolean(+found.non_adherent_payant) && isNonAdherent) return 'pending'

            if (found && Boolean(+found.adherent_payant) && !isNonAdherent) return 'pending'

            return 'completed'
        },

        async handleSubmit() {
            this.isSubmitting = true
            const nonAdherentsCount = computed(() =>
                {
                    if (!this.event.users.length) return 0;
                    return this.event.users.filter(u => u.is_adherent === "0" && u.status === "inscrit").length
                }
            )
            const adherentsCount = computed(() =>
                {
                    if (!this.event.users.length) return 0;
                    return this.event.users.filter(u => u.is_adherent === "1" && u.status === "inscrit").length
                }
            )

            let placeAdherent = this.event.subscribePlace - adherentsCount.value
            let placeNonAdherent = this.event.nonsubscribePlace - nonAdherentsCount.value
            const hasInfinitePlaces = this.event.subscribePlace < 0

            for (const participant of this.participants) {
                this.event.users = this.event.users || []
                const alreadyRegistered = this.event.users.some(u => u.email === participant.email)
                if (alreadyRegistered) {
                    alert(`Vous etes deja inscit pour cet evenement. Si vous avec deja effectué le reglement sur helloasso, vous pouvez ignorer ce message.`)
                    return
                }
                participant.status = this.isAttente ? "attente" : "inscrit"

                participant.payement_status = this.verify_payement_status(participant)

                participant.name = `${participant.firstName} ${participant.lastName}`


                const isAdherent = !participant.roles?.includes("non_adherent")

                let probleme= {
                    'participant_problem': participant,
                    'all_participants': this.participants,
                    'type': isAdherent ? 'adherent' : 'non_adherent'
                }

                if (isAdherent && !hasInfinitePlaces && placeAdherent <= 0) {
                    alert(`Il n'y a plus de places disponibles pour les adhérents.`)
                    this.$emit('no_places_available', probleme)
                    return
                }

                if (!isAdherent && placeNonAdherent <= 0) {
                    alert(`Il n'y a plus de places disponibles pour les non-adhérents.`)
                    this.$emit('no_places_available', probleme)
                    return
                }

                if (isAdherent) {
                    placeAdherent--
                } else {
                    placeNonAdherent--
                }
            }

            try {
                for (const participant of this.participants) {
                    const res = await inscritAPI.create_inscrit(this.event.event_id, participant)
                    if (!res?.data?.success) throw new Error("Échec pour " + participant.name)
                    this.$emit('created_inscriptionId', res.data.inscription_id)
                }
                this.$emit('inscrit', this.participants)
            } catch (err) {
                console.error("❌ Erreur inscription:", err)
            } finally {
                this.isSubmitting = false
            }
        }
    },

    components: {
        InscriptionFormulaireComponent
    },

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
    cursor: pointer;
    transition: all 0.2s;
}

.step-dot.active {
    border-color: var(--secondary-color);
    color: var(--secondary-color);
    background: #eaf2fa;
}

.step-dot.done {
    border-color: var(--secondary-color);
    background: var(--secondary-color);
    color: var(--writing-main-color);
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
    border-color: var(--secondary-color);
    background: #eaf2fa;
    color: var(--secondary-color);
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
    border-color: var(--secondary-color);
    color: var(--secondary-color);
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