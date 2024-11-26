<script setup>
import axios from 'axios';
import { onMounted, ref, computed } from 'vue';

const payments = ref([]);
const search = ref('');
const errorMessage = ref('');
const showEditDialog = ref(false);
const currentPayment = ref({});
const headers = ref([
  { title: 'Nom', key: 'nom_de_famille' },
  { title: 'Prénom', key: 'prenom' },
  { title: 'Montant', key: 'montant' },
  { title: 'Versement', key: 'versement' },
  { title: 'Reste', key: 'reste' },
  { title: 'Motif', key: 'motif' },
  { title: 'Date', key: 'date' },
  { title: 'Actions', key: 'actions' },
]);

const fetchPayments = async () => {
  try {
    const response = await axios.get('/api/payments');
    payments.value = response.data || [];
  } catch (error) {
    errorMessage.value = "Erreur lors de la récupération des paiements.";
    console.error(errorMessage.value, error);
  }
};

const openEditDialog = (payment) => {
  if (payment) {
    currentPayment.value = { ...payment }; // Ensure currentPayment is an object
    showEditDialog.value = true;
  }
};

const closeEditDialog = () => {
  showEditDialog.value = false;
  currentPayment.value = { id: null, montant: null, versement: null, reste: null, motif: '', date: '' }; // Reset the form after closing
};

const savePayment = async () => {
  try {
    const response = await axios.put(`/api/payments/${currentPayment.value.id}`, currentPayment.value);
    const updatedPayment = response.data;
    const index = payments.value.findIndex(p => p.id === updatedPayment.id);

    if (index !== -1) {
      payments.value[index] = updatedPayment;
    }

    closeEditDialog();  // Close the dialog after saving
    await fetchPayments();
  } catch (error) {
    console.error("Error saving payment:", error.response?.data || error);
    errorMessage.value = error.response?.data?.message || "Erreur lors de la sauvegarde du paiement.";
    closeEditDialog();  // Ensure dialog closes even on error
  }
};

const markAsPaid = async (payment) => {
  try {
    const response = await axios.put(`/api/payments/${payment.id}/mark-paid`);
    const updatedPayment = response.data;
    const index = payments.value.findIndex(p => p.id === updatedPayment.id);
    if (index !== -1) {
      payments.value[index] = updatedPayment;
    }
    await fetchPayments();
  } catch (error) {
    console.error("Error marking payment as paid:", error.response?.data || error);
    errorMessage.value = error.response?.data?.message || "Erreur lors de la mise à jour du paiement.";
  }
};

// Computed property to filter payments based on the search query
const filteredPayments = computed(() => {
  if (!search.value) {
    return payments.value;
  }
  const lowerSearch = search.value.toLowerCase();
  return payments.value.filter(payment => {
    return (
      payment.consultation?.patient?.nom_de_famille?.toLowerCase().includes(lowerSearch) ||
      payment.consultation?.patient?.prenom?.toLowerCase().includes(lowerSearch) ||
      String(payment.montant).toLowerCase().includes(lowerSearch) ||
      String(payment.versement).toLowerCase().includes(lowerSearch) ||
      String(payment.motif).toLowerCase().includes(lowerSearch) ||
      String(payment.date).toLowerCase().includes(lowerSearch)
    );
  });
});

onMounted(() => {
  fetchPayments();
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

  <!-- Payments Table -->
  <VDataTable :headers="headers" :items="filteredPayments" :items-per-page="5">
    <template #item="{ item }">
      <tr class="v-data-table__tr">
        <td>{{ item.consultation?.patient?.nom_de_famille || 'N/A' }}</td>
        <td>{{ item.consultation?.patient?.prenom || 'N/A' }}</td>
        <td>{{ item.montant || 'N/A' }}</td>
        <td>{{ item.versement || 'N/A' }}</td>
        <td>{{ item.montant && item.versement ? item.montant - item.versement : 'N/A' }}</td>
        <td>{{ item.motif || 'N/A' }}</td>
        <td>{{ item.date || 'N/A' }}</td>

        <td>
          <VBtn icon @click="openEditDialog(item)">
            <span>✏️</span>
          </VBtn>
          <VBtn icon @click="markAsPaid(item)">
            <span>💰</span>
          </VBtn>
        </td>
      </tr>
    </template>
  </VDataTable>

  <!-- Edit Payment Dialog -->
  <VDialog v-model="showEditDialog" max-width="500">
    <VCard>
      <VCardTitle>Modifier le Paiement</VCardTitle>
      <VCardTitle>
        <VRow>
          <VCol cols="12">
            <AppTextField label="Montant" v-model="currentPayment.montant" type="number" />
          </VCol>
          <VCol cols="12">
            <AppTextField label="Versement" v-model="currentPayment.versement" type="number" />
          </VCol>
          <VCol cols="12">
            <AppTextField label="Reste" :value="currentPayment.montant - currentPayment.versement" readonly />
          </VCol>
          <VCol cols="12">
            <AppTextField label="Modalité" v-model="currentPayment.motif" />
          </VCol>
          <VCol cols="12">
            <AppDateTimePicker label="Date" v-model="currentPayment.date" type="date" />
          </VCol>
        </VRow>
      </VCardTitle>
      <VCardActions>
        <VSpacer />
        <VBtn color="primary" @click="savePayment">Enregistrer</VBtn>
        <VBtn color="secondary" @click="closeEditDialog">Annuler</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style scoped>
/* Add styles for the Payments.vue component here */
</style>
