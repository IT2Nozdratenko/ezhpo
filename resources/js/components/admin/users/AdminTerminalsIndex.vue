<template>
    <div class="">
        <div class="">
            <div class="my-3">
                <b-button variant="success"
                          v-if="current_user_permissions.permission_to_create"
                          @click="showModal"
                          size="sm"
                >
                    Добавить терминал
                    <i class="fa fa-plus"></i>
                </b-button>

                <b-button variant="success"
                          v-if="current_user_permissions.permission_to_create"
                          @click="changeSettings"
                          size="sm"
                          :disabled="selectedIdsIsEmpty"
                >
                    Изменить настройки ({{ selectedIds.length }})
                    <i class="fa fa-cog"></i>
                </b-button>

                <b-button variant="success"
                          v-if="current_user_permissions.permission_to_create"
                          @click="changeDefaultSettings"
                          size="sm"
                >
                    Изменить настройки по умолчанию
                    <i class="fa fa-cogs"></i>
                </b-button>

                <b-button
                    v-if="current_user_permissions.permission_to_trash"
                    variant="warning" size="sm"
                    :href="deleted ? '/terminals' : '?deleted=1'"
                >
                    {{ deleted ? 'Назад' : `Корзина` }}
                    <i v-if="!deleted" class="fa fa-trash"></i>
                </b-button>
            </div>

            <slot></slot>

            <div class="card table-card">
                <div class="card-body">
                    <b-table
                        v-if="current_user_permissions.permission_to_view"
                        :items="items"
                        :fields="columns"
                        ref="users_table"
                        striped hover
                        no-local-sorting
                        selectable
                        :busy="loading"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                        :current-page="currentPage"
                        :tbody-tr-class="tableRowClass"
                        @sort-changed="sortChanged"
                        @row-click="checked"
                    >
                        <template #head(selected)>
                            <b-form-checkbox
                                v-model="selectedAll"
                                @change="toggleAll"
                            ></b-form-checkbox>
                        </template>

                        <template #cell(selected)="row">
                            <b-form-checkbox
                                v-model="selectedIds"
                                :value="row.item.id"
                                @click.stop
                            >
                            </b-form-checkbox>
                        </template>

                        <template #cell(name)="row">
                            <template v-if="current_user_permissions.permission_to_edit">
                                <a href="#" @click="editUserData(row.item)">
                                    {{ row.value || 'Неизвестно' }}
                                </a>
                            </template>
                            <template v-else>
                                {{ row.value || 'Неизвестно' }}
                            </template>
                        </template>

                        <template #cell(status)="{ item }">
                            <span v-if="item.connected" class="badge badge-success">on</span>
                            <span v-else class="badge badge-danger">off</span>
                        </template>

                        <template #cell(pv)="row">
                             {{ row.value.name }}
                        </template>

                        <template #cell(town)="{ item }">
                            {{ item.pv && item.pv.town ? item.pv.town.name : 'Неизвестно' }}
                        </template>

                        <template #cell(company_id)="{ item }">
                            {{ item.company ? item.company.name : 'Неизвестно' }}
                        </template>

                        <template #cell(stamp_id)="{ item }">
                            {{ item.stamp ? item.stamp.name : 'Неизвестно' }}
                        </template>

                        <template #cell(serial_number)="{ item }">
                            {{ item.terminal_check ? item.terminal_check.serial_number : '' }}
                        </template>

                        <template #cell(date_check)="{ item }">
                            {{ item.terminal_check ? formatDate(item.terminal_check.date_check) : '' }}
                        </template>

                        <template #cell(devices)="{ item }">
                            <div v-if="item.terminal_devices">
                                <div v-for="device of formatDevices(item.terminal_devices)">
                                    {{ device.name }} ({{ device.serialNumber }})
                                </div>
                            </div>
                        </template>

                        <template #cell(blocked)="row">
                            {{ row.value === '1' ? 'Да' : 'Нет' }}
                        </template>

                        <template #cell(buttons)="row">
                            <div class="d-flex">
                                <b-button
                                    v-if="current_user_permissions.permission_to_logs_read"
                                    size="sm"
                                    variant="primary"
                                    @click="logsRead(row.item.id)"
                                    title="Журнал действий"
                                >
                                    <i class="fa fa-book"></i>
                                </b-button>
                                <b-button
                                    v-if="!deleted"
                                    :disabled="!current_user_permissions.permission_to_delete"
                                    variant="danger"
                                    size="sm"
                                    class="ml-1"
                                    @click="deleteUser(row.item.id)">
                                    <i class="fa fa-trash"></i>
                                </b-button>
                                <b-button
                                    v-if="deleted"
                                    :disabled="!current_user_permissions.permission_to_trash"
                                    variant="warning"
                                    size="sm"
                                    class="ml-1"
                                    @click="returnTrash(row.item.id)">
                                    <i class="fa fa-undo"></i>
                                </b-button>
                            </div>
                        </template>
                    </b-table>
                </div>
            </div>

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
                v-model="enableModal"
                ref="users_modal"
                hide-footer
                :title="'Добавление терминала'"
            >

                <b-row class="mb-3">
                    <b-col lg="12">
                        <label>
                            <b class="text-danger text-bold">* </b>
                            AnyDesk:
                        </label>
                        <b-form-input v-model="infoModalUser.name"
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
                            v-model="infoModalUser.company"
                            @search-change="searchCompany"
                            @select="(company) => infoModalUser.company_id = company.id"
                            :options="companies"
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
                            v-model="infoModalUser.stamp"
                            @search-change="searchStamp"
                            @select="(stamp) => infoModalUser.stamp_id = stamp.id"
                            @remove="infoModalUser.stamp_id = null"
                            :options="stamps"
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
                        <b-form-input v-model="infoModalUser.timezone"
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
                        <b-form-select v-model="infoModalUser.pv_id"
                               :options="[{ value: null, text: 'Выберите пункт выпуска', disabled: true }, ...optionsPvs]"/>
                    </b-col>
                </b-row>
                <b-row class="mb-3">
                    <b-col lg="12">
                        <label>
                            <b class="text-danger text-bold">* </b>
                            Дата поверки:
                        </label>
                        <b-form-datepicker v-model="infoModalUser.dateCheck"
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
                        <b-form-input v-model="infoModalUser.serialNumber"
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
                        <devices-input v-model="infoModalUser.devices" :options="devicesOptions"/>
                    </b-col>
                </b-row>
                <b-row class="mb-3">
                    <b-col lg="12">
                        <b-form-checkbox
                            id="checkbox-1"
                            v-model="infoModalUser.blocked"
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
                    <b-button class="ml-2" variant="success" @click="saveUser">Сохранить</b-button>
                </div>
            </b-modal>
        </div>

        <b-modal
            v-model="logsModalShow"
            :title="'Журнал действий'"
            :static="true"
            size="lg"
            hide-footer>
            <logs-modal ref="logsModal">
            </logs-modal>
        </b-modal>
    </div>
