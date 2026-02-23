<template>
    <div>{{ "Ceci sert de page test" }}</div>
    <button
        v-if="canSee"
        @click="()=>{showMembers=true}"
    >
        {{ "Voir les membres " }}
    </button>

    <SeachModal
        v-if="showMembers"
        :list-to-pass="members"
        :ColumnList="ColumnsToshow"
        :title="'Membres :'"
        @cancel-signal="() => {showMembers = false}"
        @select="selectUser"
    />
</template>

<script>
import { isEncadrant } from '@/javascript/constants/roles';
import apiUser from "@/javascript/api/users_wp"
import { jwtDecode } from "jwt-decode"
import SeachModal from '@/subcomponents/modals/modal_search.vue'

export default{
    async mounted() {
        const token = sessionStorage.getItem("mps_moto")
        if (token) {
            const decoded = jwtDecode(token)
            const user_id = decoded.data.user.id
            const user_info = await apiUser.get_user(user_id)
            this.user = user_info.user
        }
        if (isEncadrant(this.user.roles)){
            const users = await apiUser.get_adherents()
            if (users?.data){
                this.members = users.data.users.filter(el=> Number(el.ID) !== 1)
            }
            this.canSee = true
        }

    },

    data() {
        return {
            user: {},
            members: [],
            ColumnsToshow: {
                "firstName":"Prénom",
                "lastName":"Nom"
            },
            showMembers: false,
            canSee:false,
        }
    },

    methods:{
        selectUser(user) {
            console.log("Utilisateur sélectionné :", user)
        }
    },

    components:{
        SeachModal
    }
}
</script>