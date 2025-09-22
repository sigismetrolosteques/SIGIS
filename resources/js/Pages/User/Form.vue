<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  user: { type: Object, default: null },
  roles: Array,
  gerencias: Array, // 👈 se recibe del backend
  submitLabel: String
});

// Inicializar el formulario con datos si es edición
const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  role: props.user?.roles?.[0]?.id ?? '',
  gerencia_id: props.user?.gerencia_id ?? '', // 👈 nuevo campo
  password: '',
  password_confirmation: '',
  security_question_1: props.user?.security_question_1 ?? '',
  security_answer_1: props.user?.security_answer_1 ? '********' : '',
  security_question_2: props.user?.security_question_2 ?? '',
  security_answer_2: props.user?.security_answer_2 ? '********' : '',
  security_question_3: props.user?.security_question_3 ?? '',
  security_answer_3: props.user?.security_answer_3 ? '********' : '',
});

const isEdit = computed(() => !!props.user);

function submit() {
  if (isEdit.value) {
    form.put(route('admin.user.update', { user: props.user.id }), {
      onSuccess: () => {
        // Manejo de éxito
      }
    });
  } else {
    form.post(route('admin.user.store'), {
      onSuccess: () => {
        form.reset('password', 'password_confirmation');
      }
    });
  }
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div>
      <label class="lbl">Nombre</label>
      <input v-model="form.name" class="ipt" required />
      <span class="err" v-if="form.errors.name">{{ form.errors.name }}</span>
    </div>

    <div>
      <label class="lbl">Email</label>
      <input v-model="form.email" type="email" class="ipt" required />
      <span class="err" v-if="form.errors.email">{{ form.errors.email }}</span>
    </div>

    <div>
      <label class="lbl">Rol</label>
      <select v-model="form.role" class="ipt" required>
        <option disabled value="">Seleccione un rol</option>
        <option :value="r.id" v-for="r in roles" :key="r.id">{{ r.name }}</option>
      </select>
      <span class="err" v-if="form.errors.role">{{ form.errors.role }}</span>
    </div>

    <!-- Gerencia -->
    <div>
      <label class="lbl">Gerencia</label>
      <select v-model="form.gerencia_id" class="ipt" required>
        <option disabled value="">Seleccione una gerencia</option>
        <option :value="g.id" v-for="g in gerencias" :key="g.id">{{ g.name }}</option>
      </select>
      <span class="err" v-if="form.errors.gerencia_id">{{ form.errors.gerencia_id }}</span>
    </div>

    <!-- Mostrar campos de contraseña sólo al crear -->
    <template v-if="!isEdit">
      <div>
        <label class="lbl">Contraseña</label>
        <input v-model="form.password" type="password" class="ipt" required />
        <span class="err" v-if="form.errors.password">{{ form.errors.password }}</span>
      </div>

      <div>
        <label class="lbl">Confirmar contraseña</label>
        <input v-model="form.password_confirmation" type="password" class="ipt" required />
      </div>
    </template>

    <button class="btn-primary" :disabled="form.processing">{{ submitLabel }}</button>
  </form>
</template>

<style scoped>
</style>
