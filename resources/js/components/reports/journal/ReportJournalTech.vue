<template>
    <div>
        <div v-if="reports" class="card">
            <h5 class="card-header">Техосмотры за период</h5>
            <div class="card-body">
                <table id="reports-table-2" class="table table-striped table-sm">
                    <thead>
                    <tr>
                        <th width="100">ID</th>
                        <th width="250">Автомобиль</th>
                        <th class="text-center" width="150">Предрейсовый/Предсменный</th>
                        <th class="text-center" width="150">Послерейсовый/Послесменный</th>
                        <th class="text-center">Несогласованные осмотры</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="(item, name, index) in reports">
                        <tr v-if="name !== 'empty_id'" :key="index">
                            <td width="100">
                                {{ name }}
                            </td>

                            <td width="250">
                                <b>{{ getStringMark(item) }}</b>
                                <p>{{ item.pv_id || '' }}</p>
                            </td>

                            <td class="text-center" width="150">
                                {{ getTotal(item, 'Предрейсовый', 'Предсменный') }}

                                <div class="text-red font-weight-bold" v-if="getSum(item, 'Предрейсовый', 'Предсменный') == null">
                                    Услуги не указаны
                                </div>
                                <div class="font-weight-bold" v-else>
                                    {{ getSum(item, 'Предрейсовый', 'Предсменный') }}₽
                                    <span class="text-red" v-if="getDiscount(item, 'Предрейсовый', 'Предсменный')">
                                    ({{ getDiscount(item, 'Предрейсовый', 'Предсменный') }}%)
                                </span>
                                    <i v-if="isSync(item, 'Предрейсовый', 'Предсменный')" class="fa fa-refresh text-success"></i>
                                    <i v-else class="fa fa-refresh text-red"></i>
                                </div>
                            </td>

                            <td class="text-center" width="150">
                                {{ getTotal(item, 'Послерейсовый', 'Послесменный') }}

                                <div class="text-red font-weight-bold" v-if="getSum(item, 'Послерейсовый', 'Послесменный') == null">
                                    Услуги не указаны
                                </div>
                                <div class="font-weight-bold" v-else>
                                    {{ getSum(item, 'Послерейсовый', 'Послесменный') }}₽
                                    <span class="text-red" v-if="getDiscount(item, 'Послерейсовый', 'Послесменный')">
                                    ({{ getDiscount(item, 'Послерейсовый', 'Послесменный') }}%)
                                </span>
                                    <i v-if="isSync(item, 'Послерейсовый', 'Послесменный')" class="fa fa-refresh text-success"></i>
                                    <i v-else class="fa fa-refresh text-red"></i>
                                </div>

                            </td>

                            <td class="text-center" width="150">
                                {{ getTotal(item, 'is_dop') }}
                            </td>
                        </tr>

                        <tr v-else v-for="(subItem, car_type, subIndex) in item.type_auto" :key="index + '-' + subIndex">
                            <td width="100"></td>

                            <td width="250">
                                <b>{{ getStringMark(subItem) + ' (' + car_type + ')' }}</b>
                                <p>{{ subItem.pv_id || '' }}</p>
                            </td>

                            <td class="text-center" width="150">
                                {{ getTotal(subItem, 'Предрейсовый', 'Предсменный') }}

                                <div class="text-red font-weight-bold" v-if="getSum(subItem, 'Предрейсовый', 'Предсменный') == null">
                                    Услуги не указаны
                                </div>
                                <div class="font-weight-bold" v-else>
                                    {{ getSum(subItem, 'Предрейсовый', 'Предсменный') }}₽
                                    <span class="text-red" v-if="getDiscount(subItem, 'Предрейсовый', 'Предсменный')">
                                    ({{ getDiscount(subItem, 'Предрейсовый', 'Предсменный') }}%)
                                </span>
                                    <i v-if="isSync(subItem, 'Предрейсовый', 'Предсменный')" class="fa fa-refresh text-success"></i>
                                    <i v-else class="fa fa-refresh text-red"></i>
                                </div>
                            </td>

                            <td class="text-center" width="150">
                                {{ getTotal(subItem, 'Послерейсовый', 'Послесменный') }}

                                <div class="text-red font-weight-bold" v-if="getSum(subItem, 'Послерейсовый', 'Послесменный') == null">
                                    Услуги не указаны
                                </div>
                                <div class="font-weight-bold" v-else>
                                    {{ getSum(subItem, 'Послерейсовый', 'Послесменный') }}₽
                                    <span class="text-red" v-if="getDiscount(subItem, 'Послерейсовый', 'Послесменный')">
                                    ({{ getDiscount(subItem, 'Послерейсовый', 'Послесменный') }}%)
                                </span>
                                    <i v-if="isSync(subItem, 'Послерейсовый', 'Послесменный')" class="fa fa-refresh text-success"></i>
                                    <i v-else class="fa fa-refresh text-red"></i>
                                </div>

                            </td>

                            <td class="text-center" width="150">
                                {{ getTotal(subItem, 'is_dop') }}
                            </td>
                        </tr>
                    </template>

                    <tr v-if="reports">
                        <td width="100">
                            <b>Всего</b>
                        </td>

                        <td>
                            {{ Object.keys(reports).length + (reports.empty_id ? Object.keys(reports.empty_id.type_auto).length - 1 : 0) }}
                        </td>

                        <td class="text-center" width="150">
                            {{ getTotalAll(reports, 'Предрейсовый', 'Предсменный') }}

                            <div class="font-weight-bold" v-if="getSumAll(reports, 'Предрейсовый', 'Предсменный') != null">
                                {{ getSumAll(reports, 'Предрейсовый', 'Предсменный') }}₽
                            </div>
                        </td>

                        <td class="text-center" width="150">
                            {{ getTotalAll(reports, 'Послерейсовый', 'Послесменный') }}

                            <div class="font-weight-bold" v-if="getSumAll(reports, 'Послерейсовый', 'Послесменный') != null">
                                {{ getSumAll(reports, 'Послерейсовый', 'Послесменный') }}₽
                            </div>
                        </td>

                        <td class="text-center" width="150">
                            {{ getTotalAll(reports, 'is_dop') }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-else-if="show" class="alert alert-secondary" role="alert">
            Техосмотры за период не найдены
        </div>
    </div>
</template>

<script>
import { getTotalAll, getTotal, getSum, getSumAll, getDiscount, isSync } from "../../const/reportsAmount";

export default {
    name: "ReportJournalTech",
    data() {
        return {
            reports: false,
            show: false,
        }
    },
    methods: {
        log(item) {
            console.log(item)
        },
        getStringMark(item) {
            let str = '';
            if (item.car_gos_number) {
                str += item.car_gos_number + ' ';
            } else {
                str += 'неизвестный автомобиль ';
            }

            if (item.type_auto) {
                str += `(${item.type_auto})`;
            }

            return str;
        },
        visible(reports, show = true) {
            if (reports.length === undefined || reports.length > 0) {
                this.reports = reports;
            }
            this.show = show;
        },
        hide() {
            this.reports = false;
            this.show = false;
        },
        getTotalAll,
        getTotal,
        getSumAll,
        getSum,
        getDiscount,
        isSync
    }
}
</script>

<style scoped>

</style>
