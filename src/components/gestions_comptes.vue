<template>
    <div v-if="user && user.firstName" class="user-profile">
        <h2>Profil utilisateur</h2>
        <ul>
            <li><strong>Nom :</strong> {{ user.lastName }}</li>
            <li><strong>Prénom :</strong> {{ user.firstName }}</li>
            <li><strong>Email :</strong> {{ user.email }}</li>
            <li><strong>Téléphone :</strong> {{ user.telephone }}</li>
            <li><strong>Moto :</strong> {{ user.moto }}</li>
        </ul>
    </div>

    <div v-else>
        <p>Aucun utilisateur connecté.</p>
    </div>

    <div v-if="isAdherent" class="adhesion-container">
        <h2>Pour devenir Adherent</h2>
        <iframe class="adhesion-iframe" src="https://www.helloasso.com/associations/mps-moto/adhesions/devenir-adherent/widget"></iframe>
    </div>
    <div v-else>
        <p>{{ "WIP" }}</p>
    </div>
</template>

<script>
import { jwtDecode } from "jwt-decode"
import api from "../javascript/users_wp.js"

export default {
    data() {
        return {
            user: {},
        }
    },

    async mounted() {
        const token = sessionStorage.getItem("mps_moto")
        if (token) {
            const decoded = jwtDecode(token)
            const user_id = decoded.data.user.id
            const user_info = await api.get_user(user_id)
            this.user = user_info.user
        }
    },

    computed: {
        isAdherent() {
            if(this.user?.roles) {
                if (!this.user.roles.includes("non_adherent")) {
                    return true
                }
            }
            return false
        }
    }
}

</script>

<style>
.user-profile {
  background: #f9f9f9;
  border: 1px solid #ddd;
  padding: 1rem;
  border-radius: 8px;
  max-width: 400px;
}
.user-profile h2 {
  margin-bottom: 1rem;
}
.user-profile ul {
  list-style: none;
  padding: 0;
}
.user-profile li {
  margin-bottom: 0.5rem;
}

.adhesion-container {
  max-width: 900px;   /* largeur max du bloc */
  margin: 2rem auto;  /* centré horizontalement */
}

.adhesion-iframe {
  width: 100%;
  height: 800px;   /* 👈 hauteur fixée pour afficher tout le widget */
  border: none;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>