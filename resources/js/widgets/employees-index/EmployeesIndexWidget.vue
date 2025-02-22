<script setup>
import {usePageSetup} from "./usePageSetup";
import {computed, onMounted, ref} from "vue";
import EmployeesTable from "./EmployeesTable.vue";
import EmployeesPagination from "./EmployeesPagination.vue";
import EmployeesFilter from "./EmployeesFilter.vue";
import LogsModal from "../../components/logs/logs-modal.vue";
import {useEmployeesTable} from "./useEmployeesTable";
import {usePermissions} from "./usePermissions";
import {useDeleteEmployee} from "./useDeleteEmployee";
import {useRestoreEmployee} from "./useRestoreEmployee";
import EmployeeModal from "./EmployeeModal.vue";

const {fields, isTrashMode} = usePageSetup()
const {can} = usePermissions()
const {fetchTablePending, table, params, fetchEmployeesItems} = useEmployeesTable()
const {deleteEmployeePending, performDeleteEmployee} = useDeleteEmployee()
const {restoreEmployeePending, performRestoreEmployee} = useRestoreEmployee()

const employeeModalElement = ref(null)
const logsModalElement = ref(null)
const logsModalShow = ref(false)

const tableBusy = computed(() => {
  return fetchTablePending.value || deleteEmployeePending.value || restoreEmployeePending.value
})

const handleShowCreateModal = () => {
  employeeModalElement.value.show()
}

const handleShowEditModal = (id) => {
  employeeModalElement.value.show(id)
}

const handleCloseEmployeeModal = async (withChanges) => {
  if (withChanges) {
    await fetchEmployeesItems()
  }
}

const handleDelete = async (id) => {
  performDeleteEmployee(id)
    .then(async () => {
      await fetchEmployeesItems()
    })
}

const handleRestore = (id) => {
  performRestoreEmployee(id)
    .then(async () => {
      await fetchEmployeesItems()
    })
}

const handleLogsRead = (id) => {
  logsModalShow.value = true
  logsModalElement.value.loadData(id)
}

onMounted(async () => {
  await fetchEmployeesItems()
})
</script>

<template>
  <div>
    <div>
      <div class="my-3">
        <b-button
          variant="success"
          v-if="can.create"
          @click="handleShowCreateModal"
          size="sm"
        >
          Добавить пользователя
          <i class="fa fa-plus"></i>
        </b-button>

        <b-button
          v-if="can.trash"
          variant="warning" size="sm"
          :href="isTrashMode ? '/employees' : '?deleted=1'"
        >
          {{ isTrashMode ? 'Назад' : `Корзина` }}
          <i v-if="!isTrashMode" class="fa fa-trash"></i>
        </b-button>
      </div>

      <employees-filter v-if="can.view"/>

      <employees-table
        :is-trash-mode="isTrashMode"
        :can-view="can.view"
        :can-restore="can.trash"
        :can-delete="can.delete"
        :can-read-logs="can.logsRead"
        :can-edit="can.edit"
        :current-page="table.page"
        :fields="fields"
        :items="table.items"
        :busy="tableBusy"
        :sort-by.sync="params.sortBy"
        :sort-desc.sync="params.sortDesc"
        @edit="handleShowEditModal"
        @delete="handleDelete"
        @logs-read="handleLogsRead"
        @restore="handleRestore"
      />

      <employees-pagination
        ref="employeeModalElement"
        v-model="table.page"
        :pending="tableBusy"
        :total="table.total"
        :perPage="table.perPage"
      />

      <employee-modal
        ref="employeeModalElement"
        @close="handleCloseEmployeeModal"
      />

      <b-modal
        v-model="logsModalShow"
        :title="'Журнал действий'"
        :static="true"
        size="lg"
        hide-footer>
        <logs-modal ref="logsModalElement"/>
      </b-modal>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
