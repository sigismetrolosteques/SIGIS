<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  user: { type: Object, default: null },
  roles: Array,
  submitLabel: String
});

// Inicializar el formulario con datos si es edición
const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  role: props.user?.roles?.[0]?.id ?? '',
  password: '',
  password_confirmation: '',
  security_question_1: props.user?.security_question_1 ?? '',
  security_answer_1: props.user?.security_answer_1 ? '********' : '',
  security_question_2: props.user?.security_question_2 ?? '',
  security_answer_2: props.user?.security_answer_2 ? '********' : '',
  security_question_3: props.user?.security_question_3 ?? '',
  security_answer_3: props.user?.security_answer_3 ? '********' : '',
});

function submit() {
  const isEdit = !!props.user;
  
  if (isEdit) {
    form.put(route('admin.user.update', { user: props.user.id })), {
      onSuccess: () => {
        // Manejo de éxito
      }
    };
  } else {
    form.post(route('admin.user.store')), {
      onSuccess: () => {
        form.reset('password', 'password_confirmation');
      }
    };
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
</style>
