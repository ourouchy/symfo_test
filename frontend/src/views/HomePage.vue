<template>
  <div style="max-width:500px;margin:auto;padding:20px">
  <h1>Ajouter une Scpi</h1>

  <form class="form" @submit.prevent="submitForm">
    <input v-model="form.nom" placeholder="Nom" required />
    <input v-model="form.rendement" type="number" step="0.01" placeholder="Rendement" required />
    <button type="submit">Ajouter</button>
  </form>


  <h2>Liste SCPI</h2>

  <ul>
    <li v-for="scpi in scpis" :key="scpi.id">
      {{ scpi.nom }} - {{scpi.rendement * 100}} %
    </li>
  </ul>
  </div>
</template>


<script setup>

import { ref, onMounted } from "vue"
import {getScpis, createScpi } from "../api.js"

const form = ref({ nom: "", rendement: 0})
const scpis = ref([])

const fetchScpis = async () => {
    const res = await getScpis()
    scpis.value = res.data.data
}
const submitForm = async () => {
    await createScpi(form.value)
    form.value = { nom: "", rendement: 0 }
    fetchScpis()
}

onMounted(fetchScpis)


</script>
