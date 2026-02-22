const Administrateur = ["administrator"]
const Bureau = [...Administrateur, "bureau"]
const Encadrant = [...Bureau, "encadrant"]
const Adherent = [...Encadrant, "adherent"]

export const Roles = {
    Administrateur: Administrateur,
    Bureau: Bureau,
    Adherent: Adherent,
    Encadrant: Encadrant
}

function hasRoles(rolesToCheck, RolesUsed) {
    return rolesToCheck?.some(el => RolesUsed.includes(el)) ?? false
}

export function isAdministrator(roles) {
    return hasRoles(roles, Roles.Administrateur)
}

export function isBureau(roles) {
    return hasRoles(roles, Roles.Bureau)
}

export function isEncadrant(roles){
    return hasRoles(roles, Roles.Encadrant)
}

export function isAdherent(roles){
    return hasRoles(roles, Roles.Adherent)
}

export function isNonAdherent(roles) {
    const isAdherent = isAdherent(roles)
    return !isAdherent
}