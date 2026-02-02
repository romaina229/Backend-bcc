// resources/js/app.js
import './bootstrap'
import { createApp } from 'vue'

// Importer le composant
import Bienvenue from './components/Bienvenue.vue'

const app = createApp({})

// Enregistrer le composant
app.component('bienvenue', Bienvenue)

app.mount('#app')