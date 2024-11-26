<script>const blankAppointment = {
  patient_id: null,
  start: '',
  end: '',
  description: '',
  duration: 30,
};
</script>
<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import { useAppointmentsStore } from './useAppointmentsStore';
import { VForm, VCol, VRow, VBtn, VIcon, VCard, VCardText, VDivider } from 'vuetify/components';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';


const props = defineProps({
  appointment: {
    type: Object,
    required: true,
    default: () => ({ ...blankAppointment }),
  },
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  validationErrors: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['update:isDrawerOpen', 'addAppointment', 'updateAppointment', 'removeAppointmentFn']);

const store = useAppointmentsStore();
const refForm = ref();
const submissionError = ref(null);
const appointment = ref({ ...blankAppointment });
const selectedDuration = ref(30);
const searchPatient = ref(''); // Pour la recherche des patients

// Liste des durées disponibles
const durationOptions = computed(() => {
  return Array.from({ length: 7 }, (_, i) => ({
    title: `${i * 10} minutes`,
    value: i * 10
  }));
});

// Liste filtrée des patients avec recherche
const filteredPatients = computed(() => {
  const search = searchPatient.value.toLowerCase();
  return store.patients.filter(patient => 
    `${patient.nom_de_famille} ${patient.prenom}`.toLowerCase().includes(search) ||
    `${patient.prenom} ${patient.nom_de_famille}`.toLowerCase().includes(search)
  );
});

const calculateEndTime = (startTime, durationInMinutes) => {
  if (!startTime) return '';
  const endTime = new Date(new Date(startTime).getTime() + durationInMinutes * 60000);
  return endTime;
};

// Reset complet de tous les champs
const resetAllFields = () => {
  appointment.value = { ...blankAppointment };
  selectedDuration.value = 30;
  submissionError.value = null;
  searchPatient.value = '';
  if (refForm.value) {
    refForm.value.resetValidation();
  }
};

// Watch pour la date de fin
watch([() => appointment.value.start, () => selectedDuration.value], ([newStart, newDuration]) => {
  if (newStart && newDuration) {
    appointment.value.end = calculateEndTime(newStart, newDuration);
  }
});

// Watch pour le drawer
watch(
  () => props.isDrawerOpen,
  (isOpen) => {
    if (!isOpen) {
      resetAllFields();
    }
  }
);

// Watch pour les changements de rendez-vous
watch(
  () => props.appointment,
  (newAppointment) => {
    if (props.isDrawerOpen) {
      if (newAppointment.id) {
        // Édition d'un rendez-vous existant
        appointment.value = { ...newAppointment };
        if (newAppointment.start && newAppointment.end) {
          const durationInMinutes = Math.round(
            (new Date(newAppointment.end) - new Date(newAppointment.start)) / 60000
          );
          selectedDuration.value = durationInMinutes;
        }
      } else {
        // Nouveau rendez-vous
        resetAllFields();
        appointment.value.start = newAppointment.start || '';
      }
    }
  },
  { immediate: true }
);
const removeAppointmentFn = async (appointmentId) => {
  try {
    await axios.delete(`http://127.0.0.1:8000/api/appointments/${appointmentId}`);
    await store.fetchAppointments();
    emit('update:isDrawerOpen', false);
    resetAppointment();
  } catch (error) {
    console.error('Error deleting appointment:', error);
    submissionError.value = 'Failed to delete appointment';
  }
};
const handleSubmit = async () => {
  const valid = await refForm.value?.validate();
  if (!valid) return;

  try {
    const formatMySQLDateTime = (date) => {
      const d = new Date(date);
      return d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0') + ' ' +
        String(d.getHours()).padStart(2, '0') + ':' +
        String(d.getMinutes()).padStart(2, '0') + ':' +
        String(d.getSeconds()).padStart(2, '0');
    };

    const appointmentData = {
      patient_id: appointment.value.patient_id,
      start: formatMySQLDateTime(appointment.value.start),
      end: formatMySQLDateTime(appointment.value.end),
      description: appointment.value.description || '',
    };

    if (appointment.value.id) {
      await axios.put(
        `http://127.0.0.1:8000/api/appointments/${appointment.value.id}`,
        appointmentData
      );
    } else {
      await axios.post('http://127.0.0.1:8000/api/appointments', appointmentData);
    }

    await store.fetchAppointments();
    emit('update:isDrawerOpen', false);
    resetAllFields();

  } catch (error) {
    console.error('Error saving appointment:', error);
    submissionError.value = error.response?.data.message || 'Failed to save appointment';
  }
};

const onCancel = () => {
  emit('update:isDrawerOpen', false);
  resetAllFields();
};

const startDateTimePickerConfig = computed(() => ({
  enableTime: true,
  dateFormat: 'Y-m-d H:i',
}));

onMounted(async () => {
  try {
    await store.fetchPatients();
    await store.fetchAppointments();
  } catch (err) {
    console.error('Error fetching data:', err);
  }
});
</script>

<template>
  <VNavigationDrawer temporary location="end" :model-value="props.isDrawerOpen" width="370"
    @update:model-value="val => emit('update:isDrawerOpen', val)">
    <AppDrawerHeaderSection :title="appointment.id ? 'Modifier le rendez-vous' : 'Ajouter un rendez-vous'" @cancel="onCancel">
      <template #beforeClose>
        <IconBtn v-show="appointment.id" @click="removeAppointmentFn(appointment.id)">
          <VIcon size="18" icon="tabler-trash" />
        </IconBtn>
      </template>
    </AppDrawerHeaderSection>

    <VDivider />
    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <div v-if="submissionError" class="text-error mb-4">
            {{ submissionError }}
          </div>

          <VForm ref="refForm" @submit.prevent="handleSubmit">
            <VRow>
              <VCol cols="12">
                <!-- Champ de recherche patient amélioré -->
                <AppSelect
                  v-model="appointment.patient_id"
                  label="Patient"
                  :items="filteredPatients"
                  :item-title="item => `${item.nom_de_famille} ${item.prenom}`"
                  :item-value="item => item.id"
                  :error-messages="validationErrors.patient_id"
                  :rules="[v => !!v || 'Patient is required']"
                  required
                  clearable
                  filterable
                  :filter="searchPatient"
                  @update:filter="searchPatient = $event"
                />
              </VCol>

              <VCol cols="12">
                <AppDateTimePicker 
                  v-model="appointment.start" 
                  :config="startDateTimePickerConfig"
                  label="Date & heure de début" 
                  :error-messages="validationErrors.start"
                  :rules="[v => !!v || 'La date de début est requise']" 
                  required 
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="selectedDuration"
                  label="Durée du rendez-vous"
                  :items="durationOptions"
                  item-title="title"
                  item-value="value"
                  :rules="[v => !!v || 'La durée est requise']"
                  required
                />
              </VCol>

              <VCol cols="12">
                <AppTextarea 
                  v-model="appointment.description" 
                  label="Description"
                  placeholder="Ajoutez une description..."
                />
              </VCol>

              <VCol cols="12" class="d-flex gap-3">
                <VBtn type="submit" color="primary">Enregistrer</VBtn>
                <VBtn variant="outlined" color="secondary" @click="onCancel">Annuler</VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
