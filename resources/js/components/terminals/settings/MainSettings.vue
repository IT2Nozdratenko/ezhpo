<script>
import Notify from "../../notify";

export default {
    props: {
        propsSettings: String,
    },
    data() {
        return {
            videoSizes: ['123'],
            settings: JSON.parse(this.propsSettings),
        }
    },
    mounted() {
        console.log(this.settings);
    },
    methods: {
        async save() {
            console.log(123)
            Notify.success("Настройки сохранены")
        },
    }
}
</script>

<template>
    <div class="admin__system">
        <div class="admin__system-card g1">
            <div class="admin__system-card__title">
                Основные настройки
            </div>
            <div class="admin__system-card__item">
                <span>Информация о водителе</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.driver_info">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Проверять номер телефона</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.check_phone_number">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Тип осмотра</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.type_ride">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Вопрос о сне</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.question_sleep">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>вопрос о самочувствии</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.question_helth">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Ручной режим</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.manual_mode">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Переход в начало</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.auto_start">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Задержка перехода <br> на начальный экран</span>
                <input min="10000" class="medium" type="number"
                       v-model="settings.system.delay_before_redirect_to_main_page">
            </div>
            <div class="admin__system-card__item">
                <span>Задержка перед <br> повторным прохождением <br>  при статусе "Не идентифицирован" <br>(в миллисекундах)</span>
                <input min="1000" class="medium" type="number" v-model="settings.system.delay_before_retry_inspection">
            </div>
        </div>

        <div class="admin__system-card g2">
            <div class="admin__system-card__title">
                Автономный режим
            </div>
            <div class="admin__system-card__item">
                <span>Максимальное количество <br> дней работы <br> в автономном режиме</span>
                <input min="0" class="medium" type="number" v-model="settings.system.delay_day_in_offline_mod">
            </div>
            <div class="admin__system-card__item">
                <span>Максимальное количество <br> осмотров <br> в автономном режиме</span>
                <input min="1" class="medium" type="number" v-model="settings.system.max_inspection_in_offline_mod">
            </div>
        </div>

        <div class="admin__system-card g3">
            <div class="admin__system-card__title">
                Алкометр
            </div>
            <div class="admin__system-card__item">
                <span>Пропуск</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.alcometer_skip">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>включен</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.alcometer_visible">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Быстрый режим</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.alcometer_fast">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Количественный замер <br> при положительном тесте</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.alcometer_retry">
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
        <div class="admin__system-card g4">
            <div class="admin__system-card__title">
                Камера
            </div>
            <div class="admin__system-card__item">
                <span>Качество</span>
                <select v-model="settings.system.camera_dimension">
                    <option v-for="size in videoSizes" :key="size" :value="size">{{ size }}</option>
                </select>
            </div>
            <div class="admin__system-card__item">
                <span>Видео</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.camera_video">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Фото</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.camera_photo">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Фото водителя</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.driver_photo">
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
        <div class="admin__system-card g5">
            <div class="admin__system-card__title">
                Принтер
            </div>
            <div class="admin__system-card__item">
                <span>Печать</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.printer_write">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>Количество</span>
                <input min="1" class="small" type="number" v-model="settings.system.print_count">
            </div>
            <div class="admin__system-card__item">
                <span>Печать маркировки осмотра</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.print_qr_check">
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
        <div class="admin__system-card g6">
            <div class="admin__system-card__title">
                Тонометр
            </div>
            <div class="admin__system-card__item">
                <span>Пропуск</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.tonometer_skip">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>включен</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.tonometer_visible">
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
        <div class="admin__system-card g7">
            <div class="admin__system-card__title">
                Термометр
            </div>
            <div class="admin__system-card__item">
                <span>Пропуск</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.thermometer_skip">
                    <div class="slider round"></div>
                </label>
            </div>
            <div class="admin__system-card__item">
                <span>включен</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.thermometer_visible">
                    <div class="slider round"></div>
                </label>
            </div>
        </div>

        <div class="admin__system-footer g8">
            <button @click="save" class="btn btn-default">Сохранить</button>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.g1 {
    grid-area: g1;
}

