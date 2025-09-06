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
  baseURL: "https://mps-moto.fr/wp-json/vue-plugin/v1",
  withCredentials: true,
})


function isJwtExpired(token) {
    try {
        const payload = JSON.parse(atob(token.split(".")[1]));
        const now = Math.floor(Date.now() / 1000);
        return payload.exp < now;
    } catch (e) {
        return true; // si le token est cassé, on le considère invalide
    }
}

api.interceptors.request.use(config => {
    const token = sessionStorage.getItem("mps_moto");

    if (token && !isJwtExpired(token)) {
        config.headers.Authorization = `Bearer ${token}`;
        delete config.headers["X-WP-Nonce"];
    } else {
        // fallback CSRF
        sessionStorage.removeItem("mps_moto"); // nettoyer l'expiré
        delete config.headers.Authorization;
        config.headers["X-WP-Nonce"] = window.vueAppData.nonce;
    }

    return config;
});

api.interceptors.response.use(
  response => {
    console.log("✅ Réponse succès :", response)
    return response
  },
  error => {
    console.error("❌ Interceptor déclenché !")
    console.error("   ↳ error:", error)
    console.error("   ↳ error.response:", error.response)
    console.error("   ↳ error.message:", error.message)
    return error.response.data.message
  }
)

export default api