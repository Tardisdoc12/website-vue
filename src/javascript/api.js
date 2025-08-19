import axios from "axios"

// Fonction utilitaire pour lire un cookie
function getCookie(name) {
  let cookieValue = null
  if (document.cookie && document.cookie !== "") {
    const cookies = document.cookie.split(";")
    for (let i = 0; i < cookies.length; i++) {
      const cookie = cookies[i].trim()
      if (cookie.substring(0, name.length + 1) === name + "=") {
        cookieValue = decodeURIComponent(cookie.substring(name.length + 1))
        break
      }
    }
  }
  return cookieValue
}

// Crée ton instance axios
const api = axios.create({
  baseURL: "https://localhost:8000", // ton backend
  withCredentials: true,            // 👈 nécessaire pour envoyer cookies/session
})

// Ajoute le token CSRF automatiquement
async function initApi() {
  try {
    await api.get("/csrf/") // 👉 appelle la route Django qui set le cookie
    const token = getCookie("csrftoken")
    if (token) {
      api.defaults.headers.common["X-CSRFToken"] = token
    }
  } catch (err) {
    console.error("Erreur récupération CSRF:", err)
  }
}

initApi()

export default api