.g2 {
    grid-area: g2;
}

.g3 {
    grid-area: g3;
}

.g4 {
    grid-area: g4;
}

.g5 {
    grid-area: g5;
}

.g6 {
    grid-area: g6;
}

.g7 {
    grid-area: g7;
}

.g8 {
    grid-area: g8;
}

.admin {
    &__tabs {
        display: flex;
        -ms-flex-align: center;
        justify-content: center;
        gap: 5px;
    }

    &__tab {
        border: none;
        outline: none;
        font-size: 18px;
        padding: 10px 20px;
        background-color: rgba(#244673, 0.2);
        font-weight: 500;
        border-radius: 5px;
        cursor: pointer;
        transition: ease-in-out 0.2s;

        &.active {
            background-color: rgba(#244673, 1);
            color: #fff;

            &:hover {
                background-color: rgba(#244673, 1);
            }
        }

        &:hover {
            background-color: rgba(#244673, 0.4);
        }
    }

    &__testing {
        display: flex;
        flex-wrap: wrap;
        text-align: center;
        justify-content: center;
        margin-top: 20px;
        gap: 5px;
    }

    &__loading {
        width: 100%;
        margin-top: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 20px;
        font-size: 20px;
        font-weight: 500;
    }

    &__testing-result {
        width: 100%;
        margin-top: 50px;
        justify-content: center;
        align-items: center;

        img {
            width: 500px;
            border-radius: 15px;
        }
    }

    &__testing-timer {
        font-size: 30px;
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        font-weight: 500;

        span {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            width: 80px;
            height: 80px;
            background-color: rgba(0, 0, 0, 0.09);
            margin-top: 20px;
            font-size: 50px;
            border-radius: 10px;
        }
    }

    &__tonometer {
        justify-content: center;
        display: flex;
        margin-top: 20px;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 50px;
    }

    &__tonometer-card {
        background-color: rgba(#244673, 0.2);
        border-radius: 10px;
        font-weight: 500;

        p {
            padding: 5px 10px;
            margin: 0px;
            font-size: 16px;
            font-weight: 500;
        }
    }

    &__tonometer-card-title {
        font-size: 18px;
        font-weight: 500;
        background-color: rgba(#244673, 0.2);
        padding: 10px;
        margin-bottom: 10px;
    }

    &__tonometer-devices {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    &__tonometer-device {
        background-color: rgba(#244673, 0.2);
        border: none;
        padding: 10px;
        border-radius: 10px;
        width: 500px;
        font-size: 18px;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;

        &:hover {
            background-color: rgba(#244673, 0.4);
        }
    }

    &__testing-temp {
        font-size: 40px;
        font-weight: 500;
        background-color: rgba(#244673, 0.2);
        display: inline;
        padding: 10px;
        border-radius: 10px;
    }

    &__system {
        //margin: 0 auto;
        //width: 90%;
        //display: flex;
        //align-items: flex-start;
        //gap: 10px;
        //margin-top: 50px;
        //flex-wrap: wrap;
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 5px 5px;
        grid-auto-flow: row;
        grid-template-areas:
            "g1 g2 g3 g4"
            "g1 g5 g6 g7"
            "g8 g8 g8 g8";
    }

    &__system-card {
        background-color: rgba(#244673, 0.2);
        padding: 10px;
        border-radius: 10px;
        font-weight: 500;
        text-transform: uppercase;

        &__title {
            font-size: 16px;
            padding-bottom: 5px;
            margin-bottom: 5px;
            text-align: center;
            border-bottom: 1px solid rgba(#244673, 0.2);
            font-weight: 600;
            display: flex;
            justify-content: center;

            .connection-status {
                margin-left: auto;
            }
        }

        &__item {
            padding: 10px 0;
            display: flex;
            align-items: center;
            font-size: 15px;
            gap: 20px;
            justify-content: space-between;
        }

        input {
            border: none;
            outline: none;
            padding: 5px 0 5px 10px;
            font-weight: 500;
            border-radius: 5px;
            width: 100%;

            &.small {
                max-width: 35px;
            }

            &.medium {
                max-width: 75px;
            }
        }

        &.block {
            width: 50%;
        }

        select {
            padding: 5px 10px;
            border: none;
            outline: none;
            border-radius: 5px;
        }
    }

    &__system-footer {
        width: 100%;
        justify-content: center;
        align-items: center;
        display: flex;
        padding: 0 60px;
        margin-top: 10px;
    }

    &__testing-pressure {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    &__testing-pressure-item {
        display: flex;
        flex-direction: column;
        background-color: rgba(#244673, 0.2);
        border-radius: 10px;
        overflow: hidden;
        font-size: 18px;
        font-weight: 500;
        padding: 0 0 10px 0;
        min-width: 170px;

        span {
            background-color: rgba(#244673, 0.9);
            color: #fff;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
    }

    &__logo {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 70vh;
        flex-direction: column;
        gap: 30px;
    }

    .stamp {
        display: flex;
        flex-direction: column;
        padding: 0 4rem 10px 4rem;

        &__body {
            display: flex;
            flex-direction: row;

            .template {
                width: 450px;
                margin: 0 20px 0 20px;

                &__header {
                    text-align: center;
                }

                &__content {
                    color: black;
                    text-align: center;
                    font-size: 15px;
                    font-weight: 400;
                    font-family: Helvetica, Arial, sans-serif;
                    border: 1px solid;
                    height: 215px;
                    width: 450px;
                    margin-top: 45px;
                    padding: 10px 0 10px 0;

                    span.header {
                        font-size: 25px;
                    }

                    span.license {
                        font-size: 11px;
                    }
                }
            }

            .stampBox {
                width: 100%;
                display: flex;
                justify-content: center;
                flex-direction: column;

                &__header {
                    text-align: center;
                }

                &__content {
                    max-height: 55vh;
                    overflow-y: auto;
                }

                .stampItem {
                    //border: 1px solid;
                    border-radius: 11px;
                    text-align: center;
                    padding: 18px;
                    margin: 22px;
                    background: rgba(36, 70, 115, .2);
                }

                .selected {
                    background: #244673;
                    color: white;
                }
            }

            .content-block {

                &__table {
                    width: 100%;
                    //padding-left: 2vw;
                    //max-height: 551px;
                    max-height: 70vh;
                    overflow: auto;

                    table {
                        border-collapse: collapse;
                        width: 100%;
                    }

                    thead {
                        position: sticky;
                        top: 0;
                        background: white;
                        z-index: 1;

                        tr {
                            width: 100%;
                            border-bottom: 2px solid;
                            border-bottom-color: #e8dede;

                            .selected_stamp {
                                color: white;

                                th {
                                    background: rgba(255, 0, 0, 0.37);
                                }
                            }
                        }
                    }

                    tbody {
                        //height: 80px;
                        overflow-y: auto;

                        tr.selected {
                            background: #ff6565;
                        }

                        tr:nth-child(even) {
                            background-color: #f2f2f2;
                        }

                        tr:hover {
                            background-color: #ddd;
                        }

                        tr {
                            height: 101.5px;
                            font-weight: 500;
                            font-size: 15px;

                            td {
                                //.icon {
                                //  background-color: rgba(0, 0, 0, 0);
                                //  color: #244673;
                                //  font-size: 55px;
                                //  padding: 15px 30px 0px 30px;
                                //  max-width: 87px;
                                //  border: 1px #244673 solid;
                                //}
                            }
                        }
                    }

                    th {
                        padding-top: 12px;
                        padding-bottom: 12px;
                        text-align: left;
                    }
                }

                &__warning {
                    width: 100%;
                    text-align: center;
                }
            }
        }
    }

}

</style>
