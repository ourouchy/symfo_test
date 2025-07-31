import axios from "axios";


const api = axios.create({
  baseURL: "/api"
});

export const getScpis = () => api.get("/scpi");
export const createScpi = (data) => api.post("/scpi", data);


/**
 * Simule un investissement SCPI
 * @param {Object} data - {montantInvesti, tauxRendement, dureeAnnees}
 * @returns {Promise} Réponse avec résultats de simulation
 */
export const simulateRendement = (data) => {
    return api.post("/calculateur/simulate", data);
};

/**
 * Récupère l'historique des calculs
 * @param {Object} params - {limit?, offset?}
 * @returns {Promise} Liste des calculs précédents
 */
export const getHistoriqueCalculs = (params = {}) => {
    return api.get("/calculateur/historique", { params });
};

/**
 * Récupère un calcul spécifique
 * @param {number} id - ID du calcul
 * @returns {Promise} Données du calcul
 */
export const getCalcul = (id) => {
    return api.get(`/calculateur/${id}`);
};
