<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { router } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';

const props = defineProps({
  equipo: {
    type: Object,
    required: true
  },
  miembros: {
    type: Array,
    required: true
  },
  rol: {
    type: [String, null],
    required: true
  }
});

const emit = defineEmits([ 'closeEquipo']);


const solicitarUnirse = (id) => {
  router.post(route('equipo.solicitarUnirse', id), {
    equipo_id: id  
  }, {
    onSuccess: () => {
      console.log('Solicitud enviada al equipo con id:', id);
    }
  });
}

const unirseAEquipo = (id) => {
  router.post(route('equipo.join', id), {
    equipo_id: id  
  }, {
    onSuccess: () => {
      console.log('Unido al equipo con id:', id);
    }
  });
}

const showLeaveModal = ref(false);
const showExpulsionModal = ref(false);
const showDeleteModal = ref(false);
const miembroAExpulsar = ref(null);
const ActualizacionGuardada = ref(false);

const salirDeEquipo = () => {
  showLeaveModal.value = true;
}

const confirmSalirDeEquipo = () => {
  router.post(route('equipo.leave', props.equipo.id), {
    equipo_id: props.equipo.id  
  }, {
    onSuccess: () => {
      console.log('Saliste del equipo con id:', props.equipo.id);
      showLeaveModal.value = false;
    }
  });
}

const expulsarMiembro = (miembroId) => {
  miembroAExpulsar.value = miembroId;
  showExpulsionModal.value = true;
}

const confirmExpulsarMiembro = () => {
  router.post(route('equipo.expulsar', miembroAExpulsar.value), {
    miembro_id: miembroAExpulsar.value
  }, {
    onSuccess: () => {
      console.log('Miembro expulsado con id:', miembroAExpulsar.value);
      showExpulsionModal.value = false;
    }
  });
}

const equipoNombre = ref(props.equipo.nombre);
const equipoDescripcion = ref(props.equipo.descripcion);
const equipoTipo = ref(props.equipo.tipo);
const equipoColor = ref(props.equipo.color);
const equipoFoto = ref(null);
const equipoFotoPreview = ref(`/archivos/${props.equipo.foto}`);

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      equipoFotoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
    equipoFoto.value = file;
  }
};

const guardarCambios = () => {
  const formData = new FormData();
  formData.append('nombre', equipoNombre.value);
  formData.append('descripcion', equipoDescripcion.value);
  formData.append('tipo', equipoTipo.value);
  formData.append('color', equipoColor.value);
  if (equipoFoto.value) {
    formData.append('foto', equipoFoto.value);
  }
  router.post(route('equipo.update', props.equipo.id), formData, {
    equipo_id: props.equipo.id ,
    onSuccess: () => {
      console.log('Equipo actualizado');
      ActualizacionGuardada.value = true;
      
    }
  });
}

const eliminarEquipo = () => {
  showDeleteModal.value = true;
}

const confirmEliminarEquipo = () => {
  router.delete(route('equipo.delete', props.equipo.id), {
    onSuccess: () => {
      console.log('Equipo eliminado');
      showDeleteModal.value = false;
    }
  });
}
</script>

