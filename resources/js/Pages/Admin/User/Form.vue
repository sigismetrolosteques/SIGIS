<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  user: { type: Object, default: null },
  roles: Array,
  submitLabel: String
});

const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  role: props.user?.roles?.[0]?.id ?? '',
  password: '',
  password_confirmation: ''
});

const isEdit = computed(() => !!props.user);

function submit() {
  if (isEdit.value) {
    // Versión garantizada que funciona
    form.put(route('admin.user.update', { user: props.user.id }), {
      preserveScroll: true,
      onSuccess: () => {
        form.reset('password', 'password_confirmation');
        // Mostrar notificación de éxito si lo deseas
      },
      onError: (errors) => {
        console.error('Errores de validación:', errors);
      }
    });
  } else {
    form.post(route('admin.user.store'));
  }
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div>
      <label class="lbl">Nombre</label>
      <input v-model="form.name" class="ipt" />
      <span class="err" v-if="form.errors.name">{{ form.errors.name }}</span>
    </div>

    <div>
      <label class="lbl">Email</label>
      <input v-model="form.email" type="email" class="ipt" />
      <span class="err" v-if="form.errors.email">{{ form.errors.email }}</span>
    </div>

    <div>
      <label class="lbl">Rol</label>
      <select v-model="form.role" class="ipt">
        <option :value="r.id" v-for="r in roles" :key="r.id">{{ r.name }}</option>
      </select>
      <span class="err" v-if="form.errors.role">{{ form.errors.role }}</span>
    </div>

    <!-- Mostrar campos de contraseña sólo al crear -->
    <template v-if="!isEdit">
      <div>
        <label class="lbl">Contraseña</label>
        <input v-model="form.password" type="password" class="ipt" />
        <span class="err" v-if="form.errors.password">{{ form.errors.password }}</span>
      </div>

      <div>
        <label class="lbl">Confirmar contraseña</label>
        <input v-model="form.password_confirmation" type="password" class="ipt" />
      </div>
    </template>

    <button class="btn-primary" :disabled="form.processing">{{ submitLabel }}</button>
  </form>
</template>

<style scoped>
.lbl {
  @apply block mb-1 font-medium;
}

.ipt {
  @apply w-full rounded border-gray-300;
}

.err {
  @apply text-sm text-red-600;
}

.btn-primary {
  @apply bg-red-600 text-white px-4 py-2 rounded;
}
</style>
