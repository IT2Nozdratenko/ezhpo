<script>
import Notify from "../../notify";
import ButtonWithPending from "../../../v-components/ButtonWithPending.vue";

export default {
    components: {ButtonWithPending},
    props: {
        propsSettings: String,
    },
    data() {
        return {
            pending: false,
            settings: window.PAGE_SETUP.settings,
            terminals: window.PAGE_SETUP.terminals,
        }
    },
    mounted() {
        console.log(this.settings);
    },
    methods: {
        save() {
            this.pending = true;
            console.log(this.settings)
            axios.post(`/terminals/sync-settings`, {
                settings: this.settings,
                terminal_ids: this.terminals.map(({id}) => id),
            })
                .then(({status}) => {
                    if (status === 204) {
                        Notify.success("Настройки сохранены")
                    } else {
                        Notify.error('Ошибка!')
                    }
                })
                .catch(error => {
                    console.error(error)
                    Notify.error('Ошибка!')
                })
                .finally(() => {
                    this.pending = false;
                });
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
            <div class="admin__system-card__item">
                <span>Автоматическая отправка на сервер</span>
                <label class="switch">
                    <input type="checkbox" v-model="settings.system.auto_send_to_crm">
                    <div class="slider round"></div>
                </label>
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
        <div class="admin__system-card g8">
            <div class="admin__system-card__title">
                Настройки доступа
            </div>
            <div class="admin__system-card__item">
                <span>Пароль администратора</span>
                <input class="large" type="text" v-model="settings.main.password">
            </div>
            <div class="admin__system-card__item">
                <span>Пароль медика</span>
                <input class="large" type="text" v-model="settings.main.medic_password">
            </div>
        </div>

        <div class="admin__system-footer g9">
            <a href="/terminals" class="btn btn-default mr-2">Назад</a>

            <button-with-pending @click="save" class="btn btn-success" :pending="pending" description="Сохранить">
                <template v-slot:loading> Сохранение... </template>
                <template v-slot:default> Сохранить </template>
            </button-with-pending>
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

.g9 {
    grid-area: g9;
}

.admin {
    &__system {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 5px 5px;
        grid-auto-flow: row;
        grid-template-areas:
            "g1 g2 g3 g4"
            "g1 g5 g6 g7"
            "g8 g8 g8 g8"
            "g9 g9 g9 g9";
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

            &.large {
                max-width: 50%;
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
}
</style>
