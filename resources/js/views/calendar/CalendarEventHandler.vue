<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components/VForm'
import avatar1 from '@images/avatars/avatar-1.png'
import avatar2 from '@images/avatars/avatar-2.png'
import { useAppointmentsStore } from './useAppointmentsStore'

const props = defineProps({
  appointment: {
    type: Object,
    required: true,
    default: () => ({
      title: '',
      start: '',
      end: '',
    }),
  },
});


const emit = defineEmits([
  'update:isDrawerOpen',
  'addAppointment',
  'updateAppointment',
  'removeAppointment',
])

const store = useAppointmentsStore()
const refForm = ref()

const appointment = ref(JSON.parse(JSON.stringify(props.appointment || {})));


const resetAppointment = () => {
  appointment.value = JSON.parse(JSON.stringify(props.appointment))
  nextTick(() => {
    refForm.value?.resetValidation()
  })
}

watch(() => props.isDrawerOpen, resetAppointment)

const removeAppointment = () => {
  emit('removeAppointment', String(appointment.value.id))
  emit('update:isDrawerOpen', false)
}

const handleSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      if ('id' in appointment.value) {
        emit('updateAppointment', appointment.value)
      } else {
        emit('addAppointment', appointment.value)
      }
      emit('update:isDrawerOpen', false)
    }
  })
}

const onCancel = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    resetAppointment()
    refForm.value?.resetValidation()
  })
}

const startDateTimePickerConfig = computed(() => ({
  enableTime: true,
  dateFormat: 'Y-m-d H:i',
  maxDate: appointment.value.end || undefined,
}))

const endDateTimePickerConfig = computed(() => ({
  enableTime: true,
  dateFormat: 'Y-m-d H:i',
  minDate: appointment.value.start || undefined,
}))
</script>

<template>
  <VNavigationDrawer
    temporary
    location="end"
    :model-value="props.isDrawerOpen"
    width="370"
    :border="0"
    class="scrollable-content"
    @update:model-value="val => emit('update:isDrawerOpen', val)"
  >
    <AppDrawerHeaderSection
      :title="appointment.id ? 'Update Appointment' : 'Add Appointment'"
      @cancel="emit('update:isDrawerOpen', false)"
    >
      <template #beforeClose>
        <IconBtn
          v-show="appointment.id"
          @click="removeAppointment"
        >
          <VIcon size="18" icon="tabler-trash" />
        </IconBtn>
      </template>
    </AppDrawerHeaderSection>

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm ref="refForm" @submit.prevent="handleSubmit">
            <VRow>
              <!-- Patient -->
              <VCol cols="12">
                <AppSelect
                  v-model="appointment.patient"
                  label="Patient"
                  placeholder="Select Patient"
                  :rules="[requiredValidator]"
                  :items="store.patients"
                  :item-title="item => item.name"
                  :item-value="item => item.id"
                />
              </VCol>

              <!-- Appointment Type -->
              <VCol cols="12">
                <AppSelect
                  v-model="appointment.type"
                  label="Appointment Type"
                  placeholder="Select Type"
                  :rules="[requiredValidator]"
                  :items="store.appointmentTypes"
                />
              </VCol>

              <!-- Start Date -->
              <VCol cols="12">
                <AppDateTimePicker
                  v-model="appointment.start"
                  :rules="[requiredValidator]"
                  label="Start Time"
                  placeholder="Select Start Time"
                  :config="startDateTimePickerConfig"
                />
              </VCol>

              <!-- End Date -->
              <VCol cols="12">
                <AppDateTimePicker
                  v-model="appointment.end"
                  :rules="[requiredValidator]"
                  label="End Time"
                  placeholder="Select End Time"
                  :config="endDateTimePickerConfig"
                />
              </VCol>

              <!-- Notes -->
              <VCol cols="12">
                <AppTextarea
                  v-model="appointment.notes"
                  label="Notes"
                  placeholder="Enter any notes"
                />
              </VCol>

              <!-- Form Buttons -->
              <VCol cols="12">
                <VBtn type="submit" class="me-3">
                  Submit
                </VBtn>
                <VBtn variant="outlined" color="secondary" @click="onCancel">
                  Cancel
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
