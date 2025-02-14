<script>
export default {
    name: "ButtonWithPending",
    props: {
        pending: {
            type: Boolean,
            required: true,
        },
    }
}
</script>

<template>
    <button :disabled="pending" @click="$emit('click')">
        <template v-if="pending">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <slot name="loading"></slot>
        </template>
        <template v-if="!pending">
            <slot name="default"></slot>
        </template>
    </button>
</template>

<style scoped>

</style>
