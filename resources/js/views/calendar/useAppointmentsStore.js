import { defineStore } from 'pinia';
import { useApi } from '@/composables/useApi'; // Ensure this is imported correctly
export const blankAppointment = {
    patient_id: null,
    start: null,
    end: null,
    description: '',
  }
export const useAppointmentsStore = defineStore('appointments', {
  state: () => ({
    appointments: [],
    selectedAppointments: [],
    patients: [],
  }),
  actions: {
    // Add a new appointment
    async addAppointment(appointment) {
        try {
          const { data, error } = await useApi('/appointments', {
            method: 'POST',
            body: {
              patient_id: parseInt(appointment.patient_id), // Assurez-vous que c'est un nombre
              start: appointment.start,
              end: appointment.end,
              description: appointment.description || '',
            },
          });
      
          if (error?.value) {
            throw new Error(error.value);
          }
      
          // Ajouter le nouveau rendez-vous à l'état local
          const newAppointment = data.value;
          this.appointments = [...this.appointments, newAppointment];
          
          return newAppointment;
      
        } catch (err) {
          console.error('Failed to add appointment:', err);
          throw err;
        }
      },

    // Fetch all appointments
    async fetchAppointments() {
        try {
          const { data, error } = await useApi('/appointments', { method: 'GET' });
      
          if (error?.value) {
            console.error('Error fetching appointments:', error.value);
            return; // Stop further execution if there's an error
          }
      
          console.log('Fetched appointments:', data); // Log the data to inspect it
      
          // Correctly access the array inside the Vue Ref
          this.appointments = data?.value || [];  // Use `.value` to get the actual array
      
        } catch (err) {
          console.error('Unexpected error while fetching appointments:', err);
        }
      },

    // Fetch all patients
    async fetchPatients() {
      try {
        const { data, error } = await useApi('/patients/liste', { method: 'GET' });

        if (error?.value) {
          console.error('Error fetching patients:', error.value);
          return;
        }

        this.patients = data?.value || []; // Store fetched patients in state
      } catch (err) {
        console.error('Unexpected error while fetching patients:', err);
      }
    },

    // Update an existing appointment
    async updateAppointment(appointment) {
      try {
        const { data, error } = await useApi(`/appointments/${appointment.id}`, {
          method: 'PUT',
          body: {
            patient_id: appointment.patient_id,
            start: appointment.start,
            end: appointment.end,
            description: appointment.description || '',
          },
        });

        if (error?.value) {
          console.error('Error updating appointment:', error.value);
          throw new Error('Failed to update appointment');
        }

        const index = this.appointments.findIndex((a) => a.id === appointment.id);
        if (index !== -1) this.appointments[index] = data.value; // Update the appointment in the state
        return data.value;
      } catch (err) {
        console.error('Unexpected error while updating appointment:', err);
        throw new Error('Failed to update appointment');
      }
    },

    // Remove an appointment
    async removeAppointment(appointmentId) {
      try {
        const { error } = await useApi(`/appointments/${appointmentId}`, { method: 'DELETE' });

        if (error?.value) {
          console.error('Error removing appointment:', error.value);
          throw new Error('Failed to remove appointment');
        }

        this.appointments = this.appointments.filter((a) => a.id !== appointmentId); // Filter out the removed appointment
      } catch (err) {
        console.error('Unexpected error while removing appointment:', err);
        throw new Error('Failed to remove appointment');
      }
    },

    // Reset appointment to a blank state
    resetAppointment() {
      return {
        patient_id: null,
        start: '',
        end: '',
        description: '',
      };
    },
  },
  getters: {
    // Filter upcoming appointments
    upcomingAppointments: (state) => {
      return state.appointments.filter((appointment) => new Date(appointment.start) > new Date());
    },

    // Return all patients
    allPatients: (state) => state.patients,
  },
});
