<template>
    <div class="calculateur">
        <h2>Simulateur de Rendement SCPI</h2>

        <!-- Formulaire -->
        <form @submit.prevent="simuler">
            <div>
                <label>Montant investi (€)</label>
                <input v-model.number="form.montantInvesti" type="number" min="1" required />
            </div>

            <div>
                <label>Taux de rendement (%)</label>
                <input v-model.number="form.tauxRendement" type="number" step="0.01" min="0" max="100" required />
            </div>

            <div>
                <label>Durée (années)</label>
                <input v-model.number="form.dureeAnnees" type="number" min="1" max="50" required />
            </div>

            <button type="submit" :disabled="loading">
                {{ loading ? 'Calcul...' : 'Simuler' }}
            </button>
        </form>

        <!-- Résultats -->
        <div v-if="resultats" class="resultats">
            <h3>Résultats de simulation</h3>
            <div class="metriques">
                <div class="metrique">
                    <span class="label">Revenus annuels :</span>
                    <span class="valeur">{{ formatCurrency(resultats.rendementAnnuel) }}</span>
                </div>
                <div class="metrique">
                    <span class="label">Revenus mensuels :</span>
                    <span class="valeur">{{ formatCurrency(resultats.revenusMensuels) }}</span>
                </div>
                <div class="metrique">
                    <span class="label">Capital total après {{ form.dureeAnnees }} ans :</span>
                    <span class="valeur">{{ formatCurrency(resultats.rendementTotal) }}</span>
                </div>
            </div>

            <!-- Tableau projection -->
            <table class="projection">
                <thead>
                <tr>
                    <th>Année</th>
                    <th>Capital</th>
                    <th>Revenus</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="annee in resultats.projectionAnnuelle" :key="annee.annee">
                    <td>{{ annee.annee }}</td>
                    <td>{{ formatCurrency(annee.capital) }}</td>
                    <td>{{ formatCurrency(annee.revenus) }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Historique -->
        <div class="historique">
            <h3>Derniers calculs</h3>
            <ul>
                <li v-for="calc in historique" :key="calc.id">
                    {{ formatCurrency(calc.montantInvesti) }} à {{ calc.tauxRendement }}%
                    - {{ formatCurrency(calc.rendementAnnuel) }}/an
                    <small>({{ formatDate(calc.dateCalcul) }})</small>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { simulateRendement, getHistoriqueCalculs } from '../api.js'

const form = ref({
    montantInvesti: 10000,
    tauxRendement: 4.5,
    dureeAnnees: 10
})

const resultats = ref(null)
const historique = ref([])
const loading = ref(false)

const simuler = async () => {
    loading.value = true
    try {
        const response = await simulateRendement(form.value)
        resultats.value = response.data.data.resultats
        await chargerHistorique() // Refresh historique
    } catch (error) {
        console.error('Erreur simulation:', error)
    } finally {
        loading.value = false
    }
}

const chargerHistorique = async () => {
    try {
        const response = await getHistoriqueCalculs()
        historique.value = response.data.data
    } catch (error) {
        console.error('Erreur historique:', error)
    }
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR')
}

onMounted(chargerHistorique)
</script>

<style scoped>
.calculateur {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

form {
    display: grid;
    gap: 15px;
    margin-bottom: 30px;
}

form div {
    display: flex;
    flex-direction: column;
}

label {
    font-weight: bold;
    margin-bottom: 5px;
}

input {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    padding: 12px 24px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.resultats {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.metriques {
    display: grid;
    gap: 10px;
    margin-bottom: 20px;
}

.metrique {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: white;
    border-radius: 4px;
}

.label {
    font-weight: normal;
}

.valeur {
    font-weight: bold;
    color: #28a745;
}

.projection {
    width: 100%;
    border-collapse: collapse;
}

.projection th,
.projection td {
    padding: 8px 12px;
    text-align: right;
    border: 1px solid #ddd;
}

.projection th {
    background: #e9ecef;
    font-weight: bold;
}

.historique ul {
    list-style: none;
    padding: 0;
}

.historique li {
    padding: 8px;
    border-bottom: 1px solid #eee;
}

.historique small {
    color: #666;
    margin-left: 10px;
}
</style>
