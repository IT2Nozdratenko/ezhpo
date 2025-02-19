<script>
export default {
    name: "DevicesInput",
    props: {
        options: {
            type: Array,
            required: true,
        },
        value: {
            type: Array,
            required: true
        }
    },
    emits: ['input'],
    computed: {
        resultValues() {
            return this.options.reduce((acc, cur) => {
                acc[cur.id] = {
                    checked: !!this.value.filter(item => item.id === cur.id).length,
                    serialNumber: this.value.filter(item => item.id === cur.id)[0]?.serial_number ?? '',
                }

                return acc
            }, {})
        }
    },
    methods: {
        inputCheckbox(id) {
            const current = this.value.filter(item => item.id === id)[0]
            let newValue = []

            if (current) {
                newValue = this.value.filter(item => item.id !== id)
            }
            else {
                newValue = this.value
                newValue.push({ id: id, serial_number: '' })
            }

            this.$emit('input', newValue)
        },

        inputSerialNumber(id, value) {
            const current = this.value.filter(item => item.id === id)[0]

            if (current) {
                current.serial_number = value
            }

            this.$emit('input', this.value)
        }
    }
}
</script>

<template>
    <div class="device-input-wrapper">
        <b-row v-for="(item, index) of options" class="mb-1" :key="index">
            <b-col lg="3">
                <b-checkbox
                    @change="inputCheckbox(item.id)"
                    :value="item.id"
                    :checked="resultValues[item.id].checked ? item.id : null"

                >{{ item.text }}</b-checkbox>
            </b-col>
            <b-col lg="9">
                <b-form-input
                    :value="resultValues[item.id].serialNumber"
                    :disabled="!resultValues[item.id].checked"
                    placeholder="Введите серийный номер"
                    @input="value => inputSerialNumber(item.id, value)"
                ></b-form-input>
            </b-col>
        </b-row>
    </div>
</template>

<style scoped>

</style>
