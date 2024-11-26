<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

// Champs de recherche et données
const search = ref('');
const consultations = ref([]); // Liste des consultations
const errorMessage = ref('');
const showDialog = ref(false); // Contrôle l'affichage du dialogue
const currentConsultation = ref({}); // Consultation en cours d'édition
const isPaymentDialogVisible = ref(false);
const paymentDialogDetails = ref({
  montant: 2000,
  versement: 2000,
  reste: 0,
  motif: '/',
  date: new Date().toISOString().substr(0, 10),
});
const openPaymentDialog = (consultation) => {
  paymentDialogDetails.value = {
    ...paymentDialogDetails.value,
    consultation_id: consultation.id, // Use consultation object correctly
    patient_id: consultation.patient_id, // Use patient ID from consultation
    montant: 2000,
    versement: 2000,
    reste: 0,
    motif: '/',
    date: new Date().toISOString().substr(0, 10),
  };
  isPaymentDialogVisible.value = true;
};


const closePaymentDialog = () => {
  isPaymentDialogVisible.value = false;
};

// En-têtes du tableau pour l'historique des consultations
const headers = [
  { title: 'Nom du patient', key: 'patient.nom_de_famille' },
  { title: 'Prénom du Patient', key: 'patient.prenom' },
  { title: 'Date', key: 'date_consultation' },
  { title: 'Heure', key: 'heure_consultation' },
  { title: 'Motif', key: 'motif' },
  { title: 'Diagnostic', key: 'diagnostic' },
  { title: 'Action', key: 'action' },
];

// Récupération de l'historique des consultations depuis l'API
const fetchConsultations = async () => {
  try {
    const response = await axios.get('/api/consultations');
    consultations.value = response.data;
  } catch (error) {
    errorMessage.value = "Erreur lors de la récupération de l'historique des consultations.";
    console.error(errorMessage.value, error);
  }
};

// Afficher le dialogue de détails de la consultation
const voirDetails = (consultation) => {
  currentConsultation.value = { ...consultation }; // Pré-remplit les champs avec les données de la consultation
  showDialog.value = true; // Ouvre le dialogue
};

// Sauvegarder les modifications
const saveDetails = async () => {
  try {
    const [hours, minutes] = currentConsultation.value.heure_consultation.split(':');
    currentConsultation.value.heure_consultation = `${hours}:${minutes}`;

    // Validate the time format
    if (!/^\d{2}:\d{2}$/.test(currentConsultation.value.heure_consultation)) {
      throw new Error("L'heure doit être au format H:i (ex: 14:30).");
    }

    console.log("Saving consultation:", currentConsultation.value); // Log the details being saved

    const response = await axios.put(`/api/consultations/${currentConsultation.value.id}`, currentConsultation.value);
    const updatedConsultation = response.data;

    // Find the index of the consultation being updated
    const index = consultations.value.findIndex(c => c.id === updatedConsultation.id);
    if (index !== -1) {
      // Update the consultation directly in the array
      consultations.value[index] = updatedConsultation; // This should trigger reactivity
    }
    await fetchConsultations();
    showDialog.value = false; // Close the dialog
  } catch (error) {
    console.error("Error saving details:", error.response?.data || error); // Log any errors
    errorMessage.value = error.response?.data?.message || "Erreur lors de la sauvegarde des modifications.";
  }
};

// // Open payment dialog
// const voirPaiement = (consultation) => {
//   currentPayment.value = { ...consultation }; // Pre-fill payment fields
//   showPaymentDialog.value = true;
// };

// // Save payment details
// const savePayment = async () => {
//   try {
//     // API call to save payment details
//     const response = await axios.put(`/api/consultations/${currentPayment.value.id}/payment`, currentPayment.value);

//     // Update local data
//     const updatedConsultation = response.data;
//     const index = consultations.value.findIndex(c => c.id === updatedConsultation.id);
//     if (index !== -1) consultations.value[index] = updatedConsultation;

