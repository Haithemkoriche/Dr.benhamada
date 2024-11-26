export default [
  { heading: 'cabine managment' },
  {
    title: 'Rendez-vous',
    to: 'rdvs-rdvs',
    icon: { icon: 'tabler-calendar' },
    // children: [
    //   {
    //     title: 'Nouveau RDV',
    //     to: '',
    //   },
    //   {
    //     title: 'Liste des RDVs',
    //     to: '',
    //   },
    // ],
  },
  {
    title: 'File d attente',
    icon: { icon: 'tabler-home-down' },
    children: [
      {
        title: "Salle d'attente",
        to: 'salles-attente',
      },
      {
        title: "Salle d'attente precédente",
        to: 'salles-historique',
      },
    ],
  },
  {
    title: 'Patients',
    icon: { icon: 'tabler-physotherapist' },
    children: [
      {
        title: 'Ajouter un patient',
        to: 'patients',
      },
      {
        title: 'Liste des patients',
        to: 'patients-liste',
      },
    ],
  },
  {
    title: 'Consultations',
    icon: { icon: 'tabler-stethoscope' },
    children: [
      {
        title: 'Nouvelle consultation',
        to: 'consultations-add',
      },
      {
        title: 'Liste des consultations',
        to: 'consultations-list',
      },
    ],
  },
  {
    title: 'Payments',
    icon: { icon: 'tabler-cash' },
    to: 'payments'
  },
  // {
  //   title: 'Roles & Permissions',
  //   icon: { icon: 'tabler-lock' },
  //   children: [
  //     { title: 'Roles', to: 'apps-roles' },
  //     { title: 'Permissions', to: 'apps-permissions' },
  //   ],
  // },
]
