<script>
import LogsFilter from "./form-logs-filter.vue";
import LogsTable from "./form-logs-table.vue";
import swal from "sweetalert2";
import ModelSearcher from "./../searcher/model-searcher.vue";

export default {
    name: "form-logs-index",
    components: {ModelSearcher, LogsTable, LogsFilter},
    data() {
        return {
            pageSetup: window.PAGE_SETUP,
            filter: {
                users: [],
                models: [],
                id: null,
                uuid: null,
                actions: [],
                date_start: null,
                date_end: null,
            },
            items: [],
            page: 1,
            total: 1,
            limit: 100,
        }
    },
    methods: {
        async reload() {
            try {
                const {data} = await axios.post(this.pageSetup.tableDataUrl, {
                    limit: this.limit,
                    page: this.page,
                    filter: {
                        id: this.filter.id ? this.filter.id : null,
                        uuid: this.filter.uuid ? this.filter.uuid : null,
                        date_start: this.filter.date_start ? this.filter.date_start : null,
                        date_end: this.filter.date_end ? this.filter.date_end : null,
                        users: this.filter.users.map(item => item.id),
                        models: this.filter.models.map(item => item.id),
                        actions: this.filter.actions.map(item => item.id),
                    },
                })

                this.total = data.total
                this.items = data.data.map((item) => {
                    if (item.type === 'set_feedback') {
                        if (item.payload.feedback) {
                            item.payload = [
                                {
                                    name: 'feedback',
                                    oldValue: null,
                                    newValue: item.payload.feedback === 'positive' ? 'Хорошо' : 'Плохо'
                                }
                            ]
                        } else {
                            item.payload = []
                        }
                    }

                    item.payload = (item.payload ?? []).map((field) => {
                        field.name = this.pageSetup.fieldPromptsMap[item.model_type]?.[field.name] ?? field.name

                        return field
                    })

                    item.type = this.pageSetup.actionsMap[item.type];
                    item.model_type = this.pageSetup.modelsMap[item.model_type];

                    return item
                })
            } catch (e) {
                console.error(e.message);

                swal.fire({
                    title: 'Ошибка',
                    text: 'Ошибка при загрузке данных',
                    icon: 'error'
                });
            }
        },

        async handleApply() {
            await this.resetPageOrReload()
        },

        async handleReset() {
            this.filter = {
                users: [],
                models: [],
                id: null,
                uuid: null,
                actions: [],
                date_start: null,
                date_end: null,
            }
            await this.resetPageOrReload()
        },

        async resetPageOrReload() {
            if (this.page === 1) {
                await this.reload()
            } else {
                this.page = 1
            }
        }
    },
    async mounted() {
        await this.reload();
    },
    watch: {
        page() {
            this.reload()
        },

        limit() {
            this.resetPageOrReload()
        },
    }
}
</script>

<template>
    <div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <logs-filter
                        :filter.sync="filter"
                        :users-options="pageSetup.usersOption"
                        :models-options="pageSetup.modelsOption"
                        :actions-options="pageSetup.actionsOption"
                        style="margin-bottom: 25px"
                        @apply="handleApply"
                        @reset="handleReset"
                    />

                    <hr>

                    <logs-table
                        :items="items"
                        :page.sync="page"
                        :limit.sync="limit"
                        :total="total"
                        style="margin-top: 25px"
                    />
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <model-searcher/>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
