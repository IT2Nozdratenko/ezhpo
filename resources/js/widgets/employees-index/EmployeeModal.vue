<script setup>
import UserPvsList from "../../components/admin/users/components/UserPvsList.vue";
import vSelect from "vue-select";
import Swal2 from "sweetalert2";
import {useEmployeeForm} from "./useEmployeeForm";
import {computed, watch} from "vue";
import {debounce} from "lodash";
import {useEmployeeModal} from "./useEmployeeModal";
import {createEmployee, updateEmployee} from "./api";
import {ref} from "vue";
const emit = defineEmits(['close'])

const {
  fetchFormPending,
  form,
  permissionTabExpanded,
  searchPermissions,
  displayedPermissions,
  fetchForm,
  fetchPermissions,
  makeParams,
  resetForm,
} = useEmployeeForm()
const {
  isShow,
  employeeId,
  showModal,
  rolesModalOptions,
  clientRoleId,
  pointsModalOptions,
  headOperatorSdpoRoleId,
  allPvs,
} = useEmployeeModal()

defineExpose({
  show: showModal,
})

const pending = ref(false)

const isEditingMode = computed(() => {
  return !!employeeId.value
})

const title = computed(() => {
  return (isEditingMode.value ? 'Редактирование' : 'Добавление') + ' сотрудника'
})

const isClient = computed(() => {
  return form.roles.some((item) => item.id === clientRoleId.value)
})

const isHeadOperatorSdpo = computed(() => {
  return form.roles.some((item) => item.id === headOperatorSdpoRoleId.value)
})

const handlePvsSelect = (value) => {
  form.pvs = value
}

const handleClose = () => {
  isShow.value = false
  emit('close', false)
}

const handleSubmit = async () => {
  const params = makeParams()
  pending.value = true

  try {
    if (isEditingMode.value) {
      await updateEmployee(employeeId.value, params)
    }
    else {
      await createEmployee(params)
    }

    console.log('exit')
    emit('close', true)
    isShow.value = false
  } catch (e) {
    let message = 'При сохранении произошла ошибка'
    const data = e.response.data

    if (typeof data.message === 'string') {
      message = data.message
    }

    if (typeof data.errors === 'object' && data.errors !== null) {
      message = Object
        .values(data.errors)
        .reduce((carry, fieldErrors) => {
          if (Array.isArray(fieldErrors)) {
            return carry + (carry ? '</br>' : '') + fieldErrors.join('</br>')
          }
          return carry;
        }, '')
    }

    Swal2.fire('Ошибка', message, 'error');
  } finally {
    pending.value = false
  }
}

watch(
  () => isShow.value,
  debounce(async (value) => {
    if (!value || !isEditingMode.value) {
      resetForm()
      return
    }

    await fetchForm(employeeId.value)
    await fetchPermissions()
  })
)
</script>

