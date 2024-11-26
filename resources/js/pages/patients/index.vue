<script setup>
import * as demoCode from '@/views/demos/forms/form-layout/demoCodeFormLayout'
</script>

<template>
  <div>

    <VRow>
      <VCol cols="12">
        <!-- 👉 Form with Tabs -->
        <AppCardCode
          title="Ajouter patient"
          no-padding
          :code="demoCode.formWithTabs"
        >
          <DemoFormLayoutFormWithTabs />
        </AppCardCode>
      </VCol>

    </VRow>
  </div>
</template>
