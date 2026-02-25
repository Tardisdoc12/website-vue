import api from "./api.js"

export default {
    async get_subcategorie() {
        const results = await api.get("/subcategories/")
        return results
    },

    async get_sources() {
        const results = await api.get("/sources/")
        return results
    },

    async add_subcategorie(subcategories) {
        const results = await api.post("/subcategories/", subcategories)
        return results
    },

    async add_source(source) {
        const results = api.post("/sources/", source)
        return results
    },
    async delete_source(id) {
        const results = await api.delete(`/sources/${id}`)
        return results
    },
    async update_source(source) {
        if (!source.source_id) {
            throw new Error("L'ID de la source est requis pour la mise à jour.")
        }
        const results = await api.put(`/sources/${source.source_id}`, source)
        return results
    },
    async delete_subcategorie(id) {
        const results = await api.delete(`/subcategories/${id}`)
        return results
    },

    async get_exercices(){
        const response = await api.get('/exercices')
        return response
    }
}