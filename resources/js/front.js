import axios from 'axios'
import swal from 'sweetalert2'
import Swal2 from 'sweetalert2'
import {v4 as uidv4} from 'uuid'
import {ApiController} from "./components/ApiController";

require('./init-plugins')
require('chosen-js')
require('croppie')
require('suggestions-jquery')
require('./common/camera')
require('./common/findDuplicates')

$.fn.select2.amd.require(['select2/selection/search'], function (Search) {
    Search.prototype.searchRemoveChoice = function (decorated, item) {
        this.trigger('unselect', {
            data: item
        });

        this.$search.val('');
        this.handleSearch();
    };
});

function toggleAnketaCloneButton(state) {
    let button = $('#ANKETA_CLONE_TRIGGER');
    if (state === true) {
        return button.hide();
    }
    return button.show();
}

function initDatePicker(selector, conf = {}) {
    selector.flatpickr({
        mode: "multiple",
        dateFormat: "Y-m-d",
        locale: {
            weekdays: {
                shorthand: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
                longhand: [
                    "Воскресенье",
                    "Понедельник",
                    "Вторник",
                    "Среда",
                    "Четверг",
                    "Пятница",
                    "Суббота",
                ],
            },
            months: {
                shorthand: [
                    "Янв",
                    "Фев",
                    "Март",
                    "Апр",
                    "Май",
                    "Июнь",
                    "Июль",
                    "Авг",
                    "Сен",
                    "Окт",
                    "Ноя",
                    "Дек",
                ],
                longhand: [
                    "Январь",
                    "Февраль",
                    "Март",
                    "Апрель",
                    "Май",
                    "Июнь",
                    "Июль",
                    "Август",
                    "Сентябрь",
                    "Октябрь",
                    "Ноябрь",
                    "Декабрь",
                ],
            },
            firstDayOfWeek: 1,
            rangeSeparator: " — ",
            weekAbbreviation: "Нед.",
            scrollTitle: "Прокрутите для увеличения",
            toggleTitle: "Нажмите для переключения",
            amPM: ["ДП", "ПП"],
            yearAriaLabel: "Год",
            time_24hr: true,
        },
        ...conf
    });
}