</template>

<script>
import vSelect from "vue-select";
import 'vue-select/dist/vue-select.css';
import Swal2 from "sweetalert2";
import DevicesInput from "./ui/DevicesInput.vue";
import LogsModal from "../../logs/logs-modal";

export default {
    name: "AdminTerminalsIndex",
    props: ['users', 'deleted', 'roles', 'points', 'all_permissions', 'current_user_permissions', 'options_company', 'fields', 'devicesOptions'],
    components: {DevicesInput, Swal2, vSelect, LogsModal },

    data() {
        return {
            allPermissions: [],
            enableModal: false,
            permission_collapse: false,
            searchPermissions: '',
            infoModalUser_roles: [],
            busy: false,
            companies: [],
            stamps: [],

            selectedAll: false,
            selectedIds: [],

            currentPage: 1,
            totalRows: 0,
            perPage: 100,
            sortBy: '',
            sortDesc: false,

            infoModalUser: {
                id: null,
                name: null,
                login: null,
                email: null,
                password: null,
                eds: null,
                timezone: null,
                pv: null,
                blocked: 0,
                company: null,
                permissions: [],
                serialNumber: null,
                dateCheck: null,
                devices: []
            },
            optionsPvs: [],
            optionsRoles: [],
            columns: [],
            items: [],
            loading: false,
            logsModalShow: false,
        }
    },
    computed: {
        selectedIdsIsEmpty() {
            return this.selectedIds.length === 0;
        }
    },
    methods: {
        toggleAll() {
            if (this.selectedAll) {
                this.selectedIds = this.items.map((row) => row.id);
                return;
            }
            this.selectedIds = [];
        },
        checked(item) {
            const index = this.selectedIds.indexOf(item.id);
            if (index === -1) {
                this.selected.push(item.id);
            } else {
                this.selected.splice(index, 1);
            }
        },
        changeSettings() {
            const params = new URLSearchParams({
                terminal_ids: this.selectedIds,
            });
            window.location.href = `/terminals/sync-settings?${params.toString()}`;
        },
        changeDefaultSettings() {
            const params = new URLSearchParams({
                terminal_ids: [null],
            });
            window.location.href = `/terminals/sync-settings?${params.toString()}`;
        },
        logsRead(modelId) {
            this.logsModalShow = true
            this.$refs.logsModal.loadData(modelId)
        },
        sortChanged(e) {
            this.sortBy = e.sortBy;
            this.sortDesc = e.sortDesc;
            this.loadData();
        },
        loadConnectionStatus() {
          if (this.items.length === 0) {
              return;
          }

          const terminals_id = this.items.map((item) => {
              return item.id;
          });

          axios.get('/terminals/status', {
              params: {
                  terminals_id
              }
          }).then(({ data }) => {
            data.forEach(item => {
                this.items.forEach(terminal => {
                    if (terminal.id === item.id) {
                        terminal.connected = item.connected;
                    }
                });
            });

            this.$forceUpdate();
          });
        },
        tableRowClass(item, type) {
            if (item && type === 'row') {
                if (item?.need_check?.in_a_month) {
                    return 'row-check-in-a-month'
                }
                else if (item?.need_check?.expired) {
                    return 'row-check-expired'
                }
                else {
                    return ''
                }
            } else {
                return null
            }
        },
        async fetchTerminalsToCheck() {
            axios.get('/terminals/to-check')
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
        loadData() {
            this.busy = true;
            this.loading = true;

            axios.get('/terminals' + window.location.search, {
                params: {
                    sortBy: this.sortBy,
                    sortDesc: this.sortDesc,
                    page: this.currentPage,
                    take: this.perPage,
                    api: 1,
                },
            }).then(({data}) => {
                this.items = data.items;
                this.currentPage = data.current_page;
                this.totalRows = data.total_rows;
                this.busy = false;
                this.loadConnectionStatus();
                this.fetchTerminalsToCheck()
            }).finally(() => {
                this.loading = false;
            });
        },
        fetchRoleData(e) {
            let newRoles = e.map((item) => {
                return item.id
            })
            let oldRoles = [];

            if (this.infoModalUser.id) {
                oldRoles = this.items.filter((item) => {
                    return item.id == this.infoModalUser.id
                })[0].roles.map((item) => {
                    return item.id
                })
            }

            if (JSON.stringify(newRoles) != JSON.stringify(oldRoles)) {
                this.allPermissions = this.allPermissions.map((item) => {
                    item.disable = false;
                    item.checked = false;

                    return item;
                })
                this.infoModalUser.permissions = [];

                // Если не выьрана роль
                if (newRoles.length == 0) {
                    return;
                }
                axios.get('/users/fetchRoleData', {
                    params: {
                        role_ids: newRoles,
                    },
                }).then(({data}) => {
                    this.allPermissions.map((item, index) => {
                        if (data.includes(item.id)) {
                            this.allPermissions[index].disable = true;
                            this.infoModalUser.permissions.push(item.id)
                        }
                    })

                    this.showModal()
                }).finally(() => {
                    this.loading = false;
                });
            }
        },
        deleteUser(id) {
            Swal2.fire({
                title: 'Вы уверены, что хотите удалить?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Да, удалить!',
                cancelButtonText: 'Отмена',
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('/users', {
                        id: id,
                    }).then(({data}) => {
                        if (data.status) {
                            Swal2.fire('Удалено', 'Данные были успешно удалены', 'success');
                            this.items = this.items.filter((item) => {
                                return item.id != id;
                            })
                        } else {
                            Swal2.fire('Ошибка', data.message, 'warning');
                        }

                    }).finally(() => {
                        this.loading = false;
                    });
                }
            })
        },
        returnTrash(id) {
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

        validateDevices() {
            return !!this.infoModalUser.devices.length && !this.infoModalUser.devices.filter(item => !item.serial_number).length
        },
        saveUser() {
            this.loading = true;

            const deviceSerialNumbers = this.infoModalUser.devices.map(item => item.serial_number)

            if (!this.infoModalUser.name ||
                !this.infoModalUser.timezone ||
                !this.infoModalUser.pv_id ||
                !this.infoModalUser.company_id ||
                !this.infoModalUser.serialNumber ||
                !this.infoModalUser.dateCheck ||
                !this.validateDevices()) {
                this.$toast('Не все поля указаны', { type: 'error' });
                return;
            }

            if ([...(new Set(deviceSerialNumbers))].length !== deviceSerialNumbers.length) {
                this.$toast('Серийные номера комплектующих не должны совпадать', { type: 'error' })
                return;
            }

            axios.post('/terminals', {
                user_id: this.infoModalUser.id,
                name: this.infoModalUser.name,
                timezone: this.infoModalUser.timezone,
                pv: this.infoModalUser.pv_id,
                company_id: this.infoModalUser.company_id,
                blocked:  this.infoModalUser.blocked,
                stamp_id: this.infoModalUser.stamp_id,
                serial_number: this.infoModalUser.serialNumber,
                date_check: this.infoModalUser.dateCheck,
                devices: this.infoModalUser.devices,
            })
                .then(({data}) => {
                    if (data.status) {
                        this.items.forEach((item, i, arr) => {
                            if (item.id == data.user_info.id) {
                                this.items[i] = data.user_info // Новый объект с новыми свойствами
                            }
                        })
                        Swal2.fire('Сохранено', 'Данные были успешно записаны', 'success');
                        this.$refs.users_table.refresh()
                        this.enableModal = false
                        location.reload()

                    }
                }).catch(error => {
                    let errorText = 'Произошла ошибка. Попробуйте, пожалуйста, позже'
                    if (error?.response?.data?.errors?.length) {
                        errorText = error?.response?.data?.errors.join('\n')
                    } else {
                        console.error(error)
                    }

                    Swal2.fire('Ошибка', errorText, 'error');
                }).finally(() => {
                    this.loading = false;
                });
        },

        editUserData(user) {
            const data = { ...user }

            data.dateCheck = null
            data.serialNumber = null
            if (user.terminal_check) {
                data.dateCheck = user.terminal_check.date_check
                data.serialNumber = user.terminal_check.serial_number
            }

            data.devices = []
            if (user.terminal_devices) {
                data.devices = user.terminal_devices.map(item => ({
                    id: item.device_name,
                    serial_number: item.device_serial_number,
                }))
            }

            this.infoModalUser = data

            this.showModal();
        },
        resetModal() {
            this.infoModalUser.id = null;
            this.infoModalUser.name = null;
            this.infoModalUser.login = null;
            this.infoModalUser.email = null;
            this.infoModalUser.password = null;
            this.infoModalUser.eds = null;
            this.infoModalUser.timezone = null;
            this.infoModalUser.pv = null;
            this.infoModalUser.pv_id = null;
            this.infoModalUser_roles = [];
            this.infoModalUser.blocked = 0;
            this.infoModalUser.company = null;
            this.infoModalUser.permissions = [];
            this.infoModalUser.serialNumber = null;
            this.infoModalUser.dateCheck = null;
            this.infoModalUser.devices = [];
            this.permission_collapse = false;

            this.allPermissions = this.allPermissions.map((item) => {
                item.disable = false;

                return item;
            })
        },
        showModal() {
            this.enableModal = true
        },
        hideModal() {
            this.enableModal = false
        },
        searchCompany(query = '') {
            axios.get('/api/companies/find', {
                params: {
                    search: query
                }
            }).then(({ data }) => {
                data.forEach(company => {
                    company.name = `[${company.hash_id}] ${company.name}`;
                });
                this.companies = data;
            });
        },

        searchStamp(query = '') {
            axios.get('/stamp/find', {
                params: {
                    search: query
                }
            }).then(({ data }) => {
                data.forEach(stamp => {
                    stamp.name = `[${stamp.id}] ${stamp.name}`;
                });
                this.stamps = data;
            });
        },

        formatDate(date) {
            const dateObject = new Date(date)
            return `${String(dateObject.getDate()).padStart(2, '0')}.${String(dateObject.getMonth() + 1).padStart(2, '0')}.${dateObject.getFullYear()}`
        },

        formatDevices(devices) {
            return devices.map(item => ({
                name: this.devicesOptions.filter(option => option.id === item.device_name)[0].text,
                serialNumber: item.device_serial_number
            }))
        }
    },
    mounted() {
        this.loadData()
        this.searchCompany();
        this.searchStamp();
        this.optionsPvs = this.points;
        this.optionsRoles = this.roles.filter((item) => {
            return ![3, 9, 6].includes(item.id)
        });
        this.allPermissions = this.all_permissions;

        this.columns.push({ key: 'selected', label:"", class: 'text-right' });
        this.fields.forEach(field =>{
            this.columns.push({
                'key': field.field,
                'label': field.name,
                'sortable': true,
                'thAttr': {
                    'data-toggle': 'tooltip',
                    'data-html': true,
                    'data-trigger': 'hover',
                    'data-placement': 'top',
                    title: field.content,
                }
            });
        });

        this.columns.push({ key: 'buttons', label: '#', class: 'text-right' });

        if (this.deleted) {
            const columns = [
                {
                    key:   'deleted_user.name',
                    label: 'Имя удалившего',
                },
                {
                    key:   'deleted_at',
                    label: 'Время удаления',
                }
            ];

            this.columns.push(...columns)
        }

        setInterval(this.loadConnectionStatus, 5000);
    },
    watch: {
        searchPermissions(val){
            if(val === ''){
                this.allPermissions = this.all_permissions
                return;
            }
            val = val.toLowerCase();
            this.allPermissions = this.all_permissions.filter((item) => {
                return item.guard_name.toLowerCase().match(val)
            })
        },
        enableModal(val) {
            if (!val) {
                this.resetModal()
            }
        },
        infoModalUser_roles() {
            this.fetchRoleData(this.infoModalUser_roles)
        },
        currentPage(){
            this.loadData();
        },
        selectedIds(newSelected) {
            this.selectedAll = newSelected.length === this.items.length;
        }
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
    background-color: #fbf1d3!important;
}

.row-check-expired {
    background-color: #fbd3d3 !important;
}

.table-card {
    max-height: 80vh;
    overflow: hidden;
}

.table-card > .card-body {
    overflow: scroll;
    padding: 0 !important;
    margin: 15px !important;
    overscroll-behavior: contain;
}
</style>
