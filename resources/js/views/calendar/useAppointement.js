import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'
import timeGridPlugin from '@fullcalendar/timegrid'
import { useConfigStore } from '@core/stores/config'
import { useAppointmentsStore } from './useAppointmentsStore'

export const blankAppointment = {
  patient_id: null,
  start: null,
  end: null,
  description: '',
};

export const useCalendar = (appointment, isAppointmentHandlerSidebarActive, isLeftSidebarOpen) => {
  const configStore = useConfigStore()

  // Store for appointments
  const store = useAppointmentsStore()

  // Calendar template ref
  const refCalendar = ref()

  // Calendar colors for appointments
  const calendarsColor = {
    Routine: 'primary',
    Consultation: 'success',
    Checkup: 'error',
    Emergency: 'warning',
    Other: 'info',
  }

  // Extract appointment data from API
  const extractAppointmentDataFromApi = appointmentApi => {
    const { id, patient_id, start, end, description, extendedProps } = appointmentApi
    return {
      id,
      patient_id,
      start,
      end,
      description,
      extendedProps,
    }
  }

  if (typeof process !== 'undefined' && process.server)
    store.fetchAppointments()

  // Fetch appointments
  const fetchAppointments = (info, successCallback) => {
    if (!info) return
    store.fetchAppointments()
      .then(appointments => {
        successCallback(appointments.map(app => ({
          ...app,
          start: new Date(app.start),
          end: new Date(app.end),
        })))
      })
      .catch(e => {
        console.error('Error fetching appointments', e)
      })
  }

  const calendarApi = ref(null)

  // Update appointment in the calendar
  const updateAppointmentInCalendar = (updatedAppointmentData) => {
    calendarApi.value = refCalendar.value.getApi()
    const existingAppointment = calendarApi.value?.getEventById(String(updatedAppointmentData.id))
    if (!existingAppointment) {
      console.warn('Appointment not found in calendar')
      return
    }

    // Update properties of the appointment
    existingAppointment.setProp('title', updatedAppointmentData.description)
    existingAppointment.setDates(updatedAppointmentData.start, updatedAppointmentData.end)
  }

  // Remove appointment from the calendar
  const removeAppointmentFromCalendar = appointmentId => {
    const _appointment = calendarApi.value?.getEventById(appointmentId)
    if (_appointment) _appointment.remove()
  }

  // Refetch appointments
  const refetchAppointments = () => {
    calendarApi.value?.refetchEvents()
  }

  watch(() => store.selectedCalendars, refetchAppointments)

  // Add appointment
  const addAppointment = _appointment => {
    store.addAppointment(_appointment)
      .then(() => {
        refetchAppointments()
      })
  }

  // Update appointment
  const updateAppointment = _appointment => {
    store.updateAppointment(_appointment)
      .then(r => {
        updateAppointmentInCalendar(r)
      })
    refetchAppointments()
  }

  // Remove appointment
  const removeAppointment = appointmentId => {
    store.removeAppointment(appointmentId).then(() => {
      removeAppointmentFromCalendar(appointmentId)
    })
  }

  // Calendar options configuration
  const calendarOptions = {
    plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin, listPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
      start: 'drawerToggler,prev,next title',
      end: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth',
    },
    events: fetchAppointments,
    forceEventDuration: true,
    editable: true,
    eventResizableFromStart: true,
    dragScroll: true,
    dayMaxEvents: 2,
    navLinks: true,
    eventClassNames({ event: calendarEvent }) {
      const colorName = calendarsColor[calendarEvent._def.extendedProps.calendar] || 'info'
      return [`bg-light-${colorName} text-${colorName}`]
    },
    eventClick({ event: clickedEvent, jsEvent }) {
      jsEvent.preventDefault()
      if (clickedEvent.url) {
        window.open(clickedEvent.url, '_blank')
      }
      appointment.value = extractAppointmentDataFromApi(clickedEvent)
      isAppointmentHandlerSidebarActive.value = true
    },
    dateClick(info) {
      appointment.value = { ...appointment.value, start: info.date }
      isAppointmentHandlerSidebarActive.value = true
    },
    eventDrop({ event: droppedEvent }) {
      updateAppointment(extractAppointmentDataFromApi(droppedEvent))
    },
    eventResize({ event: resizedEvent }) {
      if (resizedEvent.start && resizedEvent.end)
        updateAppointment(extractAppointmentDataFromApi(resizedEvent))
    },
    customButtons: {
      drawerToggler: {
        text: 'calendarDrawerToggler',
        click() {
          isLeftSidebarOpen.value = true
        },
      },
    },
  }

  onMounted(() => {
    calendarApi.value = refCalendar.value.getApi()
  })

  const jumpToDate = currentDate => {
    calendarApi.value?.gotoDate(new Date(currentDate))
  }

  watch(() => configStore.isAppRTL, val => {
    calendarApi.value?.setOption('direction', val ? 'rtl' : 'ltr')
  }, { immediate: true })

  return {
    refCalendar,
    calendarOptions,
    refetchAppointments,
    fetchAppointments,
    addAppointment,
    updateAppointment,
    removeAppointment,
    jumpToDate,
  }
}
