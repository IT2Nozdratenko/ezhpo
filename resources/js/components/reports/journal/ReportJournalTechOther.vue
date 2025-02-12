<template>
    <div>
        <div v-if="data" class="card">
            <h5 class="card-header">Техосмотры за другие периоды</h5>
            <div class="card-body">
                <table id="reports-table-4" class="table table-responsive">
                    <thead>
                    <tr>
                        <th v-for="(item, name, index) in data" :key="index"
                            style="border-left: 1px solid #e9e9e9;background: #e9e9e9;" class="text-center" colspan="7">
                            {{ months[item.month - 1] }} {{ item.year }}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <td v-for="(item, name, index) in data" colspan="7" class="p-0">
                        <table class="w-100 table mr-2">
                            <thead>
                                <th class="text-center">Автомобиль</th>
                                <th class="text-center">Предрейсовый/Предсменный</th>
                                <th class="text-center">Послерейсовый/Послесменный</th>
                                <th class="text-center">Несогласованные осмотры</th>
                            </thead>

                            <tbody>
                            <tr v-for="(report, name, index) in item.reports">
                                <td class="text-center">
                                    {{ getStringMark(report) }}
                                </td>
                                <td class="text-center">
                                    {{ getTotal(report, 'Предрейсовый', 'Предсменный') }}
                                    <div class="font-weight-bold" v-if="getSum(report, 'Предрейсовый', 'Предсменный')">
                                        {{ getSum(report, 'Предрейсовый', 'Предсменный') }}₽
                                        <span class="text-red" v-if="getDiscount(report, 'Предрейсовый', 'Предсменный')">
                                            ({{ getDiscount(report, 'Предрейсовый', 'Предсменный') }}%)
                                        </span>
                                        <i v-if="isSync(report, 'Предрейсовый', 'Предсменный')" class="fa fa-refresh text-success"></i>
                                        <i v-else class="fa fa-refresh text-red"></i>
                                    </div>
                                </td>
                                <td class="text-center">
                                    {{ getTotal(report, 'Послерейсовый', 'Послесменный') }}

                                    <div class="font-weight-bold" v-if="getSum(item, 'Послерейсовый', 'Послесменный')">
                                        {{ getSum(report, 'Послерейсовый', 'Послесменный') }}₽
                                        <span class="text-red" v-if="getDiscount(report, 'Послерейсовый', 'Послесменный')">
                                            ({{ getDiscount(report, 'Послерейсовый', 'Послесменный') }}%)
                                        </span>
                                        <i v-if="isSync(report, 'Послерейсовый', 'Послесменный')" class="fa fa-refresh text-success"></i>
                                        <i v-else class="fa fa-refresh text-red"></i>
                                    </div>
                                </td>
                                <td class="text-center">{{ getTotal(report, 'is_dop') }}</td>
                            </tr>
                            <tr v-if="item.reports">
                                <td class="text-center">
                                    <b>Всего</b>
                                </td>
                                <td class="text-center">{{ getTotalAll(item.reports, 'Предрейсовый', 'Предсменный') }}</td>
                                <td class="text-center">{{ getTotalAll(item.reports, 'Послерейсовый', 'Послесменный') }}</td>
                                <td class="text-center">{{ getTotalAll(item.reports, 'is_dop') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-else-if="show" class="alert alert-secondary" role="alert">
            Техосмотры за другие периоды не найдены
        </div>
    </div>
</template>

<script>
import { months } from '../../const/local';
import {getTotalAll, getTotal, getSumAll, getSum, getDiscount, isSync} from "../../const/reportsAmount";

export default {
    name: "ReportJournalTechOther",
    data() {
        return {
            data: false,
            show: false,
            months,
        }
    },
    methods: {
        getStringMark(item) {
            let str = '';
            if (item.car_gos_number) {
                str += item.car_gos_number + ' ';

            } else {
                str += 'неизвестный автомобиль ';
            }

            if (item.type_auto) {
                str +=  `(${item.type_auto})`;
            }

            return str;
        },
        visible(data, show = true) {
            if (data.length === undefined || data.length > 0) {
                this.data = data;
            }

            this.show = show;
        },
        hide() {
            this.data = false;
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
