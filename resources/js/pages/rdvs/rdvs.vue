<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import { useCalendar } from '@/views/calendar/useCalendar'
import { useAppointmentsStore } from '@/views/calendar/useAppointmentsStore'
import { blankAppointment } from '@/views/calendar/useCalendar'
import axios from 'axios'; // Ensure axios is imported
import frLocale from '@fullcalendar/core/locales/fr'

// Components
import CalendarAppointmentHandler from '@/views/calendar/CalendarAppointmentHandler.vue'

// 👉 Store
const store = useAppointmentsStore()
store.selectedAppointments = store.selectedAppointments || []

// 👉 Calendar ref
const calendarRef = ref(null)

// 👉 Sidebar state
const isLeftSidebarOpen = ref(true)

// 👉 Appointment
const appointment = ref(structuredClone(blankAppointment))
const isAppointmentHandlerSidebarActive = ref(false)

// Transform appointments to FullCalendar events format
const calendarEvents = computed(() => {
  return store.appointments.map(apt => ({
    id: apt.id,
    title: `${apt.patient?.prenom} ${apt.patient?.nom_de_famille} - ${apt.description}`,
    start: new Date(apt.start),
    end: new Date(apt.end),
    extendedProps: {
      patient: apt.patient,
      description: apt.description,
      patient_id: apt.patient_id
    }
  }))
})

// 👉 useCalendar
const { calendarOptions } = useCalendar(
  appointment,
  isAppointmentHandlerSidebarActive,
  isLeftSidebarOpen
)

// Handles delete button click
const handleDeleteClick = async (eventId, event) => {
  event.stopPropagation() // Prevent event bubble up
  if (confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?')) {
    try {
      await store.removeAppointment(eventId)
      // Refresh calendar after deletion
      if (calendarRef.value) {
        calendarRef.value.getApi().refetchEvents()
      }
    } catch (error) {
      console.error('Erreur lors de la suppression:', error)
    }
  }
}

// Modify calendar options to work with our data
const modifiedCalendarOptions = computed(() => ({
  ...calendarOptions,
  locale: frLocale,
  buttonText: {
    today: "Aujourd'hui",
    month: 'Mois',
    week: 'Semaine',
    day: 'Jour',
    list: 'Liste'
  },
  events: calendarEvents.value,
  height: 'auto',
  eventClick: ({ event }) => {
    appointment.value = {
      id: event.id,
      patient_id: event.extendedProps.patient_id,
      start: event.start,
      end: event.end,
      description: event.extendedProps.description,
      patient: event.extendedProps.patient
    }
    isAppointmentHandlerSidebarActive.value = true
  },
  eventContent: (arg) => ({
    html: `
    <div class="fc-event-title d-flex align-center justify-space-between">
      <div>
        <strong>${arg.event.extendedProps.patient?.prenom} ${arg.event.extendedProps.patient?.nom_de_famille}</strong>
        <div>${arg.event.extendedProps.description}</div>
      </div>
      <v-icon
        class="delete-icon ms-2"
        icon="mdi-delete"
        size="small"
        color="error"
        @click.stop="handleDeleteClick(arg.event.id, $event)"
      ></v-icon>
    </div>
  `
  })
}))

// CRUD Operations
const createAppointment = async () => {
  try {
    // Make the API call to save the appointment
    // const response = await axios.post('http://127.0.0.1:8000/api/appointments', appointment.value);

    // Handle the response (e.g., appointment created successfully)
    console.log('Appointment created successfully:', response.data);

    // Reset the appointment form and close the sidebar
    isAppointmentHandlerSidebarActive.value = false;
    appointment.value = structuredClone(blankAppointment);

    // Refresh the calendar after creating the appointment
    if (calendarRef.value) {
      calendarRef.value.getApi().refetchEvents();
    }
  } catch (error) {
    console.error('Error while creating appointment:', error);

    // You can handle the error further, such as displaying a user-friendly error message
    if (error.response && error.response.data) {
      // For example, show validation errors returned by the API
      const errors = error.response.data.errors;
      console.log('Validation Errors:', errors);
      // You can display these errors to the user, for example, with a toast notification
    } else {
      // Generic error handling
      console.error('An unexpected error occurred:', error.message);
    }

    // Optionally, you can display a UI alert or toast here for the user
  }
};


