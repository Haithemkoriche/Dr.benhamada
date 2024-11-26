<script setup>
import axios from 'axios';
import { onMounted, ref, computed } from 'vue';
import { useRouter } from 'vue-router';

// Basic variables
const search = ref('');
const waitingPatients = ref([]);
const errorMessage = ref('');
const isDialogVisible = ref(false);
const activeTab = ref(0);

// Patient details
const patientDetails = ref(null);
const consultationDetails = ref({
  date_consultation: new Date().toISOString().substr(0, 10),
  heure_consultation: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
  motif_consultation: '',
  diagnostic: '',
  prescriptions: [],
});

// Payment details
const paymentDetails = ref({
  montant: 2000,
  versement: 0,
  reste: computed(() => paymentDetails.value.montant - paymentDetails.value.versement),
  motif: '/',
  date: new Date().toISOString().substr(0, 10),
});

// Table headers
const headers = [
  { title: 'NOM', key: 'nom_de_famille' },
  { title: 'PRENOM', key: 'prenom' },
  { title: 'SEXE', key: 'sexe' },
  { title: 'GROUPE SANGUIN', key: 'groupe_sanguin' },
  { title: 'ADRESSE', key: 'adresse' },
  { title: 'TELEPHONE', key: 'telephone' },
  { title: 'ACTION', key: 'action' },
];

// Functions
const fetchWaitingPatients = async () => {
  try {
    const response = await axios.get('/api/patients/liste');
    waitingPatients.value = response.data;
  } catch (error) {
    errorMessage.value = 'Erreur lors de la récupération des patients';
  }
};

const openDialog = async (id) => {
  try {
    const response = await axios.get(`/api/patients/${id}`);
    patientDetails.value = response.data;
    isDialogVisible.value = true;
  } catch (error) {
    errorMessage.value = 'Erreur lors de la récupération des détails du patient';
  }
};

const closeDialog = () => {
  isDialogVisible.value = false;
  patientDetails.value = null;
  consultationDetails.value = {
    date_consultation: new Date().toISOString().substr(0, 10),
    heure_consultation: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    motif_consultation: '',
    diagnostic: '',
    prescriptions: [],
  };
  paymentDetails.value = {
    montant: 0,
    versement: 0,
    motif: '',
    date: new Date().toISOString().substr(0, 10),
  };
};
const fermerModal = () => {
  isDialogVisible.value = false;
  patientDetails.value = null;
  consultationDetails.value = { // Réinitialisez les détails de consultation
    date_consultation: '',
    heure_consultation: '',
    motif_consultation: '',
    diagnostic: '',
    prescriptions: [],
  };
  paymentDetails.value = { // Réinitialisez les détails de paiement
    consultation_id: null,
    montant: 0,
    versement: 0,
    reste: 0,
    motif: '',
    date: new Date().toISOString().substr(0, 10),
  };
};
const saveConsultation = async () => {
  if (activeTab.value === 1) {
    activeTab.value = 2;
    return;
  }
  try {
    const consultationResponse = await axios.post('/api/consultations', {
      patient_id: patientDetails.value.id,
      utilisateur_id: '1',
      date_consultation: consultationDetails.value.date_consultation,
      heure_consultation: consultationDetails.value.heure_consultation,
      motif: consultationDetails.value.motif_consultation,
      diagnostic: consultationDetails.value.diagnostic,
      traitement: consultationDetails.value.prescriptions.join(", "),
    });

    if (consultationResponse.status === 201) {
      const consultationId = consultationResponse.data.consultation.id;
      await axios.post('/api/payments', {
        consultation_id: consultationId,
        patient_id: patientDetails.value.id,
        montant: paymentDetails.value.montant,
        versement: paymentDetails.value.versement,
        reste: paymentDetails.value.reste,
        motif: paymentDetails.value.motif,
        date: paymentDetails.value.date,
      });

      alert('Consultation et paiement enregistrés avec succès');
      closeDialog();
      fetchWaitingPatients();
    } else {
      errorMessage.value = 'Erreur lors de l\'enregistrement de la consultation.';
    }
  } catch (error) {
    errorMessage.value = 'Erreur lors de l\'enregistrement de la consultation ou du paiement.';
  }
};

onMounted(() => {
  fetchWaitingPatients();
});

</script>

