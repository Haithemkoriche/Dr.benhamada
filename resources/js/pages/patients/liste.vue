<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

const search = ref('');
const patients = ref([]);
const router = useRouter();
const expandedRowId = ref(null); // Track the expanded row
const errorMessage = ref(''); // Stocker le message d'erreur
const isDialogOpen = ref(false); // État du dialogue
const selectedPatient = ref(null); // Patient sélectionné
// Fonction pour ouvrir le dialogue

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

const fetchPatients = async () => {
  try {
    const response = await axios.get('/api/patients/liste');
    patients.value = response.data;
  } catch (error) {
    errorMessage.value = 'Erreur lors de la récupération des patients';
    console.error(errorMessage.value, error);
  }
};
const openEditDialog = (patient) => {
  selectedPatient.value = { ...patient }; // Copier les données
  isDialogOpen.value = true;
  console.log('Patients:', patients.value);
  console.log('Selected Patient:', selectedPatient.value);

};
// Fonction pour enregistrer les modifications
const savePatientChanges = async () => {
  try {
    const response = await axios.put(`/api/patients/${selectedPatient.value.id}`, selectedPatient.value);
    console.log(response.data.message);
    isDialogOpen.value = false; // Fermer le dialogue
    await fetchPatients(); // Recharger les patients
  } catch (error) {
    errorMessage.value = "Erreur lors de la modification du patient";
    console.error(errorMessage.value, error);
  }
};


// Toggle the expanded row
const toggleRow = (id) => {
  expandedRowId.value = expandedRowId.value === id ? null : id;
};

// Function to put the patient in the waiting room
const mettreEnSalleDAttente = async (id) => {
  try {
    const response = await axios.post(`/api/patients/${id}/salle-attente`);
    console.log(response.data.message);
    const patient = patients.value.find((p) => p.id === id);
    if (patient) patient.en_salle_attente = !patient.en_salle_attente;
  } catch (error) {
    errorMessage.value = "Erreur lors de la mise en salle d'attente";
    console.error(errorMessage.value, error);
  }
};

// Function to view the profile
const voirProfil = (id) => {
  router.push(`/patients/${id}/profil`);
};
// const modifierPatient = async (id, updatedData) => {
//   try {
//     const response = await axios.put(`/api/patients/${id}`, updatedData);
//     console.log(response.data.message);
//     await fetchPatients(); // Recharge la liste
//   } catch (error) {
//     errorMessage.value = "Erreur lors de la modification du patient";
//     console.error(errorMessage.value, error);
//   }
// };

const supprimerPatient = async (id) => {
  if (confirm("Êtes-vous sûr de vouloir supprimer ce patient ?")) {
    try {
      const response = await axios.delete(`/api/patients/${id}`);
      console.log(response.data.message);
      await fetchPatients(); // Recharge la liste
    } catch (error) {
      errorMessage.value = "Erreur lors de la suppression du patient";
      console.error(errorMessage.value, error);
    }
  }
};
onMounted(fetchPatients);

</script>

<template>
  <VDataTable :headers="headers" :items="patients" :search="search" :items-per-page="5">
    <template #item="{ item }">
      <tr @click="toggleRow(item.id)" class="v-data-table__tr">
        <td></td>
        <td>{{ item.nom_de_famille }}</td>
        <td>{{ item.prenom }}</td>
        <td>{{ item.sexe }}</td>
        <td>{{ item.groupe_sanguin }}</td>
        <td>{{ item.adresse }}</td>
        <td>{{ item.telephone }}</td>
      </tr>
      <tr v-if="expandedRowId === item.id" class="v-data-table__expand-content">
        <td :colspan="headers.length">
          <div class="d-flex justify-space-between align-center">
            <div>
              <p class="my-1">Antécédents Médicaux: {{ item.antecedents_medicaux }}</p>
              <p class="my-1">Antécédents Chirurgicaux: {{ item.antecedents_chirurgicaux }}</p>
              <p>Allergies Connues: {{ item.allergies_connues }}</p>
            </div>
            <div class="d-flex align-center gap-2">
              <VBtn :color="item.en_salle_attente ? 'success' : 'warning'" @click="mettreEnSalleDAttente(item.id)"
                small>
                <span>{{ item.en_salle_attente ? 'Sortir' : 'En Attente' }}</span>
              </VBtn>
              <VBtn color="success" small @click="openEditDialog(item)">Modifier</VBtn>
              <VBtn color="error" small @click="supprimerPatient(item.id)">Supprimer</VBtn>
            </div>
          </div>
        </td>
      </tr>
    </template>
  </VDataTable>

  <!-- Dialogue de modification -->
  <VDialog v-model="isDialogOpen" max-width="500">
    <VCard>
      <VCardTitle>Modifier Patient</VCardTitle>
      <VCardText>
        <VTextField class="mb-4" v-model="selectedPatient.nom_de_famille" label="Nom de Famille" />
        <VTextField class="mb-4" v-model="selectedPatient.prenom" label="Prénom" />
        <VSelect class="mb-4" v-model="selectedPatient.sexe" :items="['Homme', 'Femme']" label="Sexe" />
        <VTextField class="mb-4" v-model="selectedPatient.groupe_sanguin" label="Groupe Sanguin" />
        <VTextField class="mb-4" v-model="selectedPatient.adresse" label="Adresse" />
        <VTextField class="mb-4" v-model="selectedPatient.telephone" label="Téléphone" />
      </VCardText>
      <VCardActions>
        <VBtn color="primary" @click="savePatientChanges">Enregistrer</VBtn>
        <VBtn color="secondary" @click="isDialogOpen = false">Annuler</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
