<template>
  <div class="">
    <div class="">
      <div class="my-3">
        <b-button variant="success"
                  v-if="current_user_permissions.permission_to_create"
                  @click="handleShowCreateModal"
                  size="sm"
        >
          Добавить терминал
          <i class="fa fa-plus"></i>
        </b-button>

        <b-button
          v-if="current_user_permissions.permission_to_trash"
          variant="warning" size="sm"
          :href="deleted ? '/terminals/v2' : '?deleted=1'"
        >
          {{ deleted ? 'Назад' : `Корзина` }}
          <i v-if="!deleted" class="fa fa-trash"></i>
        </b-button>
      </div>

      <slot></slot>

      <terminals-table
        :fields="fields"
        :items="items"
        :busy="loading"
        :sort-by.sync="sortBy"
        :sort-desc.sync="sortDesc"
        :page="currentPage"
        :can-view="current_user_permissions.permission_to_view"
        :can-edit="current_user_permissions.permission_to_edit"
        :can-delete="current_user_permissions.permission_to_delete"
        :can-read-logs="current_user_permissions.permission_to_logs_read"
        :can-restore="current_user_permissions.permission_to_trash"
        :is-trash-mode="!!deleted"
        @edit="handleShowEditModal"
        @delete="handleDeleteTerminal"
        @restore="handleRestore"
        @read-logs="handleReadLogs"
      />

      <div class="card">
        <div class="card-body">
          <b-row class="w-100 d-flex justify-content-center">
            <b-col class="my-1 d-flex justify-content-left">
              <b-pagination
                :disabled="busy"
                v-model="currentPage"
                :total-rows="totalRows"
                :per-page="perPage"
                align="fill"
                class="my-0"
              ></b-pagination>
            </b-col>
          </b-row>
          <b-row class="w-100 d-flex justify-content-center">
            <b-col class="my-1 d-flex justify-content-left">
              <p class="text-center">
                Количество элементов: {{ totalRows }}
              </p>
            </b-col>
          </b-row>
        </div>
      </div>

      <b-modal
        size="lg"
        v-model="showModal"
        ref="users_modal"
        hide-footer
        :title="modalTitle"
      >
        <b-row class="mb-3">
          <b-col lg="12">
            <label>
              <b class="text-danger text-bold">* </b>
              AnyDesk:
            </label>
            <b-form-input
              v-model="form.name"
              id="input-small"
              size="sm"
              placeholder="Введите AnyDesk"
            />
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col lg="12">
            <label class="mb-1" for="company">
              <b class="text-danger text-bold">* </b>
              Компании
            </label>
            <multiselect
              v-model="form.company"
              @search-change="searchCompany"
              :options="companyOptions"
              :searchable="true"
              :close-on-select="true"
              :show-labels="false"
              placeholder="Выберите компанию"
              label="name"
              class="is-invalid"
            >
              <span slot="noResult">Результатов не найдено</span>
              <span slot="noOptions">Список пуст</span>
            </multiselect>
          </b-col>
        </b-row>

        <b-row class="mb-3">
          <b-col lg="12">
            <label class="mb-1" for="stamp">
              Штамп
            </label>
            <multiselect
              v-model="form.stamp"
              @search-change="searchStamp"
              :options="stampOptions"
              :searchable="true"
              :close-on-select="true"
              :show-labels="false"
              placeholder="Выберите штамп"
              label="name"
              class="is-invalid"
            >
              <span slot="noResult">Результатов не найдено</span>
              <span slot="noOptions">Список пуст</span>
            </multiselect>
          </b-col>
        </b-row>

        <b-row class="mb-3">
          <b-col lg="12">
            <label>
              <b class="text-danger text-bold">* </b>
              Часовой пояс:
            </label>
            <b-form-input
              v-model="form.timezone"
              size="sm"
              placeholder="Введите часовой пояс"
            />
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col lg="12">
            <label>
              <b class="text-danger text-bold">* </b>
              Пункт выпуска:
            </label>
            <b-form-select
              v-model="form.pvId"
              :options="[{ value: null, text: 'Выберите пункт выпуска', disabled: true }, ...pvOptions]"
            />
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col lg="12">
            <label>
              <b class="text-danger text-bold">* </b>
              Дата поверки:
            </label>
            <b-form-datepicker
              v-model="form.dateCheck"
              size="sm"
              placeholder="Укажите дату поверки"
              locale="ru"
            />
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col lg="12">
            <label>
              <b class="text-danger text-bold">* </b>
              Серийный номер:
            </label>
            <b-form-input
              v-model="form.serialNumber"
              size="sm"
              placeholder="Введите серийный номер"
            />
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col lg="12">
            <label class="mb-2">
              <b class="text-danger text-bold">* </b>
              Комплектующие:
            </label>
            <devices-input
              v-model="form.devices"
              :options="devicesOptions"
            />
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col lg="12">
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

        <div class="row mt-2 mx-2 d-flex justify-content-end">
          <b-button variant="danger" @click="hideModal">Закрыть</b-button>
          <b-button class="ml-2" variant="success" @click="submitForm">Сохранить</b-button>
        </div>
      </b-modal>
    </div>

    <b-modal
      v-model="logsModalShow"
      :title="'Журнал действий'"
      :static="true"
      size="lg"
      hide-footer>
      <logs-modal ref="logsModal"/>
    </b-modal>
  </div>
