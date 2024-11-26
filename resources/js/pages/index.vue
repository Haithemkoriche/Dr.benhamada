<template>
  <div>
    <VContainer>
      <VRow justify="center" align="stretch" class="gy-4">
        <!-- Nombre de patients -->
        <VCol cols="12" md="3">
          <VCard class="text-center" color="light-blue lighten-4" outlined>
            <VCardTitle class="text-primary">
              <VIcon size="30" class="mr-2">tabler-users</VIcon>
              Nombre de Patients
            </VCardTitle>
            <VCardText>
              <h1 class="text-h2 font-weight-bold">{{ stats.patients }}</h1>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Nombre de rendez-vous -->
        <VCol cols="12" md="3">
          <VCard class="text-center" color="green lighten-4" outlined>
            <VCardTitle class="text-success">
              <VIcon size="30" class="mr-2">tabler-calendar</VIcon>
              Nombre de Rendez-vous
            </VCardTitle>
            <VCardText>
              <h1 class="text-h2 font-weight-bold">{{ stats.appointments }}</h1>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Nombre de consultations -->
        <VCol cols="12" md="3">
          <VCard class="text-center" color="pink lighten-4" outlined>
            <VCardTitle class="text-error">
              <VIcon size="30" class="mr-2">tabler-stethoscope</VIcon>
              Nombre de Consultations
            </VCardTitle>
            <VCardText>
              <h1 class="text-h2 font-weight-bold">{{ stats.consultations }}</h1>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Personnes en salle d'attente -->
        <VCol cols="12" md="3">
          <VCard class="text-center" color="amber lighten-4" outlined>
            <VCardTitle class="text-warning">
              <VIcon size="30" class="mr-2">tabler-armchair</VIcon>
              Salle d'attente
            </VCardTitle>
            <VCardText>
              <h1 class="text-h2 font-weight-bold">{{ stats.waitingRoom }}</h1>
            </VCardText>
          </VCard>
        </VCol>

        <!-- Navigation rapide -->
        <VCol cols="12" md="3">
          <VCard class="text-center hoverable" color="blue lighten-5" outlined @click="navigate('rdvs-rdvs')">
            <VCardTitle class="text-blue">
              <VIcon size="30" class="mr-2">tabler-calendar-check</VIcon>
              Rendez-vous
            </VCardTitle>
            <VCardText>Gérer les rendez-vous</VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="3">
          <VCard class="text-center hoverable" color="teal lighten-5" outlined @click="navigate('salles-attente')">
            <VCardTitle class="text-teal">
              <VIcon size="30" class="mr-2">tabler-clock</VIcon>
              Salle d'attente
            </VCardTitle>
            <VCardText>Accéder à la file d'attente</VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="3">
          <VCard class="text-center hoverable" color="purple lighten-5" outlined @click="navigate('patients-liste')">
            <VCardTitle class="text-purple">
              <VIcon size="30" class="mr-2">tabler-users</VIcon>
              Patients
            </VCardTitle>
            <VCardText>Voir tous les patients</VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="3">
          <VCard class="text-center hoverable" color="red lighten-5" outlined @click="navigate('consultations-list')">
            <VCardTitle class="text-red">
              <VIcon size="30" class="mr-2">tabler-clipboard-text</VIcon>
              Consultations
            </VCardTitle>
            <VCardText>Voir les consultations</VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

// Initialisation des statistiques réactives
const stats = ref({
  patients: 0,
  appointments: 0,
  consultations: 0,
  waitingRoom: 0,
});

// Fonction pour naviguer entre les pages
const router = useRouter();
const navigate = (route) => {
  router.push({ name: route });
};

// Fonction pour récupérer les statistiques via API
const fetchStats = async () => {
  try {
    const [patientsRes, appointmentsRes, consultationsRes, waitingRoomRes] = await Promise.all([
      fetch('/api/patients/liste'),
      fetch('/api/appointments'),
      fetch('/api/consultations'),
      fetch('/api/patients/salle-attente'),
    ]);

    stats.value.patients = (await patientsRes.json()).length;
    stats.value.appointments = (await appointmentsRes.json()).length;
    stats.value.consultations = (await consultationsRes.json()).length;
    stats.value.waitingRoom = (await waitingRoomRes.json()).length;
  } catch (error) {
    console.error('Erreur lors de la récupération des statistiques :', error);
  }
};

// Charger les statistiques au montage
onMounted(fetchStats);
</script>