//     showPaymentDialog.value = false; // Close the dialog
//   } catch (error) {
//     console.error("Error saving payment:", error.response?.data || error);
//     errorMessage.value = error.response?.data?.message || "Erreur lors de la sauvegarde du paiement.";
//   }
// };
const savePayment = async () => {
  try {
    const response = await axios.post('/api/payments', {
      montant: paymentDialogDetails.value.montant,
      versement: paymentDialogDetails.value.versement,
      reste: paymentDialogDetails.value.reste,
      motif: paymentDialogDetails.value.motif,
      date: paymentDialogDetails.value.date,
      consultation_id: paymentDialogDetails.value.consultation_id,
      patient_id: paymentDialogDetails.value.patient_id, // Add this
    });
    alert('Paiement enregistré avec succès');
    closePaymentDialog();
  } catch (error) {
    console.error(error.response.data); // Log validation errors
    errorMessage.value = error.response?.data?.message || "Erreur lors de l'enregistrement du paiement.";
  }
};


// Fermer le dialogue sans sauvegarder
const closeDialog = () => {
  showDialog.value = false;
};

// Appel API au chargement du composant
onMounted(() => {
  fetchConsultations();
});
</script>

<template>
  <VCardText>
    <VRow>
      <VCol cols="12" offset-md="8" md="4">
        <AppTextField v-model="search" placeholder="Rechercher..." append-inner-icon="tabler-search" single-line
          hide-details dense outlined />
      </VCol>
    </VRow>
    <p v-if="errorMessage" class="text-danger">{{ errorMessage }}</p>
  </VCardText>

  <!-- Tableau de l'historique des consultations -->
  <VDataTable :headers="headers" :items="consultations" :search="search" :items-per-page="5">
    <template #item="{ item }">
      <tr class="v-data-table__tr">
        <td>{{ item.patient.nom_de_famille }}</td>
        <td>{{ item.patient.prenom }}</td>
        <td>{{ item.date_consultation }}</td>
        <td>{{ item.heure_consultation }}</td>
        <td>{{ item.motif }}</td>
        <td>{{ item.diagnostic }}</td>
        <td>
          <!-- Bouton "Voir" pour afficher les détails de la consultation -->
          <VBtn icon @click="voirDetails(item)">
            <span>👁️</span>
          </VBtn>
          <VBtn icon @click="openPaymentDialog(item)">
            <VIcon>tabler-brand-cashapp</VIcon>
          </VBtn>

        </td>
      </tr>
    </template>
  </VDataTable>
  <VDialog v-model="isPaymentDialogVisible" persistent max-width="500">
    <VCard>
      <VCardTitle>Paiement</VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12">
            <AppTextField label="Montant" v-model="paymentDialogDetails.montant" type="number" />
          </VCol>
          <VCol cols="12">
            <AppTextField label="Versement" v-model="paymentDialogDetails.versement" type="number" />
          </VCol>
          <VCol cols="12">
            <AppTextField label="Reste" :value="paymentDialogDetails.montant - paymentDialogDetails.versement"
              readonly />
          </VCol>
          <VCol cols="12">
            <AppTextField label="Modalité" v-model="paymentDialogDetails.motif" />
          </VCol>
          <VCol cols="12">
            <AppDateTimePicker label="Date" v-model="paymentDialogDetails.date" type="date" />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="primary" @click="savePayment">Enregistrer</VBtn>
        <VBtn color="secondary" @click="closePaymentDialog">Annuler</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Dialogue pour afficher et modifier les détails de la consultation -->
  <VDialog v-model="showDialog" max-width="600">
    <VCard>
      <VCardTitle>Détails de la Consultation</VCardTitle>
      <VCardText>
        <VForm ref="form">
          <AppDateTimePicker v-model="currentConsultation.date_consultation" label="Date" type="date" />
          <VTextField v-model="currentConsultation.heure_consultation" label="Heure" type="time" placeholder="HH:mm" />
          <AppTextField v-model="currentConsultation.motif" label="Motif" />
          <AppTextarea clearable clear-icon="tabler-circle-x" v-model="currentConsultation.diagnostic"
            label="Diagnostic" />
        </VForm>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn color="primary" @click="saveDetails">Enregistrer</VBtn>
        <VBtn color="secondary" @click="closeDialog">Annuler</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
