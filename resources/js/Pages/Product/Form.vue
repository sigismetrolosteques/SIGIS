<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  product: { type: Object, default: null },
  submitLabel: String
});

const form = useForm({
  name: props.product?.name ?? '',
  description: props.product?.description ?? '',
  price: props.product?.price ?? '',
  stock: props.product?.stock ?? '',
});

const isEdit = !!props.product;

function submit() {
  if (isEdit) {
    form.post(route('product.update', props.product.id), {
      onSuccess: () => {}
    });
  } else {
    form.post(route('product.store'), {
      onSuccess: () => form.reset()
    });
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
      <label class="lbl">Descripción</label>
      <textarea v-model="form.description" class="ipt"></textarea>
      <span class="err" v-if="form.errors.description">{{ form.errors.description }}</span>
    </div>

    <div>
      <label class="lbl">Precio</label>
      <input v-model="form.price" type="number" step="0.01" class="ipt" />
      <span class="err" v-if="form.errors.price">{{ form.errors.price }}</span>
    </div>

    <div>
      <label class="lbl">Stock</label>
      <input v-model="form.stock" type="number" class="ipt" />
      <span class="err" v-if="form.errors.stock">{{ form.errors.stock }}</span>
    </div>

    <button class="btn-primary" :disabled="form.processing">{{ submitLabel }}</button>
  </form>
</template>