const updateAppointmentFn = async (appointmentData) => {
  try {
    await store.updateAppointment(appointmentData)
    isAppointmentHandlerSidebarActive.value = false
    appointment.value = structuredClone(blankAppointment)
    // Refresh calendar after update
    if (calendarRef.value) {
      calendarRef.value.getApi().refetchEvents()
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error)
  }
}

const removeAppointmentFn = async (appointmentId) => {
  try {
    await store.removeAppointment(appointmentId)
    isAppointmentHandlerSidebarActive.value = false
    appointment.value = structuredClone(blankAppointment)
    // Refresh calendar after removal
    if (calendarRef.value) {
      calendarRef.value.getApi().refetchEvents()
    }
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

// Initialize
onMounted(async () => {
  await store.fetchAppointments()
  if (calendarRef.value) {
    calendarRef.value.getApi().refetchEvents()
  }
})

// Watch for store changes
watch(() => store.appointments, () => {
  if (calendarRef.value) {
    calendarRef.value.getApi().refetchEvents()
  }
}, { deep: true })

</script>

<template>
  <div class="calendar-container">
    <VCard>
      <VLayout>
        <!-- 👉 Navigation drawer -->
        <VNavigationDrawer v-model="isLeftSidebarOpen" width="292" :temporary="$vuetify.display.mdAndDown"
          location="start" class="calendar-add-appointment-drawer">
          <div class="pa-6">
            <VBtn block prepend-icon="tabler-plus" @click="isAppointmentHandlerSidebarActive = true">
              Ajouter un rendez-vous
            </VBtn>
          </div>

          <VDivider />
          <div class="d-flex align-center justify-center pa-2">
            <AppDateTimePicker :model-value="new Date().toJSON().slice(0, 10)" :config="{ inline: true }"
              class="calendar-date-picker" />
          </div>
          <VDivider />
        </VNavigationDrawer>

        <VMain class="calendar-main">
          <VCard flat class="calendar-card">
            <FullCalendar ref="calendarRef" :options="modifiedCalendarOptions" class="calendar-component" />
          </VCard>
        </VMain>
      </VLayout>
    </VCard>

    <CalendarAppointmentHandler :appointment="appointment" :is-drawer-open="isAppointmentHandlerSidebarActive"
      @update:is-drawer-open="isAppointmentHandlerSidebarActive = $event" @add-appointment="createAppointment"
      @update-appointment="updateAppointmentFn" @remove-appointment="removeAppointmentFn" />
  </div>
</template>


<style lang="scss">
@use "@core-scss/template/libs/full-calendar";

.calendar-container {
  position: relative;
  block-size: 100%;
  inline-size: 100%;
}

.calendar-main {
  overflow: hidden;
  block-size: 100%;
}

.calendar-card {
  display: flex;
  flex-direction: column;
  block-size: 100%;
}

.calendar-component {
  block-size: 100% !important;
  min-block-size: 700px;
}

.fc {
  block-size: 100% !important;

  .fc-view {
    block-size: 100% !important;
  }
}

.delete-icon {
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s ease-in-out;

  &:hover {
    opacity: 1 !important;
  }
}

.fc-event-title {
  &:hover {
    .delete-icon {
      opacity: 0.7;
    }
  }
}

.calendars-checkbox {
  .v-label {
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
    opacity: var(--v-high-emphasis-opacity);
  }
}

.calendar-add-appointment-drawer {
  position: relative;
  overflow: visible;

  &.v-navigation-drawer--temporary:not(.v-navigation-drawer--active) {
    transform: translateX(-110%) !important;
  }
}

.calendar-date-picker {
  display: none;

  +.flatpickr-input {
    +.flatpickr-calendar.inline {
      border: none;
      box-shadow: none;

      .flatpickr-months {
        border-block-end: none;
      }
    }
  }

  ~.flatpickr-calendar .flatpickr-weekdays {
    margin-block: 0 4px;
  }
}

@media screen and (max-width: 1279px) {
  .calendar-add-appointment-drawer {
    z-index: 1000;
  }
}
</style>
