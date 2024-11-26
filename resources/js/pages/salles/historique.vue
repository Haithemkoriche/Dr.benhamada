<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

const search = ref('');
const waitingPatients = ref([]); // Liste des patients en salle d'attente
const router = useRouter();
const expandedRowId = ref(null);
const errorMessage = ref('');

// Headers
const headers = [
  { title: '', key: 'data-table-expand' },
  { title: 'NOM', key: 'nom_de_famille' },
  { title: 'PRENOM', key: 'prenom' },
  { title: 'SEXE', key: 'sexe' },
  { title: 'GROUPE SANGUIN', key: 'groupe_sanguin' },
  { title: 'ADRESSE', key: 'adresse' },
  { title: 'TELEPHONE', key: 'telephone' },
];

const fetchWaitingPatients = async () => {
  try {
    const response = await axios.get('/api/patients/histoique-salle-attente ');
    waitingPatients.value = response.data;
  } catch (error) {
    errorMessage.value = 'Erreur lors de la récupération des patients en salle d\'attente';
    console.error(errorMessage.value, error);
  }
};

// Toggle row
const toggleRow = (id) => {
  expandedRowId.value = expandedRowId.value === id ? null : id;
};

// Function to remove the patient from the waiting room
const sortirSalleDAttente = async (id) => {
  try {
    await axios.post(`/api/patients/${id}/sortir-salle-attente`);
    waitingPatients.value = waitingPatients.value.filter((p) => p.id !== id);
  } catch (error) {
    errorMessage.value = "Erreur lors de la sortie de la salle d'attente";
    console.error(errorMessage.value, error);
  }
};

// Function to view the profile
const voirProfil = (id) => {
  router.push(`/patients/${id}/profil`);
};

onMounted(fetchWaitingPatients);
</script>

<template>
  <VCardText>
    <VRow>
      <VCol cols="12" offset-md="8" md="4">
        <AppTextField v-model="search" placeholder="Search ..." append-inner-icon="tabler-search" single-line
          hide-details dense outlined />
      </VCol>
    </VRow>
    <p v-if="errorMessage" class="text-danger">{{ errorMessage }}</p> <!-- Affichage de l'erreur -->
  </VCardText>

  <VDataTable :headers="headers" :items="waitingPatients" :search="search" :items-per-page="5">
    <template #item="{ item }">
      <tr @click="toggleRow(item.id)" class="v-data-table__tr">
        <!-- Columns for patient details -->

        <td></td>
        <td>{{ item.nom_de_famille }}</td>
        <td>{{ item.prenom }}</td>
        <td>{{ item.sexe }}</td>
        <td>{{ item.groupe_sanguin }}</td>
        <td>{{ item.adresse }}</td>
        <td>{{ item.telephone }}</td>
      </tr>

      <!-- Expanded Row Details -->
      <tr v-if="expandedRowId === item.id" class="v-data-table__expand-content">
        <td :colspan="headers.length">
          <div class="d-flex justify-space-between align-center">
            <div>
              <p class="my-1">Antécédents Médicaux: {{ item.antecedents_medicaux }}</p>
              <p class="my-1">Antécédents Chirurgicaux: {{ item.antecedents_chirurgicaux }}</p>
              <p>Allergies Connues: {{ item.allergies_connues }}</p>
            </div>
            <!-- <div class="d-flex align-center gap-2">
              <VBtn color="success" @click="sortirSalleDAttente(item.id)" small>
                <span>Sortir</span>
              </VBtn>
              <VBtn color="primary" @click="voirProfil(item.id)" small>Voir Profil</VBtn>
              <VBtn color="success" small>Modifier</VBtn>
            </div> -->
          </div>
        </td>
      </tr>
    </template>
  </VDataTable>
</template>
