<script setup>
const props = defineProps({
  pending: {
    type: Boolean,
    required: true,
  },
  value: {
    type: Number,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
  perPage: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['input'])

const handleUpdatePage = (value) => {
  emit('input', value)
}
</script>

<template>
  <div class="card">
    <div class="card-body">
      <b-row class="w-100 d-flex justify-content-center">
        <b-col class="my-1 d-flex justify-content-left">
          <b-pagination
              :value="props.value"
              :disabled="props.pending"
              :total-rows="props.total"
              :per-page="props.perPage"
              align="fill"
              class="my-0"
              @change="handleUpdatePage"
          />
        </b-col>
      </b-row>
      <b-row class="w-100 d-flex justify-content-center">
        <b-col class="my-1 d-flex justify-content-left">
          <p class="text-center">
            Количество элементов: {{ props.total }}
          </p>
        </b-col>
      </b-row>
    </div>
  </div>
</template>

<style scoped>

</style>
