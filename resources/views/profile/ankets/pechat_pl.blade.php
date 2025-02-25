<div class="row">
    <div class="col-12">
        <input type="hidden" name="type_anketa" value="{{ $type_anketa }}" />

        @include('profile.ankets.components.pvs')

        <div class="form-group">
            <label class="form-control-label">Название компании:</label>
            <article>
                @include('templates.elements_field', [
                    'v' => [
                        'label' => 'Компания',
                        'type' => 'select',
                        'values' => 'Company',
                        'getField' => 'name',
                        'concatField' => 'hash_id',
                        'getFieldKey' => 'hash_id'
                    ],
                    'k' => 'company_id',
                    'is_required' => 'required',
                    'model' => 'Company',
                    'default_value' => request()->get('company_id') ?? $company_id ?? ''
                ])
                <div class="app-checker-prop"></div>
            </article>
        </div>

        <div class="cloning" id="cloning-first">
            <div class="form-group">
                <label class="form-control-label">Дата и время печати:</label>
                <article>
                    <input min="1900-02-20T20:20"
                           max="2999-02-20T20:20"
                           type="datetime-local"
                           required
                           value="{{ $default_current_date ?? '' }}"
                           name="anketa[0][date]"
                           class="form-control">
                </article>
            </div>

            <div class="form-group">
                <label class="form-control-label">ID водителя:</label>
                <article>
                    <input value="{{ $driver_id ?? '' }}"
                           type="number"
                           oninput="if(this.value.length >= 6) checkInputProp('hash_id', 'Driver', event.target.value, 'fio', $(event.target).parent())"
                           min="6"
                           name="anketa[0][driver_id]"
                           class="MASK_ID_ELEM form-control">
                    <div class="app-checker-prop"></div>
                </article>
            </div>

            <div class="form-group">
                <label class="form-control-label">Количество распечатанных ПЛ:</label>
                <article>
                    <input type="number" required name="anketa[0][count_pl]" value="{{ $count_pl ?? '' }}" class="form-control">
                </article>
            </div>

            <div class="form-group">
                <label for="period_pl" class="form-control-label">Период действия ПЛ:</label>
                <input
                    class="form-control pl-period"
                    name="anketa[0][period_pl]"
                    required
                    type="month"
                    value="{{ $period_pl ?? '' }}"
                />
            </div>

            <div class="anketa-delete"></div>
        </div>
    </div>
</div>


