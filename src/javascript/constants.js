
const seance = "seance"
const stage = "stage"
const balade = "balade"


export default {
    seance,
    stage,
    balade,

    colorBg(categorie) {
        if (categorie === seance) {
            return "lightblue"
        }
        if (categorie === stage) {
            return "lightgreen"
        }
        if (categorie === balade) {
            return "lightcoral"
        }
    },
}