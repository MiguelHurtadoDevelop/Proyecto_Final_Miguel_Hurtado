<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Importación del layout de autenticación
import { router, Link, usePage } from '@inertiajs/vue3'; // Importación del enrutador y otras funciones de Inertia
import { defineProps, ref, computed } from 'vue'; // Importación de funciones de Vue
import Equipo from '@/Pages/Equipo/Equipo.vue'; // Importación del componente Equipo
import { Head } from '@inertiajs/vue3'; // Importación del componente Head de Inertia

// Definición de props esperados por el componente
const props = defineProps({
  Equipos: {
    type: Array,
    required: true
  },
  MiembroEquipos: {
    type: Array
  },
});

// Variable reactiva para el filtro de búsqueda
const searchQuery = ref('');

// Variables reactivas para manejar la visualización de detalles del equipo
const showEquipo = ref(false);
const equipoShow = ref({});

// Función para alternar la visualización de detalles de un equipo
const toggleEquipo = (equipo) => {
  showEquipo.value = !showEquipo.value;
  if (equipoShow.value !== equipo) {
    equipoShow.value = equipo;
  } else {
    equipoShow.value = {};
  }
}

// Función para cerrar el div al hacer clic fuera de él
const closeDivOnClickOutside = (event) => {
  if (event.target.classList.contains('div-overlay')) {
    showEquipo.value = false;
    equipoShow.value = {};
  }
}

// Computación de equipos filtrados según la búsqueda
const filteredEquipos = computed(() => {
  if (!props.Equipos) return [];
  
  return props.Equipos.filter(equipo => {
    const nombre = equipo.nombre ? equipo.nombre.toLowerCase() : '';
    const descripcion = equipo.descripcion ? equipo.descripcion.toLowerCase() : '';
    const search = searchQuery.value.toLowerCase();
    
    return nombre.includes(search) || descripcion.includes(search);
  });
});

// Función para solicitar unirse a un equipo
const solicitarUnirse = (id) => {
  router.post(route('equipo.solicitarUnirse', id), {
    equipo_id: id  
  }, {
    onSuccess: () => {
    }
  });
}

// Función para unirse a un equipo
const unirseAEquipo = (id) => {
  router.post(route('equipo.join', id), {
    equipo_id: id  
  }, {
    onSuccess: () => {
    }
  });
}

// Función para verificar si el usuario pertenece a un equipo
const perteneceAlEquipo = (equipoId) => {
  return props.MiembroEquipos.some(MiembroEquipo => MiembroEquipo.equipo_id === equipoId);
}

// Función para verificar si se ha solicitado unirse a un equipo
const solicitadoAlEquipo = (equipoId) => {
  return props.MiembroEquipos.some(MiembroEquipo => MiembroEquipo.equipo_id === equipoId && MiembroEquipo.aceptado === 0);
}

// Función para obtener el rol del usuario en un equipo
const obtenerRol = (equipoId) => {
  const MiembroEquipo = props.MiembroEquipos.find(MiembroEquipo => MiembroEquipo.equipo_id === equipoId);
  return MiembroEquipo ? MiembroEquipo.rol : null;
}

// Función para verificar si el usuario ha sido aceptado en un equipo
const aceptadoAlEquipo = (equipoId) => {
  return props.MiembroEquipos.some(MiembroEquipo => MiembroEquipo.equipo_id === equipoId && MiembroEquipo.aceptado === 1);
}
</script>


<template>
  <Head title="Lista de Equipos" />

  <AuthenticatedLayout>
    <div class="container mx-auto p-4">
      <h1 class="text-4xl font-mono text-center font-bold mb-4 text-gray-800">¿Quieres unirte a un Equipo?</h1>
      <p class="mb-4 text-center">Aquí encontrarás una lista de equipos a los que puedes unirte. Puedes buscar por nombre o descripción.</p>
      <input 
        v-model="searchQuery"
        type="text"
        placeholder="Buscar equipos..."
        class="mb-4 p-2 border rounded w-full"
      />
      <div class="flex flex-col gap-6">
        <div 
          v-for="(equipo, index) in filteredEquipos" 
          :key="equipo.id || index" 
          class="card border border-gray-300 rounded-lg shadow-md p-4 overflow-hidden flex flex-col sm:flex-row items-center justify-center"
          :style="{ boxShadow: `0 2px 4px 0 ${equipo.color}`, borderColor: equipo.color }"
        >
          <div @click="toggleEquipo(equipo)" class="flex flex-col sm:flex-row items-center w-full cursor-pointer">
            <div :style="{ borderColor: equipo.color, boxShadow: `0 2px 4px ${equipo.color}` }" class="w-24 h-24 bg-black rounded-full overflow-hidden border-2 mb-4 sm:mb-0 sm:mr-4">
              <img class="w-24 h-24  object-cover" :src="`/archivos/${equipo.foto}`" alt="Foto de equipo">
            </div>

            <div class="card-body p-4 flex flex-col justify-between w-full sm:w-auto">
              <div class="">
                <h5 class="card-title text-2xl font-semibold mb-2 w-96 truncate">{{ equipo.nombre }}</h5>
                <p v-if="equipo.descripcion" class="card-text text-gray-700 w-96 truncate">{{ equipo.descripcion }}</p>
              </div>
            </div>
          </div>
          
          <div class="p-4 flex flex-col justify-center items-center w-full sm:w-auto" >
            <template v-if="!perteneceAlEquipo(equipo.id)">
              <button 
                v-if="equipo.tipo === 'privado'" 
                @click.stop="solicitarUnirse(equipo.id)"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center w-full sm:w-auto"
              >
                <font-awesome-icon :icon="['fas', 'lock']" /> <span class="ml-3">Solicitar Unirse</span>
              </button>

              <button 
                v-if="equipo.tipo === 'publico'" 
                @click.stop="unirseAEquipo(equipo.id)"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 flex items-center w-full sm:w-auto"
              >
                <font-awesome-icon :icon="['fas', 'unlock']" /> <span class="ml-3">Unirse</span>
              </button>
            </template>

            <template v-if="solicitadoAlEquipo(equipo.id)">
              <span class="text-gray-500">Solicitud enviada <i class="fas fa-paper-plane ml-2"></i></span>
            </template>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showEquipo" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-filter backdrop-blur-sm" @click="closeDivOnClickOutside">
      <div class="w-[22rem] md:w-[70rem] max-w-lg bg-white rounded-lg shadow-lg">
        <Equipo :equipo="equipoShow" :miembros="equipoShow.miembros" :rol="obtenerRol(equipoShow.id)" :aceptado="aceptadoAlEquipo(equipoShow.id)" @closeEquipo="toggleEquipo"/>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style>
.container {
  max-width: 1200px;
  margin: 0 auto;
}

.card {
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.card-body {
  background-color: white;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.card-title {
  color: #2d3748;
}

.card-text {
  color: #4a5568;
}
</style>