<template>
  <b-modal
    v-model="isShow"
    size="xl"
    :busy="fetchFormPending"
    hide-footer
    :title="title"
  >
    <b-row class="my-1">
      <b-col lg="2">
        <label>ФИО:</label>
      </b-col>
      <b-col lg="5">
        <b-form-input
          v-model="form.name"
          id="input-small"
          size="sm"
          placeholder="Введите ФИО"
        />
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col lg="2">
        <label>Login:</label>
      </b-col>
      <b-col lg="5">
        <b-form-input
          v-model="form.login"
          size="sm"
          placeholder="Введите логин"
        />
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col lg="2">
        <label>E-mail:</label>
      </b-col>
      <b-col lg="5">
        <b-form-input
          v-model="form.email"
          size="sm"
          placeholder="Введите эл. почту"
        />
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col lg="2">
        <label>Пароль:</label>
      </b-col>
      <b-col lg="5">
        <b-form-input
          v-model="form.password"
          size="sm"
          type="password"
          placeholder="Введите пароль"
        />
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col lg="2">
        <label>ЭЦП:</label>
      </b-col>
      <b-col lg="5">
        <b-form-input
          v-model="form.eds"
          size="sm"
          placeholder="Введите эл. подпись"
        />
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col lg="2">
        <label>Срок действия ЭЦП:</label>
      </b-col>
      <b-col lg="5" class="d-flex align-items-baseline" style="gap: 5px">
        с
        <input v-model="form.validityEdsStart" type="date" name="date" class="form-control">
        по
        <input v-model="form.validityEdsEnd" type="date" name="date" class="form-control">
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col lg="2">
        <label>Часовой пояс:</label>
      </b-col>
      <b-col lg="5">
        <b-form-input
          v-model="form.timezone"
          size="sm"
          placeholder="Введите часовой пояс"
        />
      </b-col>
    </b-row>
    <b-row
      v-if="!isClient"
      class="my-1"
    >
      <b-col lg="2">
        <label>Пункт выпуска:</label>
      </b-col>
      <b-col lg="5">
        <b-form-select
          v-model="form.pv"
          :options="pointsModalOptions"
        />
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col lg="2">
        <label>Роль:</label>
      </b-col>
      <b-col lg="5">
        <v-select
          :multiple="true"
          :options="rolesModalOptions"
          label="text"
          v-model="form.roles"
        >
        </v-select>
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col lg="10" offset-lg="2">
        <b-form-checkbox
          id="checkbox-1"
          v-model="form.blocked"
          name="checkbox-1"
          value="1"
          unchecked-value="0"
        >
          Заблокирован
        </b-form-checkbox>
      </b-col>
    </b-row>
    <b-row class="my-1">
      <b-col>
        <b-button
          :class="{'collapsed': permissionTabExpanded}"
          :aria-expanded="permissionTabExpanded"
          aria-controls="collapse-4"
          size="sm"
          @click="() => permissionTabExpanded = !permissionTabExpanded"
        >
          Раскрыть права
        </b-button>
        <b-collapse id="collapse-4" v-model="permissionTabExpanded" class="mt-2">
          <div class="alert alert-success my-3 text-center">
            Не все права можно выставить, так как они предусматриваются наличием роли<br>
            У каждой роли есть набор прав<br>
            У каждого пользователя есть набор прав и ролей
          </div>
          <div class="col-lg-5 mx-0 px-0 mb-3">
            <b-form-input v-model="searchPermissions" placeholder="Поиск прав"/>
          </div>
          <b-card>
            <b-form-group label="Доступы:" v-slot="{ ariaDescribedby }">
              <b-form-checkbox-group
                :aria-describedby="ariaDescribedby"
                name="flavour-2"
                v-model="form.permissionIds"
              >
                <b-row>
                  <div class="box">
                    <div v-for="(item, index) in displayedPermissions">
                      <b-col>
                        <b-form-checkbox
                          :value="item.id"
                          :disabled="item.disabled"
                          :key="index"
                        >
                          {{ item.text }}
                        </b-form-checkbox>
                      </b-col>
                    </div>
                  </div>
                </b-row>
              </b-form-checkbox-group>
            </b-form-group>
          </b-card>
        </b-collapse>
      </b-col>
    </b-row>
    <b-row class="my-1" v-if="isHeadOperatorSdpo">
      <user-pvs-list
        :all-items="allPvs"
        :selected-items="form.pvs"
        @input="handlePvsSelect"
      >
      </user-pvs-list>
    </b-row>
    <b-row class="my-1">
      <b-col>
        <div class="row mt-2 mx-2 d-flex justify-content-end">
          <b-button variant="danger" @click="handleClose">Закрыть</b-button>
          <b-button class="ml-2" variant="success" @click="handleSubmit" :disabled="pending">Сохранить</b-button>
        </div>
      </b-col>
    </b-row>
  </b-modal>
</template>

<style scoped lang="scss"></style>