$(document).ready(function () {
    const datesSelector = $("input[name='anketa[0][dates]']")
    if (datesSelector[0]) {
        initDatePicker(datesSelector[0])
    }

    $('div.form-group').each(function (i, el) {
        let requiredInput = $(el).has(':input[required]');
        requiredInput.addClass('required')
    });

    const Toast = swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', swal.stopTimer)
            toast.addEventListener('mouseleave', swal.resumeTimer)
        }
    })

    let loading = false
    let pprResult = false
    let anketa_type = $('#ANKETA_FORM_VIEW').data('anketa');

    switch (anketa_type) {
        case ('profile.ankets.tech') :
            let elPrC = $('#point_reys_control');
            if (elPrC.length > 0) {
                pprResult = (elPrC.val() === 'Пройден');
                toggleAnketaCloneButton(!pprResult);
                $(document).on('change', '#point_reys_control', function (event) {
                    pprResult = ($(event.target).val() === 'Пройден');
                    toggleAnketaCloneButton(!pprResult);
                });
            }
            break;
        case ('profile.ankets.medic') :
            let elMv = $('#med_view');
            if (elMv.length > 0) {
                pprResult = (elMv.val() === 'В норме');
                toggleAnketaCloneButton(!pprResult);
                $(document).on('change', '#med_view', function (event) {
                    pprResult = ($(event.target).val() === 'В норме');
                    toggleAnketaCloneButton(!pprResult);
                });
            }
            break;
    }


    $(document).on('click', '.reload-filters', function (event) {
        const btn = $(event.target);
        if (location.pathname.indexOf('home') > -1) {
            btn.disabled = true;
            btn.children('span.spinner').removeClass('d-none');
            $('#filter-group-2-tab').removeClass('active');
            $('#filter-group-1-tab').addClass('active');

            let path = location.origin + location.pathname;

            if (!path.endsWith('/')) {
                path += '/';
            }

            path += 'filters';
            $.get(path).done(response => {
                if (response) {
                    $('#filter-groupsContent').html(response)
                    LIBS.initChosen()
                }
            });
        }
    });

    $(document).on('input', '.select2-search__field', async function (event) {
        let search = event.target.value;
        let select = $(event.target).parents('.select2')?.parent()?.children('.filled-select2');

        if (select.length < 1) {
            const id = $(event.target).attr('aria-controls');
            select = $(`.select2-selection[aria-owns="${id}"]`).parents('.select2')?.parent()?.children('.filled-select2');
        }

        const model = select.attr('model');
        const concat = select.attr('field-concat') || false;
        const concatField = select.attr('field-concat-name') || 'hash_id';
        const field = select.attr('field');
        const key = select.attr('field-key');
        let trashed = false;
        if (select.attr('field-trashed')) {
            trashed = true;
        }

        if (!model) {
            return;
        }

        if (loading) {
            loading = false;
            return;
        }

        loading = true;
        await API_CONTROLLER.getFindModel({
            model,
            params: {
                search,
                field,
                key,
                trashed
            }
        }).then(({data}) => {
            data.forEach((element => {
                const value = element[key];
                let text = element[field];

                if (concat) {
                    text = '[' + element[concatField] + '] ' + text;
                }

                const exist = select.children('option');
                for (let i = 0; i < exist.length; i++) {
                    if (exist[i].value == value) {
                        return;
                    }
                }

                select.append($('<option>', {
                    value,
                    text
                }));
            }));
        });

        $(event.target).trigger('input');
    })

    const API_CONTROLLER = new ApiController(),
        API = API_CONTROLLER.client

    const LIBS = {
        docFields: window.DOC_FIELDS,

        initChosen() {
	    $('.filled-select2').each(function (index, element) {
                const select = $(element)

                const extraOptions = {}

                if (select.closest('#elements-modal-add').length || select.closest('#modalEditor').length) {
                    extraOptions.dropdownParent = $(this).parent()
                }

                select.select2({
                    placeholder: 'Выберите значение',
                    language: {
                        noResults: function (params) {
                            return "Совпадений не найдено"
                        }
                    },
                    allowClear: false,
                    ...extraOptions,
                });
            })

            $('.js-chosen').chosen({
                width: '100%',
                search_contains: true,
                no_results_text: 'Совпадений не найдено',
                placeholder_text_single: 'Выберите значение',
                placeholder_text_multiple: 'Выберите значения',
                reset_search_field_on_update: false,
            });
        },

        initDoc() {
            $('.Doc textarea').each(function () {
                let name = this.name

                $(this).click(async function () {
                    if (LIBS.docFields[name] && this.classList.contains('open-modal')) {
                        let df = LIBS.docFields[name]

                        const {value: dataField} = await swal.fire({
                            input: 'select',
                            inputOptions: df,
                            inputPlaceholder: 'Выберите опцию',
                            showCancelButton: true,
                            confirmButtonText: "Применить",
                            cancelButtonText: "Отмена",
                        })

                        if (dataField) {
                            this.value = df[dataField]
                        }
                    }
                })
            })

            /**
             * Сохраняем документ
             */
            $('#DOC_FORM').each(function () {
                let protokol = $(this).data('protokol')

                if (protokol) {
                    for (let i in protokol) {
                        if (i !== 'id') {
                            let inp = $(this).find(`*[name="${i}"]`)

                            inp.val(protokol[i])
                        }
                    }
                }

                $(this).submit(function (e) {
                    e.preventDefault()

                    let data = {}

                    let id = this.id.value;
                    let field = this.field?.value || 'protokol';

                    $(this).find('input,textarea,select').each(function () {
                        if (this.name) {
                            data[this.name] = this.value
                        }
                    })

                    API_CONTROLLER.saveDoc(field, data).then(response => {
                        location.reload();
                    })
                })
            })
        },

        initAll() {
            LIBS.initDoc()
            LIBS.initChosen()
        }
    }

    function initCroppies() {
        let Croppies = {}

        $('.croppie-demo').each(function () {
            let id = $(this).data('croppieId')

            Croppies[id] = $(this).croppie({
                enableOrientation: true,
                viewport: {
                    width: 170,
                    height: 170,
                    type: 'circle' // or 'square'
                },
                boundary: {
                    width: 200,
                    height: 200
                }
            })
        })

        $('[id*="croppie-input"]').on('change', function () {
            let reader = new FileReader(), croppId = this.id.replace('croppie-input', '')

            reader.onload = function (e) {
                $('#croppie-block' + croppId).show()

                Croppies[croppId].croppie('bind', {
                    url: e.target.result
                });
            }

            reader.readAsDataURL(this.files[0]);

            this.files = null
            this.value = ''
        });

        $('.croppie-save').click(function () {
            let id = $(this).data('croppieId')

            Croppies[id].croppie('result', {
                type: 'canvas',
                size: 'viewport'
            }).then(image => {
                //$('#croppie-block' + id).hide()

                // success message
                swal.fire({
                    title: 'Изображение обрезано',
                    icon: 'info'
                });

                $(`#croppie-result-base64${id}`).val(image)
                $('#croppie-block' + id).hide()
            })
        })

        $('.croppie-delete').click(function () {
            let id = $(this).data('croppieId')
            let input = $(`#croppie-input${id}`);

            if (input && id) {
                input[0].files = null;
                input[0].value = '';

                $(`#croppie-result-base64${id}`).val('')
                $('#croppie-block' + id).hide()
            }
        })
    }

    initCroppies()

    // Подгрузка в полей в Журналах: CHOSEN
    if (location.pathname.indexOf('home') > -1) {
        let path = location.origin + location.pathname;

        if (!path.endsWith('/')) {
            path += '/';
        }

        path += 'filters' + location.search;

        $.get(path).done(response => {
            if (response) {
                $('#filter-groupsContent').html(response)
                LIBS.initChosen()
            }
        });
    }

    const showContractsFormCardDBItem = (contracts) => {
        let block = '';

        if (contracts !== undefined) {
            block += `<p class="text-small m-0">Договор:</p>`;
        }

        contracts = contracts ?? []

        if (contracts.length !== 0) {
            block += `<ul class="list-group my-2">`;

            contracts.map((contract) => {
                block += `<li style="padding: 0;" class=" text-small list-group-item list-group-item-action list-group-item-success">
                                    <ul class="list-group">
                                        <b>${contract.name_with_dates}</b>`;

                (contract.services ?? []).map((service) => {
                    block += `<li style="padding: 0; font-size: 0.8em" class="list-group-item text-small list-group-item-action list-group-item-secondary">${service.name}</li>`;
                })

                block += `</ul></li>`;
            })

            block += `</ul>`;
        } else {
            block += `<p class="text-small">-- Отсутствует --</p>`;
        }

        return block;
    }

    const showInputFormCardDBItem = async (fieldName, fieldValue, fvItem, model, isBlocked) => {
        let field = '',
            clearInputBtn = '',
            fId = uidv4(),
            inputClass = `${model}_input`,
            required = Number(fvItem['noRequired'] ?? 0) !== 1 ? 'required' : '';

        if (fvItem['type'] === 'select') {
            await API_CONTROLLER.getFieldHTML({
                field: fieldName,
                model,
                default_value: encodeURIComponent(fieldValue)
            }).then(response => {
                field = response.data
            })
        } else if (['note', 'comment', 'name'].includes(fieldName)) {
            field = `<textarea id="${fId}" ${isBlocked} data-model="${model}" class="ANKETAS_TEXTAREA form-control" name="${fieldName}" ${required}>${(fieldValue ?? '').trim()}</textarea>`
        } else if (fieldName === 'photo' && fieldValue) {
            field = `<img alt="photo" src="/storage/${fieldValue}" width="100%"/>`
        } else {
            field = `<input id="${fId}" ${isBlocked} data-model="${model}" class="form-control" type="${fvItem['type']}" value='${fieldValue ?? ''}' name="${fieldName}" ${required}/>`
        }

        if (isBlocked === '' && fieldName !== 'photo') {
            clearInputBtn = `<a href="" style="font-size: 10px; color: #c2c2c2;" onclick="$('#${fId}').val('').trigger('change'); return false;"><i class="fa fa-trash"></i> Очистить</a>`
        }

        let labelStyle = ''
        const importantValueLabelStyle = "color: red; font-weight: bold;";
        if (fieldName === 'dismissed' && fieldValue.toUpperCase() === 'ДА') {
            labelStyle = importantValueLabelStyle
        }
        if (fieldName === 'group_risk' && (fieldValue ?? '').trim() !== '') {
            labelStyle = importantValueLabelStyle
        }

        return `
            <p style="${labelStyle}"
            data-field-card="${model}_${fieldName}"
            class="text-small m-0">
            ${fvItem.label}:
                <br/>
                ${clearInputBtn}
                <div class="form-group ${inputClass}">
                    ${field}
                </div>
            </p>`
    }

    /**
     * Показываем данные в сущностях в карточках Анкеты
     * @param model Название модели
     * @param data Значения инпутов
     * @param fieldsValues Описание свойств инпутов
     * @param fullData Дополнительная информация для отрисовки форм
     */
    const showAnketsCardDBitemData = async (model, data, fieldsValues, fullData) => {
        if (!data) {
            return;
        }

        let dbItemId = `CARD_${model.toUpperCase()}`,
            itemForm = `<form id="form_${model}">`,
            inputClass = `${model}_input`;

        const dbItem = $(`#${dbItemId}`);

        dbItem.html('<b class="text-info">Загружаем данные...</b>')

        let formIsFull = true;

        /**
         * Вставляем поля
         */
        for (let fieldName in data) {
            let fvItem = fieldsValues[fieldName];

            if (!fvItem) continue;

            if (['id', 'hash_id', 'contract_id', 'contract', 'contracts', 'products_id'].includes(fieldName)) continue;

            const isBlocked = (fullData.blockedFields ?? []).includes(fieldName) ? 'disabled' : '';

            const fieldValue = data[fieldName];

            try {
                const input = await showInputFormCardDBItem(fieldName, fieldValue, fvItem, model, isBlocked);

                itemForm += input;
            } catch (e) {
                itemForm += `<p>Ошибка получения поля ${fieldName}!</p>`;

                formIsFull = false;

                console.error(e.message)
            }

            /**
             * Добавление списка договоров с услугами для просмотра,
             * условие определяет - после каких полей выводить инфо
             */
            if ((fieldName === 'company_id' && model === 'Driver') || (fieldName === 'town_id' && model === 'Company')) {
                let contracts = undefined;

                if (data.contract) {
                    contracts = [data.contract]
                } else if (data.contracts) {
                    contracts = data.contracts
                }

                itemForm += showContractsFormCardDBItem(contracts)
            }
        }

        if (formIsFull) {
            itemForm += `<button type="submit" class="btn btn-sm btn-success">Сохранить</button></form>`
        }

        dbItem.html(itemForm)

        LIBS.initChosen()

        $(`#form_${model}`).submit(function (e) {
            e.preventDefault()

            if (!confirm('Сохранить?')) {
                return;
            }

            $(this).find(`.${inputClass} input, .${inputClass} select, .${inputClass} textarea`).each(function () {
                let val = this.value, name = this.name;

                if (!name) {
                    return;
                }

                API_CONTROLLER.updateModelProperty({
                    item_model: model,
                    item_field: name,
                    item_id: data['id'],
                    new_value: val
                })
            })
        })

        $('.ANKETAS_TEXTAREA').each(function () {
            this.style.height = this.scrollHeight + 'px';
        })

        /**
         * Контроль дат (DDATE)
         */
        let redDates = fullData.redDates
        if (redDates && typeof redDates === "object" && !Array.isArray(redDates)) {
            for (let i in redDates) {
                $(`*[data-field-card="${model}_${i}"]`).css({
                    'color': 'red',
                    'font-weight': 'bold'
                })
            }
        }
    }

    window.changeFormRequire = (element, className) => {
        $(element).closest('.cloning').find(`.${className}`).prop('required', !(element.value.length > 0));
    };

    window.loadPreviousOdometer = async () => {
        const previousOdometerContainer = $(event.target).closest('.input-group')
        const formContainer = previousOdometerContainer.closest('.cloning')
        const previousOdometerInput = previousOdometerContainer.find('input')[0]

        if (previousOdometerInput === undefined) {
            console.error('Не найдено поле для предыдущего показания одометра')
            return;
        }

        try {
            const formPrefix = previousOdometerInput.name.match(/\[([0-9]+)\]/)[0]

            const carIdInput = formContainer.find(`input[name="anketa${formPrefix}[car_id]"]`)[0]
            if (carIdInput === undefined) {
                previousOdometerInput.value = 'Не найдено поле ID автомобиля'
                return
            }
            const carIdValue = carIdInput.value
            if (carIdValue.trim().length === 0) {
                previousOdometerInput.value = 'Не указано ID автомобиля'
                return;
            }

            const dateInput = formContainer.find(`input[name="anketa${formPrefix}[date]"]`)[0]
            if (dateInput === undefined) {
                previousOdometerInput.value = 'Не найдено поле Дата и время осмотра'
                return
            }
            const dateValue = dateInput.value
            if (dateValue.trim().length === 0) {
                previousOdometerInput.value = 'Не указана Дата и время осмотра'
                return;
            }

            previousOdometerInput.value = 'Получение данных...'

            $.ajax({
                type: "POST",
                url: `/api/get-previous-odometer`,
                headers: {'Authorization': 'Bearer ' + API_TOKEN},
                data: {
                    car_id: carIdValue,
                    date: dateValue
                },
                success: (response) => {
                    previousOdometerInput.value = response.message
                },
                error: (jqXHR) => {
                    if (jqXHR.status === 422) {
                        previousOdometerInput.value = Object.values(jqXHR.responseJSON.errors)[0][0]
                    } else {
                        previousOdometerInput.value = 'Ошибка получения данных!'
                    }
                }
            });
        } catch (e) {
            previousOdometerInput.value = 'Ошибка!'
            console.error(e.message)
        }
    }

    // Проверка свойства по модели на бэкенда
    window.checkInputProp = async (prop = '0', model = '0', val = '0', label, parent, is_dop) => {
        val = `${val}`.trim()
        if ((prop === 'hash_id') && (val.length < 6)) {
            return;
        }

        let PARENT_ELEM = parent;
        if (!parent) {
            PARENT_ELEM = $(event.target).parent();
        }

        if (!is_dop) {
            if (!val) {
                return;
            }

            let answer = await $.ajax({
                url: `/api/check-prop-one/${prop}/${model}/${val}`,
                headers: {'Authorization': 'Bearer ' + API_TOKEN},
                success: (data) => {
                    let element = PARENT_ELEM.find('.app-checker-prop')
                    if (data.status) {
                        element.removeClass('text-danger').addClass('text-success').text(data.name);
                        PARENT_ELEM.closest('#ANKETA_FORM').find('.btn-success').prop('disabled', false);
                    } else {
                        element.removeClass('text-success').addClass('text-danger').text(`Не найдено`);
                        parent.prevObject.attr('company', null);
                        PARENT_ELEM.closest('#ANKETA_FORM').find('.btn-success').prop('disabled', true);
                    }
                }
            })

            if (!answer.status) {
                return;
            }
        }

        const setSelectValue = function (select, value, text = null) {
            if (text === null) {
                text = value
            }

            if (select.length < 1) {
                return
            }

            const exist = select.children('option').filter((id, element) => element.value === value);

            if (exist.length < 1) {
                select.append($('<option>', {
                    value,
                    text
                }));
            }

            select.val(value).trigger("change");
        }

        $.ajax({
            url: `/api/check-prop/${prop}/${model}/${val}?dateAnketa=${$('[name="anketa[0][date]"]').val()}`,
            headers: {'Authorization': 'Bearer ' + API_TOKEN},
            success: (data) => {
                const PROP_HAS_EXISTS = data.data.exists
                const DATA = data.data.message;

                const carModel = model === 'Car'
                const driverModel = model === 'Driver'
                const carTypeAutoValue = DATA?.type_auto
                if (carModel && carTypeAutoValue) {
                    const form = parent.closest('.cloning');
                    setSelectValue(form.find('select.car_type_auto'), carTypeAutoValue)
                }

                const companyHashIdValue = DATA?.company_hash_id
                if ((driverModel || carModel) && companyHashIdValue) {
                    const form = parent.closest('#ANKETA_FORM');
                    form.find('input[name="company_id"]').val(companyHashIdValue);
                    setSelectValue(form.find('select[name="company_id"]'), companyHashIdValue, DATA?.company_name)
                }

                const companyNameValue = DATA?.company_name
                if ((driverModel || carModel) && companyNameValue) {
                    const form = parent.closest('#ANKETA_FORM');
                    setSelectValue(form.find('select[name="company_name"]'), companyNameValue)
                }

                showAnketsCardDBitemData(model, DATA, data.data.fieldsValues, data.data)

                if (PARENT_ELEM.length) {
                    const APP_CHECKER_PARENT = PARENT_ELEM.find('.app-checker-prop')

                    if (PROP_HAS_EXISTS) {
                        APP_CHECKER_PARENT.removeClass('text-danger').addClass('text-success').text(DATA[label])
                    } else {
                        APP_CHECKER_PARENT.removeClass('text-success').addClass('text-danger').text(`Не найдено`)
                    }
                }

                if (DATA && !!DATA.company_id) {
                    const form = $('#ANKETA_FORM');
                    const companyIdInput = form.find('input[name="company_id"]')

                    companyIdInput.parent()
                        .find('.app-checker-prop')
                        .removeClass('text-danger')
                        .addClass('text-success')
                        .text(DATA.company_name);

                    PARENT_ELEM.closest('#ANKETA_FORM')
                        .find('.btn-success')
                        .prop('disabled', false);

                    checkInputProp('id', 'Company', DATA.company_id, 'name', companyIdInput.parent().parent())
                }

                if (driverModel && DATA && DATA.company_hash_id) {
                    parent.find('input').attr('company', DATA.company_hash_id);
                    const driverInput = parent.closest('#ANKETA_FORM').find('.car-input');
                    driverInput.each((id, input) => {
                        const attr = $(input).attr('company');
                        if (attr && attr !== DATA.company_hash_id) {
                            const mess = $(input).closest('article')?.find('.app-checker-prop');
                            if (mess?.find('#company')?.length < 1) {
                                mess.append(
                                    `<br><span id="company" class="text-danger">Компания автомобиля не соответствует компании водителя</span>`
                                );
                            }
                        } else if (attr && attr === DATA.company_hash_id) {
                            const mess = $(input).closest('article')?.find('.app-checker-prop');
                            if (mess?.find('#company')?.length > 0) {
                                mess.find('#company').remove();
                            }
                        }
                    });
                }

                if (carModel && DATA && DATA.company_hash_id) {
                    const driverInput = parent.closest('#ANKETA_FORM').find('input[name="driver_id"]');
                    parent.find('input').attr('company', DATA.company_hash_id);

                    if (driverInput) {
                        const driverCompany = driverInput.attr('company');
                        if (driverCompany && driverCompany !== DATA.company_hash_id) {
                            parent.find('.app-checker-prop').append(
                                `<br><span id="company" class="text-danger">Компания автомобиля не соответствует компании водителя</span>`
                            );
                        }
                    }
                }
            }
        });
    }


    window.addFieldToHistory = (value, field) => {
        API.post('/api/field-history', {
            value, field
        })
    }

    // Открытие/закрытие элементов
    $('[data-toggle-show]').click(function (e) {
        e.preventDefault()

        let $this = $(this), el = $($this.data('toggle-show')), title = $this.find('.toggle-title'),
            hiddenClass = 'toggle-hidden', titleData = 'Показать'

        if (el.length && title.length) {

            titleData = (el.hasClass(hiddenClass)) ? 'Скрыть' : titleData

            title.text(titleData)
            el.toggleClass(hiddenClass);
        }
    })

    $('.field').each(function () {
        let $t = $(this),
            $i = $t.find('> i'),
            $input = $t.find('> input')

        if ($t.hasClass('field--password')) {
            $i.click(function () {
                if ($(this).hasClass('fa-eye')) {
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash')
                    $input.attr('type', 'password')
                } else {
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye')
                    $input.attr('type', 'text')
                }
            })
        }
    })

    // ЭКШЕНЫ
    $('*[class*="ACTION_"]').click(function (e) {
        e.preventDefault();

        let confirms = {
            'ACTION_DELETE': 'Точно хотите удалить?'
        }

        this.classList.forEach(cls => {
            if (confirms[cls])
                if (confirm(confirms[cls]))
                    location.href = this.href;
        })
    })

    $('.TRIGGER_CLICK').trigger('click');

    // Клонируем анкету
    let count_anketa = 1;

    $('#ANKETA_CLONE_TRIGGER').click(e => {
        const CLONE_ID = 'cloning'
        if (count_anketa + 1 === 32) {
            swal.fire({
                title: 'Нельзя добавлять более 31 осмотров',
                icon: 'warning'
            });

            return
        }

        let clone_first = $(`#${CLONE_ID}-first`), clone_to = $(`#${CLONE_ID}-append`)
        let clone = clone_first.clone()
        let randId = 'ANKETA_DOUBLE_' + count_anketa

        if (clone_to.find('.cloning-clone').length) {
            clone = clone_to.find('.cloning-clone').last().clone()
        }

        // Выставляем параметры
        clone.removeAttr('id').addClass('cloning-clone')

        clone.attr('id', randId)

        // Создаем клон
        clone_to.append(clone)

        clone.find('input,select').each(function () {
            this.name = this.name.replace('anketa[' + (count_anketa - 1) + ']', 'anketa[' + count_anketa + ']');
            $(this).trigger('input');
        })

        const datePickerElement = clone.find(".date-range");
        if (datePickerElement[0]) {
            initDatePicker(clone.find(".date-range")[0])
        }

        const deleteBtn = '<a href="" onclick="' + randId + '.remove(); return false;" class="text-danger">Удалить</a>';

        clone.find('.anketa-delete').html(deleteBtn)

        if (! clone.find('.duplicate-indicator').hasClass('d-none')) {
            window.duplicates['anketa[' + count_anketa + ']' + '[date]'] = true
        }

        count_anketa++
    })

    // Отправка данных с форма по CTRL + ENTER
    $('.anketa-fields input, .anketa-fields textarea').keydown(e => {
        if (e.ctrlKey & e.keyCode == 13) {
            $('.anketa-fields form').trigger('submit');
        }
    })


    /**
     * API:
     * ИЗМЕНЕНИЕ ПОЛЕЙ НА BACKEND
     */
    $('.JS_CHANGE_FIELD_MODEL').click(function (e) {
        e.preventDefault()

        let url = this.href, field = $(this).data('field')

        field = $(field)

        if (field.length) {
            let new_value = field.val()

            API.put(url.replace(location.origin, ''), {new_value}).then(response => {
                const data = response.data

                if (data.success)
                    alert('Данные успешно обновлены!')
            })
        }
    })

    // ------------------------------------------------------- //
    // Card Close
    // ------------------------------------------------------ //
    $('.card-close a.remove').on('click', function (e) {
        e.preventDefault();
        $(this).parents('.card').fadeOut();
    });

    // ------------------------------------------------------- //
    // Tooltips init
    // ------------------------------------------------------ //

    $('[data-toggle="tooltip"]').tooltip()
    $("[rel='tooltip'], .tooltip").tooltip();

    // ------------------------------------------------------- //
    // Adding fade effect to dropdowns
    // ------------------------------------------------------ //
    $('.dropdown').on('show.bs.dropdown', function () {
        $(this).find('.dropdown-menu').first().stop(true, true).fadeIn();
    });
    $('.dropdown').on('hide.bs.dropdown', function () {
        $(this).find('.dropdown-menu').first().stop(true, true).fadeOut();
    });

    // ------------------------------------------------------- //
    // Sidebar Functionality
    // ------------------------------------------------------ //
    $('#toggle-btn').on('click', function (e) {
        e.preventDefault();
        $(this).toggleClass('active');

        $('.side-navbar').toggleClass('shrinked');
        $('.content-inner').toggleClass('active');
        $(document).trigger('sidebarChanged');

        if ($(window).outerWidth() > 1183) {
            if ($('#toggle-btn').hasClass('active')) {
                $('.navbar-header .brand-small').hide();
                $('.navbar-header .brand-big').show();
            } else {
                $('.navbar-header .brand-small').show();
                $('.navbar-header .brand-big').hide();
            }
        }

        if ($(window).outerWidth() < 1183) {
            $('.navbar-header .brand-small').show();
        }
    })

    // Меню (dropdown)
    $('[data-btn-collapse]').click(function (e) {
        e.preventDefault()

        let t = $(this), menu = $(t.data('btn-collapse')), clsCollapse = 'collapse'

        t.attr('aria-expanded', menu.hasClass(clsCollapse))

        if (menu.length) {
            menu.toggleClass(clsCollapse)
        }
    })

    /**
     * Elements FORMS (edit, add)
     * Check Requireds / Validate
     */
    function checkElementsFormDataRequireds(form, event = null) {
        let $form = $(form),
            requireds = $form.find('[required]'), errors = []

        requireds.each(function () {
            let val = $(this).val(), name = $(this).data('label')

            if (typeof val === "object") {
                if (val.length === 0) {
                    errors.push(`Поле "${name}" не заполнено`)
                }
            } else {
                if (!val)
                    errors.push(`Поле "${name}" не заполнено`)
            }
        })

        if (errors.length) {
            if (event)
                event.preventDefault()

            Toast.fire({
                icon: 'error',
                text: errors[0]
            })

            return
        }
    }

    function initElementsModalCheckFields() {
        $('[id*="elements-modal"] form, [id="elements-modal-add"] form').submit(function (e) {
            checkElementsFormDataRequireds(this, e)
        })

        $('[id*="elements-modal"] button[type="submit"], [id="elements-modal-add"] button[type="submit"]').click(function (e) {
            let $form = $(this).parents('form')

            if ($form.length)
                checkElementsFormDataRequireds($form)
        })
    }

    initElementsModalCheckFields()

    /**
     * АВТО-ОТПРАВКА ФОРМ
     */
    $('.API_FORM_SEND').submit(async function (e) {
        e.preventDefault()
        document.querySelector('#page-preloader').classList.remove('hide');
        let data = $(this).serialize(), action = this.action, method = this.method,
            formData = new FormData(this), successTitle = $(this).data('successTitle')

        successTitle = successTitle ? successTitle : 'Успешно!'

        await axios[method](action, data, {
            headers: {
                'Authorization': 'Bearer ' + API_TOKEN
            }
        }).then(response => {
            swal.fire({
                title: successTitle,
                icon: 'info'
            });
        }).catch(error => {
            swal.fire({
                title: "Ошибка!",
                text: "Обратитесь к администратору!",
                icon: 'error'
            });
        })

        document.querySelector('#page-preloader').classList.add('hide');
    })

    // Проверяем ссылки в меню
    $('a[data-btn-collapse] + ul a').each(function () {
        let $href = this.href.replace(location.origin, '')

        if (location.pathname.indexOf($href) > -1) {
            let $parent = $(this).parent().parent()

            $parent.prev().trigger('click')
        }
    })

    // Таймер
    $('.App-Timer[data-date]').each(function () {
        let $t = $(this),
            timerDateStart = new Date($t.data('date'))

        function showDiff(date1, date2) {
            var diff = (date2 - date1) / 1000
            diff = Math.abs(Math.floor(diff))

            var days = Math.floor(diff / (24 * 60 * 60))
            var leftSec = diff - days * 24 * 60 * 60
            var hrs = Math.floor(leftSec / (60 * 60))
            var leftSec = leftSec - hrs * 60 * 60

            var min = Math.floor(leftSec / (60))
            var leftSec = leftSec - min * 60

            $t.html("<b class='text-red'>" + (days < 10 ? '0' + days : days) + ":" + (hrs < 10 ? '0' + hrs : hrs) + ":" + (min < 10 ? '0' + min : min) + ":" + (leftSec < 10 ? '0' + leftSec : leftSec) + "</b>")
        }

        setInterval(() => {
            showDiff(timerDateStart, new Date())
        }, 1000)
    })

    // ------------------------------------------------------- //
    // Material Inputs
    // ------------------------------------------------------ //

    const materialInputs = $('input.input-material');

    // activate labels for prefilled values
    materialInputs.filter(function () {
        return $(this).val() !== "";
    }).siblings('.label-material').addClass('active');

    // move label on focus
    materialInputs.on('focus', function () {
        $(this).siblings('.label-material').addClass('active');
    });

    // remove/keep label on blur
    materialInputs.on('blur', function () {
        $(this).siblings('.label-material').removeClass('active');

        if ($(this).val() !== '') {
            $(this).siblings('.label-material').addClass('active');
        } else {
            $(this).siblings('.label-material').removeClass('active');
        }
    });

    // ------------------------------------------------------- //
    // Footer
    // ------------------------------------------------------ //
    function adjustFooter() {
        $('.content-inner').css('padding-bottom', $('.main-footer').outerHeight() + 'px');
    }

    $(document).on('sidebarChanged', function () {
        adjustFooter();
    });

    $(window).on('resize', function () {
        adjustFooter();
    })

    // ------------------------------------------------------- //
    // External links to new window
    // ------------------------------------------------------ //
    $('.external').on('click', function (e) {
        e.preventDefault();
        window.open($(this).attr("href"));
    })

    // ADAPTIVE
    if (screen.width <= 900) {
        $('#toggle-btn').trigger('click')
    }

    $('input[type="checkbox"][data-connect-field]').each(function () {
        let connectField = $(this).data('connectField')
        connectField = $(`*[name="${connectField}"]`)

        let triggerField = () => {
            if (this.checked) {
                connectField.removeAttr('disabled')
            } else {
                connectField.attr('disabled', 'disabled')
                connectField.removeAttr('checked')
            }
        }

        $(this).change(function () {
            triggerField()
        })

        triggerField()
    })

    function initCompanyNameSuggestion(companyOfficialNameInput, innInput, kppInput, nameInput, ogrnInput, addressInput) {
        if (!companyOfficialNameInput) return;

        if (!innInput) return;

        companyOfficialNameInput.suggestions({
            token: window.DADATA_TOKEN,
            type: "PARTY",
            count: 20,
            /* Вызывается, когда пользователь выбирает одну из подсказок */
            onSelect: function (suggestion) {
                if (!suggestion.data) {
                    return
                }

                const {name,inn,kpp,ogrn,address} = suggestion.data

                innInput.val(inn)
                kppInput.val(kpp)
                nameInput.val(name.short_with_opf)
                ogrnInput.val(ogrn)
                addressInput.val(address.unrestricted_value)
            },
            /* Определяет текст, подставляемый в инпут при выборе одной из подсказок */
            formatSelected: function (suggestion) {
                return suggestion?.data?.name?.full_with_opf
            }
        });
    }

    initCompanyNameSuggestion(
        $('#elements-modal-add input[data-field="Company_official_name"]'),
        $('#elements-modal-add input[name="inn"]'),
        $('#elements-modal-add input[name="kpp"]'),
        $('#elements-modal-add input[name="name"]'),
        $('#elements-modal-add input[name="ogrn"]'),
        $('#elements-modal-add input[name="address"]'),
    )

    $('.header #toggle-btn').each(function () {
        let localStatusSidebar = () => {
                return localStorage.getItem('sidebar')
            },
            localStatusSet = status => {
                localStorage.setItem('sidebar', status)
            }

        if (localStatusSidebar() === "0") {
            $(this).trigger('click')
        }

        $(this).click(function () {
            if ($(this).hasClass('active')) {
                localStatusSet(1)
            } else {
                localStatusSet(0)
            }
        })
    })

    LIBS.initAll()
    $('.MASK_ID_ELEM').trigger('input')

    /**
     * Отображение контента в модалке редактирования
     */
    $('#modalEditor').on('shown.bs.modal', function (event) {
        let route = $(event.relatedTarget).data('route')
        let modalContent = $("#modalEditor .modal-content")

        axios
            .create({
                headers: {
                    Authorization: 'Bearer ' + API_TOKEN
                }
            })
            .post(route)
            .then(({data}) => {
                modalContent.text('').append(data);
                LIBS.initAll()
                initCompanyNameSuggestion(
                    $('#modalEditor textarea[data-field="Company_official_name"]'),
                    $('#modalEditor input[name="inn"]'),
                    $('#modalEditor input[name="kpp"]'),
                    $('#modalEditor textarea[name="name"]'),
                    $('#modalEditor input[name="ogrn"]'),
                    $('#modalEditor input[name="address"]')
                )
            })

    })

    /**
     * Блокировка инпутов в модалке
     */
    const disableModalFields = (modal, inputsToDisableSelectors, disabled) => {
        inputsToDisableSelectors.forEach((inputToDisableSelector) => {
            const input = modal.find(inputToDisableSelector)

            input.prop("disabled", disabled);
            input.prop("required", !disabled);
        })
    }

    /**
     * Блокировка полей при выборе конкретного типа услуги
     */
    const disableServiceFieldsByProductType = (productTypeField, selectedValue) => {
        const modal = productTypeField.closest('.modal-body');

        const inputsToDisableSelectors = [
            'select[name=type_anketa]',
            'select[name="type_view[]"]'
        ];

        const disabled = selectedValue === 'Абонентская плата без реестров';

        disableModalFields(modal, inputsToDisableSelectors, disabled)
    }
    $("select[name=type_product]").on('change', function (event) {
        const field = $(event.target);
        const selected = field.val();

        disableServiceFieldsByProductType(field, selected);
    });
    $(document).on('change', '.filled-select2.filled-select.type_product', function (event) {
        const field = $(event.target);
        const selected = field.val();

        disableServiceFieldsByProductType(field, selected)
    });

    /**
     * Обновление списка доступных договоров для водителя при смене компании
     */
    $(document).on('change ready', 'select[name="company_id"]', function (event) {
        const field = $(event.target);
        const value = field.val();
        let targetSelect = field.closest('.modal-body').find('#select_for_contract_driver_car');
        targetSelect.empty();

        axios.post('/contract/getAvailableForCompany', {
            company_id: value,
        }).then(({data}) => {
            if (!data.status) {
                Swal2.fire('Ошибка', data.message, 'warning');
                return;
            }

            const contracts = (data.contracts ?? []).map((item) => {
                return {
                    value: item.id,
                    text: item.name
                }
            })

            contracts.push({
                value: '',
                text: 'Не установлено'
            })

            contracts.forEach((item) => {
                targetSelect.append($('<option>', item));
            })
        });
    })

    $('#CARD_DRIVER').on('input', 'input[name="phone"]', function() {
        const input = $('#CARD_DRIVER input[name="phone"]')
        input.val(input.val().replace(/[^+\-\d\s]/g, ''))
        if (input.val().length > 5) {
            input.val(input.val().slice(0, 18))
        }
    });
});

window.initDatePicker = initDatePicker

