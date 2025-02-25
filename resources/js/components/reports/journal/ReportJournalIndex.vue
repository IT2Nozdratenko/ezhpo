<template>
    <div>
        <div class="card mb-4" style="overflow-x: inherit">
            <h5 class="card-header">Выбор информации</h5>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-lg-6">
                        <label class="mb-1" for="company">Компания</label>
                        <multiselect
                            :disabled="client"
                            v-model="company"
                            @search-change="searchCompany"
                            @select="(company) => company_id = company.hash_id"
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
                    </div>
                    <div class="form-group col-lg-2">
                        <label class="mb-1" for="date_from">Период:</label>
                        <input type="month" required ref="month" v-model="month"
                               id="month" class="form-control form-date" name="month">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-lg-12">
                        <button v-if="permissions.create" type="submit" @click="report" class="btn btn-info" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Сформировать отчет
                        </button>
                        <button v-if="permissions.export" type="submit" @click="exportData" class="btn btn-info" :disabled="loadingExport">
                            <span v-if="loadingExport" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Экспортировать
                        </button>
                        <a href="?" class="btn btn-danger">Сбросить</a>
                    </div>
                </div>

                <div class="row" v-if="showAlert">
                    <div class="col-md-12">
                        <div class="alert alert-danger w-100" role="alert">В данном месяце оказаны услуги за другие периоды</div>
                    </div>
                </div>
            </div>
        </div>

        <ReportJournalMedic
            ref="reportsMedic"
        />

        <ReportJournalTech
            ref="reportsTech"
        />

        <ReportJournalMedicOther
            ref="reportsMedicOther"
        />

        <ReportJournalTechOther
            ref="reportsTechOther"
        />

        <ReportJournalOther
            ref="reportsOther"
        />
    </div>
</template>

<script>
import ReportJournalMedic from "./ReportJournalMedic";
import ReportJournalTech from "./ReportJournalTech";
import ReportJournalTechOther from "./ReportJournalTechOther";
import ReportJournalMedicOther from "./ReportJournalMedicOther";
import ReportJournalOther from "./ReportJournalOther";
import axios from "axios";

export default {
    name: "ReportJournalIndex",
    props: ['default_company', 'client_company', 'permissions'],
    components: {
        ReportJournalMedic,
        ReportJournalTech,
        ReportJournalTechOther,
        ReportJournalMedicOther,
        ReportJournalOther
    },
    data() {
        return {
            loading: false,
            showAlert: false,
            loadingExport: false,
            company: null,
            client: false,
            companies: [],
            month: null,
            company_id: 0,
            axios: axios.create({
                baseURL: location.origin,
                headers: {
                    Authorization: 'Bearer ' + API_TOKEN
                }
            })
        }
    },
    mounted() {
        this.searchCompany();
        const now = new Date();
        const months = now.getMonth() > 9 ? now.getMonth() : '0' + now.getMonth();
        this.month = now.getFullYear() + '-'+ months;

        if (this.client_company) {
            this.companies.push(this.client_company);
            this.company = this.client_company;
            this.company_id = this.client_company.hash_id;
            this.client = true;
        } else if (this.default_company) {
            this.companies.push(this.default_company);
            this.company = this.default_company;
            this.company_id = this.default_company.hash_id;

            this.report();
        }
    },
    methods: {
        reset() {
            this.$refs.reportsMedic.hide();
            this.$refs.reportsTech.hide();
            this.$refs.reportsTechOther.hide();
            this.$refs.reportsMedicOther.hide();
            this.$refs.reportsOther.hide();
            this.showAlert = false;
        },
        report() {
            this.reset();
            this.loading = true;
            this.axios.get('/api/reports/journal', {
                params: {
                    company_id: this.company_id,
                    month: this.month
                }
            }).then(({ data }) => {
                this.$refs.reportsMedic.visible(data.medics);
                this.$refs.reportsTech.visible(data.techs);
                this.$refs.reportsTechOther.visible(data.techs_other);
                this.$refs.reportsMedicOther.visible(data.medics_other);
                this.$refs.reportsOther.visible(data.other);
                this.showAlert = this.isEmpty(data.techs_other) || this.isEmpty(data.medics_other);
            }).finally(() => {
                this.loading = false;
            });
        },
        exportData() {
            this.loadingExport = true;
            this.axios.get('/api/reports/journal/export', {
                params: {
                    company_id: this.company_id,
                    month: this.month
                },
                responseType: 'blob'
            }).then(({ data }) => {
                const url = window.URL.createObjectURL(new Blob([data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'export.xlsx'); //or any other extension
                document.body.appendChild(link);
                link.click();
            }).finally(() => {
                this.loadingExport = false;
            });
        },
        searchCompany(query = '') {
            this.axios.get('/api/companies/find', {
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
        isEmpty(value) {
            if (value && typeof value === 'object') {
                if (Array.isArray(value)) {
                    return value.length > 0;
                }
                return Object.keys(value).length > 0;
            }

            return false;
        }
    }
}
</script>

<style scoped>

</style>
