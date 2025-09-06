
const seance = "seance"
const stage = "stage"
const balade = "balade"


export default {
    seance,
    stage,
    balade,

    colorBg(categorie) {
        if (categorie === seance) {
            return ["rgba(109, 159, 175, 1)","rgba(109, 159, 175,0.2)"]
        }
        if (categorie === balade) {
            return ["rgba(37, 158, 77, 1)","rgba(37, 158, 77, 0.2)"]
        }
        if (categorie === stage) {
            return ["rgba(164, 46, 194, 1)","rgba(164, 46, 194, 0.2)"]
        }
    },
}