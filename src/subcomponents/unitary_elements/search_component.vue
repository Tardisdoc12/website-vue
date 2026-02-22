<template>
    <div class="flex justify-center" style="margin-top: 10px;margin-bottom: 10px;">
        <div style="width:95%;" class="flex flex-col">
            <!-- Barre de recherche -->
            <input
                v-model="search"
                type="text"
                placeholder="Rechercher dans la liste..."
                class="w-full border border-gray-300 p-2 rounded mb-2"
                style="margin-bottom: 3px;"
            />

            <!-- Tableau -->
            <div class="max-h-[240px] overflow-y-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-100">
                            <th
                                v-for="(value, name_element) in ColumnToShowComp"
                                :key="name_element"
                            >
                                {{ value }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(user_, index) in filteredList" 
                            :key="index"
                            class="hover:bg-gray-50 cursor-pointer"
                            @click="selectUser(user_)"
                        
                        >
                            <template    
                                v-for="(value, key) in user_"
                                :key="key"
                            >
                                <td v-if="Object.keys(ColumnToShowComp).includes(key)" class="border border-gray-300 p-2">
                                    {{ value }}
                                </td>                            
                            </template>
                        </tr>

                        <!-- Si aucun résultat -->
                        <tr v-if="filteredList.length === 0">
                            <td colspan="2" class="p-2 text-center text-gray-400">
                                Aucun résultat
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>

export default{
    emits:['select'],
    
    props:{
        list:{
            type: Array,
            required: true,
        },
        ColumnToShow:{
            type: Object,
            required: false,
            default: null
        }
    },

    data() {
        return {
            search: ''
        }
    },

    computed:{
        ColumnToShowComp() {
            if (this.ColumnToShow) return this.ColumnToShow

            if (!this.list || !this.list.length) return {}

            return Object.keys(this.list[0]).reduce((acc, key) => {
                acc[key] = key
                return acc
            }, {})
        },

        filteredList() {
            if (!this.search) return this.list

            const term = this.search.toLowerCase()

            return this.list.filter(user => {
                return Object.values(user).some(value => {
                    return (
                        typeof value === 'string' &&
                        value.toLowerCase().includes(term)
                    )
                })
            })
        }
    },

    methods:{
        selectUser(user) {
            this.$emit("select", user)
        }
    },
}

</script>