<template>
  <div class="h-[35rem] md:h-[45rem] overflow-auto rounded-md relative bg-gray-800 text-white custom-scrollbar">

    <button @click="$emit('closeEquipo')" class="text-gray-400 z-10 absolute right-2 top-2 text-xl font-bold focus:outline-none">
      <font-awesome-icon :icon="['fas', 'xmark']" />
    </button>

    <div class="h-24 top-0 left-0 right-0 rounded-md absolute" :style="{ backgroundColor: equipo.color }" >
    </div>

    <div class="grid grid-cols-1 items-center p-6 gap-8 border-b-2 pb-8">
      <div class="flex flex-col items-center">
        <div class="relative" v-if="rol === 'manager'">
          <div :style="{ borderColor: equipo.color, boxShadow: `0 2px 4px ${equipo.color}` }" class="w-32 h-32 mb-3 bg-black rounded-full overflow-hidden border-2">
            <img 
              id="equipoFoto"
              class="w-full h-full object-cover"
              :src="equipoFotoPreview" 
              alt="Foto de perfil"
            >
          </div>
          <input 
            type="file" 
            @change="handleFileChange" 
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
          />

          <div class="absolute top-0 z-10 text-lg text-white bg-black rounded-full py-1 px-2">
            <font-awesome-icon :icon="['fas', 'camera']" />
          </div>
        </div>
        
        <template v-if="rol === 'manager'" >
          <input v-model="equipoNombre" class="text-2xl bg-gray-800  p-2 border rounded w-52 text-center"/>
        </template>
        <template v-else>
          <img 
            class="rounded-full z-10 w-32 h-32 mb-3 border-4 shadow-lg"
            :style="{ borderColor: equipoColor, boxShadow: `0 0 10px ${equipoColor}` }" 
            :src="equipoFotoPreview" 
            alt="Foto de perfil"
          >
          <p class="text-4xl font-semibold">{{ equipo.nombre }}</p>
        </template>
      </div>
      <div class="flex flex-col items-center">
        <template v-if="rol === 'manager'">
          <textarea v-model="equipoDescripcion" placeholder="Agregar descripción..." class="w-full mb-3 bg-gray-800 p-2 border rounded h-24"></textarea>
          <div class="flex items-center mb-3">
            <label class="mr-2">Tipo:</label>
            <select v-model="equipoTipo" class="px-5 py-1 bg-gray-800 w-28 border rounded">
              <option value="publico">Público</option>
              <option value="privado">Privado</option>
            </select>
          </div>
          <div class="flex items-center mb-3">
            <label class="mr-2">Color:</label>
            <input type="color" v-model="equipoColor" class="w-10 h-10 border bg-gray-800 rounded"/>
          </div>
          <div>
            <div class="flex gap-3">
              <button @click="guardarCambios" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 mt-2">Guardar Cambios</button>
              <button @click="eliminarEquipo" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 mt-2">Eliminar Equipo</button>
            </div>
            <p v-if="ActualizacionGuardada" class="text-green-600 text-center mt-3">Actualización guardada</p>
          </div>
          
        </template>
        <template v-else>
          <p v-if="equipo.descripcion" class="mb-3 text-xl">{{ equipo.descripcion }}</p>
          <p v-else class="mb-3 italic text-gray-500">Este equipo no tiene descripción.</p>
        </template>
        <div class="flex justify-center gap-4 mt-5">
          <button v-if="equipo.tipo === 'privado' && rol === null" @click="solicitarUnirse(equipo.id)" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Solicitar Unirse</button>
          <button v-if="equipo.tipo === 'publico' && rol === null" @click="unirseAEquipo(equipo.id)" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Unirse</button>
          <button v-if="rol === 'member'" @click="salirDeEquipo" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Salir del equipo</button>
        </div>
      </div>
    </div>

    <div class="mt-8 p-6">
      <h2 class="font-semibold mb-6 text-xl text-center">Miembros</h2>
      <div class="grid grid-cols-1 gap-6">
        <div v-for="miembro in miembros" :key="miembro.id" class="flex gap-4 items-center border p-4 rounded-md shadow-sm">
          <img class="rounded-full w-16 h-16 object-cover" :src="`/archivos/${miembro.user.foto}`" alt="Foto de perfil">
          <p class="flex-1 text-center">{{ miembro.user.nombre }} <span v-if="miembro.miembro.rol === 'manager'">({{ miembro.miembro.rol }})</span></p>
          <button v-if="rol === 'manager' && miembro.miembro.rol != 'manager'" @click="expulsarMiembro(miembro.miembro.id)" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Expulsar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for confirming leaving the team -->
  <div v-if="showLeaveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="w-full max-w-lg mx-4 bg-white rounded-lg shadow-lg p-6">
      <p class="text-xl font-bold mb-4 text-center">¿Estás seguro de que quieres salir del equipo?</p>
      <div class="flex justify-end space-x-2">
        <button @click="confirmSalirDeEquipo" class="bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300">Sí</button>
        <button @click="showLeaveModal = false" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300">No</button>
      </div>
    </div>
  </div>

  <!-- Modal for confirming expulsion of a member -->
  <div v-if="showExpulsionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="w-full max-w-lg mx-4 bg-white rounded-lg shadow-lg p-6">
      <p class="text-xl font-bold mb-4 text-center">¿Estás seguro de que quieres expulsar a este miembro?</p>
      <div class="flex justify-end space-x-2">
        <button @click="confirmExpulsarMiembro" class="bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300">Sí</button>
        <button @click="showExpulsionModal = false" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300">No</button>
      </div>
    </div>
  </div>

  <!-- Modal for confirming deletion of the team -->
  <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="w-full max-w-lg mx-4 bg-white rounded-lg shadow-lg p-6">
      <p class="text-xl font-bold mb-4 text-center">¿Estás seguro de que quieres eliminar el equipo?</p>
      <div class="flex justify-end space-x-2">
        <button @click="confirmEliminarEquipo" class="bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300">Sí</button>
        <button @click="showDeleteModal = false" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300">No</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.div-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
}

button {
  transition: all 0.3s ease-in-out;
}

button:hover {
  transform: translateY(-2px);

}

textarea {
  min-height: 100px;
  resize: vertical;
}

img:hover {
  transform: scale(1.05);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

p {
  word-break: break-word;
}

/* Custom scrollbar styles */
.custom-scrollbar::-webkit-scrollbar {
  width: 12px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #2e2e2e;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #555;
  border-radius: 10px;
  border: 3px solid #2e2e2e;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #999;
}
</style>