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


api.interceptors.request.use(config => {
    // const token = sessionStorage.getItem("mps_moto")
    // if (token) {
    //     config.headers.Authorization = `Bearer ${token}`
    // } else {
    //     delete config.headers.Authorization
    // }
    return config
})

api.interceptors.request.use(config => {
    config.headers["X-WP-Nonce"] = window.vueAppData.nonce
    console.log(window.vueAppData.nonce)
    return config
})

export default api