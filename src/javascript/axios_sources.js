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
        console.log(subcategories)
        const results = await api.post("/subcategories/", subcategories)
        return results
    },

    async add_source(source) {
        console.log(source)
        const results = api.post("/sources/", source)
        return results
    },
}