</template>

<script>
import vSelect from "vue-select";
import 'vue-select/dist/vue-select.css';
import Swal2 from "sweetalert2";
import DevicesInput from "../../components/admin/users/ui/DevicesInput.vue";
import LogsModal from "../../components/logs/logs-modal.vue";
import {
  createTerminal,
  deleteTerminal,
  fetchConnectionStatus,
  fetchTerminalItem,
  fetchTerminalsToCheck,
  fetchTerminalTableItems,
  updateTerminal
} from "./api";
import TerminalsTable from "./TerminalsTable.vue";

export default {
  name: "TerminalsIndexWidget",
  props: ['users', 'deleted', 'points', 'current_user_permissions', 'fields', 'devicesOptions'],
  components: {TerminalsTable, DevicesInput, Swal2, vSelect, LogsModal},
  data() {
    return {
      showModal: false,
      editMode: false,
      busy: false,
      companyOptions: [],
      stampOptions: [],
      pvOptions: [],

      currentPage: 1,
      totalRows: 0,
      perPage: 100,
      sortBy: '',
      sortDesc: false,

      form: {
        id: null,
        name: null,
        timezone: null,
        blocked: 0,
        stamp: null,
        company: null,
        pvId: null,
        serialNumber: null,
        dateCheck: null,
        devices: []
      },
      items: [],
      loading: false,
      logsModalShow: false,
    }
  },
  computed: {
    modalTitle() {
      return this.editMode ? 'Редактирование терминала' : 'Добавление терминала'
    },
  },
  methods: {
    handleReadLogs(modelId) {
      this.logsModalShow = true
      this.$refs.logsModal.loadData(modelId)
    },
    loadConnectionStatus() {
      if (this.items.length === 0) {
        return
      }

      const terminals_id = this.items.map((item) => {
        return item.id
      })

      fetchConnectionStatus(terminals_id)
        .then(({data}) => {
          data.forEach(item => {
            this.items.forEach(terminal => {
              if (terminal.id === item.id) {
                terminal.connected = item.connected
              }
            })
          })

          this.$forceUpdate()
        })
    },
    async loadTerminalsToCheck() {
      fetchTerminalsToCheck()
        .then(response => {
          this.items = this.items.map(item => ({
            ...item,
            need_check: {
              in_a_month: response.data.less_month.includes(item.hash_id),
              expired: response.data.expired.includes(item.hash_id),
            }
          }))
        })
    },
    loadTableItems() {
      this.busy = true;
      this.loading = true;

      fetchTerminalTableItems({
        sortBy: this.sortBy,
        sortDesc: this.sortDesc,
        page: this.currentPage,
        take: this.perPage,
      })
        .then(({data}) => {
          this.items = data.items;
          this.currentPage = data.current_page;
          this.totalRows = data.total_rows;
          this.busy = false;
          this.loadConnectionStatus();
          this.loadTerminalsToCheck()
        }).finally(() => {
        this.loading = false;
      })
    },
    async handleDeleteTerminal(id) {
      const result = await Swal2.fire({
        title: 'Вы уверены, что хотите удалить?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Да, удалить!',
        cancelButtonText: 'Отмена',
      })

      if (!result.isConfirmed) {
        return
      }

      try {
        await deleteTerminal(id)

        await Swal2.fire('Удалено', 'Данные были успешно удалены', 'success');
        this.loadTableItems()
      } catch (e) {
        await Swal2.fire('Ошибка', e.data.message, 'warning')
      } finally {
        this.loading = false
      }
    },
    handleRestore(id) {
      axios.post('/terminals/return_trash', {
        id: id,
      }).then(({data}) => {
        if (data.status) {
          Swal2.fire('Восстановлено', 'Данные были успешно восстановлены', 'success');
          this.items = this.items.filter((item) => {
            return item.id != id;
          })
        } else {
          Swal2.fire('Ошибка', data.message, 'warning');
        }
      }).finally(() => {
        this.loading = false;
      });
    },
    validateForm() {
      const deviceSerialNumbers = this.form.devices.map(item => item.serial_number)

      const validateDevices = () => {
        return !!this.form.devices.length && !this.form.devices.filter(item => !item.serial_number).length
      }

      if (!this.form.name ||
        !this.form.timezone ||
        !this.form.pvId ||
        !this.form.company ||
        !this.form.serialNumber ||
        !this.form.dateCheck ||
        !validateDevices()) {
        this.$toast('Не все поля указаны', {type: 'error'});

        return false
      }

      if ([...(new Set(deviceSerialNumbers))].length !== deviceSerialNumbers.length) {
        this.$toast('Серийные номера комплектующих не должны совпадать', {type: 'error'})

        return false
      }

      return true
    },
    async submitForm() {
      this.loading = true;

      if (!this.validateForm()) {
        return
      }

      const requestBody = {
        name: this.form.name,
        blocked: this.form.blocked,
        timezone: this.form.timezone,
        pv_id: this.form.pvId,
        stamp_id: this.form.stamp?.id ?? null,
        company_id: this.form.company.id,
        serial_number: this.form.serialNumber,
        date_check: this.form.dateCheck,
        devices: this.form.devices.map((item) => {
          return {
            slug: item.id,
            serial_number: item.serial_number,
          }
        }),
      }

      const handler = async () => {
        if (this.editMode) {
          return await updateTerminal(this.form.id, requestBody)
        } else {
          return await createTerminal(requestBody)
        }
      }

      try {
        await handler()

        await Swal2.fire('Сохранено', 'Данные были успешно записаны', 'success');
        this.showModal = false
        location.reload()
      } catch (e) {
        let errorText = 'Произошла ошибка. Попробуйте, пожалуйста, позже'
        if (e?.response?.data?.errors?.length) {
          errorText = e?.response?.data?.errors.join('\n')
        } else {
          console.error(e)
        }

        await Swal2.fire('Ошибка', errorText, 'error');
      } finally {
        this.loading = false
      }
    },
    handleShowCreateModal() {
      this.showModal = true
      this.editMode = false
    },
    async handleShowEditModal(id) {
      const {data} = await fetchTerminalItem(id)

      let stamp = null
      if (data.stamp_id) {
        stamp = {
          id: data.stamp_id,
          name: `[${data.stamp_id}] ${data.stamp_name}`
        }
      }

      this.form = {
        id: data.id,
        name: data.name,
        blocked: data.blocked,
        company: {
          id: data.company_id,
          name: `[${data.company_hash_id}] ${data.company_name}`
        },
        stamp: stamp,
        pvId: data.pv_id,
        timezone: data.timezone,
        dateCheck: data.date_check,
        serialNumber: data.serial_number,
        devices: data.devices.map(device => ({
          id: device.device_name,
          serial_number: device.device_serial_number,
        }))
      }

      this.showModal = true
      this.editMode = true
    },
    resetModal() {
      this.form = {
        id: null,
        name: null,
        timezone: null,
        blocked: 0,
        stamp: null,
        company: null,
        pvId: null,
        serialNumber: null,
        dateCheck: null,
        devices: []
      }
    },
    hideModal() {
      this.showModal = false
    },
    searchCompany(query = '') {
      axios.get('/api/companies/find', {
        params: {
          search: query
        }
      }).then(({data}) => {
        data.forEach(company => {
          company.name = `[${company.hash_id}] ${company.name}`;
        });
        this.companyOptions = data;
      });
    },
    searchStamp(query = '') {
      axios.get('/stamp/find', {
        params: {
          search: query
        }
      }).then(({data}) => {
        data.forEach(stamp => {
          stamp.name = `[${stamp.id}] ${stamp.name}`;
        });
        this.stampOptions = data;
      });
    },
  },
  mounted() {
    this.loadTableItems()
    this.searchCompany();
    this.searchStamp();
    this.pvOptions = this.points;

    setInterval(this.loadConnectionStatus, 5000);
  },
  watch: {
    showModal(val) {
      if (!val) {
        this.resetModal()
      }
    },
    currentPage() {
      this.loadTableItems();
    },
    sortBy() {
      this.loadTableItems()
    },
    sortDesc() {
      this.loadTableItems()
    },
  },
}
</script>

<style>
.box {
  display: flex;
  flex-wrap: wrap;
  flex-direction: column;
  width: 500px;
  max-height: 400px;
  align-content: space-between;
}

.modal-dialog.modal-xl {
  max-width: 90%;
  margin: 1.75rem auto;
}

.row-check-in-a-month {
  background-color: #fbf1d3 !important;
}

.row-check-expired {
  background-color: #fbd3d3 !important;
}
</style>