<template>
  <VCardText>
    <VRow>
      <VCol cols="12" offset-md="8" md="4">
        <AppTextField v-model="search" placeholder="Rechercher..." append-inner-icon="tabler-search" single-line hide-details dense outlined />
      </VCol>
    </VRow>
    <p v-if="errorMessage" class="text-danger">{{ errorMessage }}</p>
  </VCardText>

  <VDataTable :headers="headers" :items="waitingPatients" :search="search" :items-per-page="5">
    <template #item="{ item }">
      <tr class="v-data-table__tr">
        <td>{{ item.nom_de_famille }}</td>
        <td>{{ item.prenom }}</td>
        <td>{{ item.sexe }}</td>
        <td>{{ item.groupe_sanguin }}</td>
        <td>{{ item.adresse }}</td>
        <td>{{ item.telephone }}</td>
        <td>
          <VBtn icon @click="openDialog(item.id)">
            <span>👁️</span>
          </VBtn>
        </td>
      </tr>
    </template>
  </VDataTable>

 <VDialog v-model="isDialogVisible" fullscreen transition="dialog-bottom-transition" :scrim="true" persistent
    scrollable>
    <VCard>
      <!-- Toolbar -->
      <VToolbar color="primary">
        <VBtn icon variant="plain" @click="fermerModal">
          <VIcon color="white" icon="tabler-x" />
        </VBtn>
        <VToolbarTitle>Consultation Patient</VToolbarTitle>
        <VSpacer />
        <VToolbarItems>
          <VBtn variant="text" @click="saveConsultation">Enregistrer</VBtn>
        </VToolbarItems>
      </VToolbar>

      <!-- Tabs -->
      <VTabs v-model="activeTab">
        <VTab>Info</VTab>
        <VTab>Consultation</VTab>
        <!-- <VTab>Ordonnance</VTab> -->
        <!-- <VTab>Document</VTab> -->
        <VTab>Payment</VTab>
      </VTabs>

      <!-- Contenu des onglets -->
      <div v-if="patientDetails && activeTab === 0">
        <!-- Contenu de l'onglet Info -->
        <VContainer>
          <VRow>
            <VCol cols="12" md="6">
              <VCard>
                <VCardTitle>Informations du Patient</VCardTitle>
                <VCardText>
                  <p><strong>Nom :</strong> {{ patientDetails.nom_de_famille }}</p>
                  <p><strong>Prénom :</strong> {{ patientDetails.prenom }}</p>
                  <p><strong>Sexe :</strong> {{ patientDetails.sexe }}</p>
                  <p><strong>Groupe Sanguin :</strong> {{ patientDetails.groupe_sanguin }}</p>
                </VCardText>
              </VCard>
            </VCol>
            <VCol cols="12" md="6">
              <VCard>
                <VCardTitle>Détails de Contact</VCardTitle>
                <VCardText>
                  <p><strong>Adresse :</strong> {{ patientDetails.adresse }}</p>
                  <p><strong>Téléphone :</strong> {{ patientDetails.telephone }}</p>
                  <p><strong>Antécédents Médicaux :</strong> {{ patientDetails.antecedents_medicaux }}</p>
                  <p><strong>Antécédents Chirurgicaux :</strong> {{ patientDetails.antecedents_chirurgicaux }}</p>
                  <p><strong>Allergies Connues :</strong> {{ patientDetails.allergies_connues }}</p>
                </VCardText>
              </VCard>
            </VCol>
          </VRow>
        </VContainer>
      </div>

      <div v-if="activeTab === 1">
        <!-- Contenu de l'onglet Consultation -->
        <VContainer>
          <VRow>
            <VCol cols="12" md="6">
              <AppDateTimePicker label="Date de Consultation" v-model="consultationDetails.date_consultation"
                type="date" />
            </VCol>
            <VCol cols="12" md="6">
              <AppTextField label="Heure de Consultation" v-model="consultationDetails.heure_consultation"
                type="time" />
            </VCol>
            <VCol cols="12" md="6">
              <AppTextField label="Motif de Consultation" v-model="consultationDetails.motif_consultation" />
            </VCol>
            <VCol cols="12">
              <AppTextarea clearable clear-icon="tabler-circle-x" label="Diagnostic"
                v-model="consultationDetails.diagnostic" />
            </VCol>
            <!-- <VCol cols="12">
              <AppTextarea clearable clear-icon="tabler-circle-x" label="Notes" v-model="consultationDetails.notes" />
            </VCol> -->
            <VCol cols="12">
              <AppCombobox :items="items" label="Prescriptions" v-model="consultationDetails.prescriptions" multiple>
                <template #selection="{ item }">
                  <VChip size="small">
                    <template #prepend>
                      <VAvatar start color="primary" size="16">
                        {{ String(item.title).charAt(0).toUpperCase() }}
                      </VAvatar>
                    </template>
                    {{ item.title }}
                  </VChip>
                </template>
              </AppCombobox>
            </VCol>
          </VRow>
        </VContainer>
      </div>


      <div v-if="activeTab === 2">
        <VContainer>
          <VRow>
            <VCol cols="12" md="6">
              <AppTextField label="Montant" v-model="paymentDetails.montant" type="number" />
            </VCol>
            <VCol cols="12" md="6">
              <AppTextField label="Versement" v-model="paymentDetails.versement" type="number"
                @input="calculateReste" />
            </VCol>
            <VCol cols="12" md="6">
              <AppTextField label="Reste" v-model="paymentDetails.reste" type="number" readonly />
            </VCol>
            <VCol cols="12" md="6">
              <AppTextField label="Modalité de payement" v-model="paymentDetails.motif" />
            </VCol>
            <VCol cols="12" md="6">
              <AppDateTimePicker label="Date du paiement" v-model="paymentDetails.date" type="date" />
            </VCol>
          </VRow>
        </VContainer>
      </div>

    </VCard>
  </VDialog>
</template>
