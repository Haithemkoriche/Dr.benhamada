<script lang="ts" setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

// Onglet et champs du formulaire
const tab = ref('informations-personnelles')
const prenom = ref('')
const nom_de_famille = ref('')
const sexe = ref('')
const groupe_sanguin = ref('')
const adresse = ref('')
const commune = ref('')
const date_naissance = ref('')
const telephone = ref < number | undefined > ()
const contact_urgence = ref < number | undefined > ()
const antecedents_medicaux = ref('')
const antecedents_chirurgicaux = ref('')
const antecedents_familiales = ref('')
const allergies_connues = ref('')

// Listes prédéfinies pour certaines sélections
const listeSexe = ['Homme', 'Femme']
const listegroupe_sanguin = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']
const communeList = ref([])

onMounted(async () => {
  const response = await fetch('./com.json');  // Remplacez par le chemin réel
  communeList.value = await response.json();
});

// Méthode de soumission du formulaire pour envoyer toutes les données
function submitForm() {
  // Vérifiez si l'utilisateur est dans le premier onglet
  if (tab.value === 'informations-personnelles') {
    // Rediriger l'utilisateur vers l'onglet "Détails Médicaux"
    tab.value = 'details-medicaux';
    return; // Arrêtez la soumission ici
  }

  // Soumettre le formulaire uniquement si toutes les données sont présentes
  const patientData = {
    prenom: prenom.value,
    nom_de_famille: nom_de_famille.value,
    sexe: sexe.value,
    groupe_sanguin: groupe_sanguin.value,
    adresse: adresse.value,
    commune: commune.value,
    date_naissance: date_naissance.value,
    telephone: telephone.value,
    contact_urgence: contact_urgence.value,
    antecedents_medicaux: antecedents_medicaux.value,
    antecedents_chirurgicaux: antecedents_chirurgicaux.value,
    antecedents_familiales: antecedents_familiales.value,
    allergies_connues: allergies_connues.value
  };

  axios.post('/api/patients', patientData)
    .then(response => {
      console.log('Patient enregistré avec succès:', response.data);
      tab.value = 'confirmation'; // Diriger vers un onglet de confirmation
    })
    .catch(error => {
      console.error('Erreur lors de l\'enregistrement du patient:', error);
    });
}

</script>

<template>
  <VTabs v-model="tab">
    <VTab value="informations-personnelles">Infos Personnelles</VTab>
    <VTab value="details-medicaux">Infos Médicales</VTab>
  </VTabs>

  <VCard flat>
    <VCardText>
      <VWindow v-model="tab" class="disable-tab-transition">

        <!-- Onglet Informations Personnelles -->
        <VWindowItem value="informations-personnelles">
          <VForm class="mt-2">
            <VRow>
              <VCol md="6" cols="12">
                <AppTextField v-model="prenom" label="Prénom" placeholder="John" />
              </VCol>
              <VCol md="6" cols="12">
                <AppTextField v-model="nom_de_famille" label="Nom de Famille" placeholder="Doe" />
              </VCol>
              <VCol md="6" cols="12">
                <AppSelect v-model="sexe" :items="listeSexe" label="Sexe" placeholder="Sélectionner le sexe" />
              </VCol>
              <VCol cols="12" md="6">
                <AppDateTimePicker v-model="date_naissance" label="Date de Naissance" placeholder="JJ/MM/AAAA" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="adresse" label="Adresse" placeholder="123 rue, Ville" />
              </VCol>
              <VCol cols="12" md="6">
                <AppAutocomplete v-model="commune" :items="communeList" label="Commune"
                  placeholder="Sélectionner la commune" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="telephone" label="Téléphone" type="number" placeholder="+213 555 123456" />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField v-model="contact_urgence" label="Contact d'Urgence" type="number"
                  placeholder="+213 555 654321" />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>

        <!-- Onglet Détails Médicaux -->
        <VWindowItem value="details-medicaux">
          <VForm class="mt-2">
            <VRow>
              <VCol cols="12" md="6">
                <VSelect v-model="groupe_sanguin" :items="listegroupe_sanguin" label="Groupe Sanguin"
                  placeholder="Sélectionner le groupe sanguin" />
              </VCol>
              <VCol cols="12">
                <AppTextarea v-model="antecedents_medicaux" clearable clear-icon="tabler-circle-x"
                  label="Antécédents Médicaux" placeholder="Antécédents du patient" />
              </VCol>
              <VCol cols="12">
                <AppTextarea v-model="antecedents_chirurgicaux" clearable clear-icon="tabler-circle-x"
                  label="Antécédents Chirurgicaux" placeholder="Antécédents Chirurgicaux" />
              </VCol>
              <VCol cols="12">
                <AppTextarea v-model="antecedents_familiales" clearable clear-icon="tabler-circle-x"
                  label="Antécédents Familiales" placeholder="Antécédents Familiales" />
              </VCol>
              <VCol cols="12">
                <AppTextarea v-model="allergies_connues" clearable clear-icon="tabler-circle-x" label="Allergies"
                  placeholder="Allergies connues" />
              </VCol>
            </VRow>
          </VForm>
        </VWindowItem>
      </VWindow>
    </VCardText>

    <VDivider />
    <VCardText class="d-flex gap-4">
      <VBtn @click="submitForm">Soumettre</VBtn>
      <VBtn color="secondary" variant="tonal">Annuler</VBtn>
    </VCardText>
  </VCard>
</template>
