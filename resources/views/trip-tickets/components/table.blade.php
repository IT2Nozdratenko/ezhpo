<table id="trip-tickets-table" class="trip-tickets-table table table-striped table-sm" style="min-height: 170px">
    <thead>
    <tr>
        <th>#</th>

        @foreach($fieldPrompts as $field)
            <th data-field-key="{{ $field->field }}"
                @isset($blockedToExportFields[$field->field])
                    class="not-export"
                @endisset>
                <span class="user-select-none"
                      @if ($field->content)
                          data-toggle="tooltip"
                      data-html="true"
                      data-trigger="click hover"
                      title="{{ $field->content }}"
                      @endif>
                    {{ $field->name }}
                </span>
                <a class="not-export"
                   href="?orderBy={{ $orderBy === 'DESC' ? 'ASC' : 'DESC' }}&orderKey={{ $field->field }}&{{ $queryString }}">
                    <i class="fa fa-sort"></i>
                </a>
            </th>
        @endforeach

        @if(request()->get('trash'))
            <th width="60">Удаливший</th>
            <th width="60">Время удаления</th>
        @endif
        <th>#</th>
    </tr>
    </thead>
    <tbody>
    @foreach($tripTickets as $tripTicketKey => $tripTicket)
        <tr data-field="{{ $tripTicketKey }}">
            <td>
                <input
                    type="checkbox"
                    data-id="{{ $tripTicket->uuid }}"
                    class="hv-checkbox-mass-deletion">
            </td>

            @foreach($fieldPrompts as $field)
                <td data-field-key="{{ $field->field }}"
                    @isset($blockedToExportFields[$field->field])
                        class="not-export"
                    @endisset>
                    @if(in_array($field->field, ['start_date', 'created_at']) && $tripTicket[$field->field])
                        {{ date('d.m.Y', strtotime($tripTicket[$field->field])) }}
                    @elseif($field->field === 'period_pl' && $tripTicket[$field->field])
                        {{ date('m.Y', strtotime($tripTicket[$field->field])) }}
                    @elseif($field->field === 'driver_fio' && user()->access('drivers_read'))
                        <a href="{{ route('renderElements', ['model' => 'Driver', 'filter' => 1, 'fio' => $tripTicket[$field->field] ]) }}">
                            {{ $tripTicket[$field->field] }}
                        </a>
                    @elseif($field->field === 'car_gos_number' && user()->access('cars_read'))
                        <a href="{{ route('renderElements', ['model' => 'Car', 'filter' => 1, 'gos_number' => $tripTicket[$field->field] ]) }}">
                            {{ $tripTicket[$field->field] }}
                        </a>
                    @elseif($field->field === 'logistics_method')
                        {{ \App\Enums\LogisticsMethodEnum::getLabel($tripTicket[$field->field]) }}
                    @elseif($field->field === 'transportation_type')
                        {{ \App\Enums\TransportationTypeEnum::getLabel($tripTicket[$field->field]) }}
                    @elseif($field->field === 'template_code')
                        {{ \App\Enums\TripTicketTemplateEnum::getLabel($tripTicket[$field->field]) }}
                    @elseif($field->field === 'photos' && $tripTicket[$field->field])
                        <a class="photos-modal-btn" data-toggle="modal" data-target="#photos-view" style="cursor: pointer" data-id="{{ $tripTicket->uuid }}">
                            <i class="fa fa-camera"></i>
                        </a>
                    @else
                        {{ $tripTicket[$field->field] }}
                    @endif
                </td>
            @endforeach

            @if($permissionToDelete && request()->get('trash'))
                <td class="td-option">
                    {{ ($tripTicket->deleted_user_name) }}
                </td>
                <td class="td-option">
                    {{ ($tripTicket->deleted_at) }}
                </td>
            @endif

            <td class="td-option not-export dropleft d-flex" style="width: 40px">
                <a class="dropdown-toggle" type="button" data-toggle="dropdown"
                   aria-expanded="false" style="font-size: 1.5rem">
                    <i class="fa fa-ellipsis-h"></i>
                </a>
                <div class="dropdown-menu">
                    @if($permissionToUpdate)
                        <a href="{{ route('trip-tickets.edit', $tripTicket->uuid) }}"
                           class="dropdown-item"><i class="fa fa-edit"></i> Редактировать ПЛ</a>
                    @endif
                    @if($permissionToEditMedicForm && $tripTicket['medic_form_id'])
                        <a href="{{ route('forms.get', $tripTicket->medic_form_id) }}"
                           class="dropdown-item"><i class="fa fa-edit"></i> Редактировать МО</a>
                    @endif
                    @if($permissionToEditTechForm && $tripTicket['tech_form_id'])
                        <a href="{{ route('forms.get', $tripTicket->tech_form_id) }}"
                           class="dropdown-item"><i class="fa fa-edit"></i> Редактировать ТО</a>
                    @endif

                    @if($permissionToCreateMedicForm && ! $tripTicket['medic_form_id'])
                        <a href="{{ route('trip-tickets.create-form', ['id' => $tripTicket->uuid, 'type' => \App\Enums\FormTypeEnum::MEDIC]) }}" class="dropdown-item">
                            <i class="fa fa-plus"></i> Добавить МО</a>
                    @endif
                    @if($permissionToCreateTechForm && ! $tripTicket['tech_form_id'])
                        <a href="{{ route('trip-tickets.create-form', ['id' => $tripTicket->uuid, 'type' => \App\Enums\FormTypeEnum::TECH]) }}" class="dropdown-item">
                            <i class="fa fa-plus"></i> Добавить ТО</a>
                    @endif
                    @if($permissionToUpdate)
                        <a class="dropdown-item form-actions-modal-btn" data-toggle="modal" data-target="#from-actions" style="cursor: pointer" data-id="{{ $tripTicket->uuid }}">
                            &plusmn; Привязать/отвязать МО и ТО
                        </a>
                    @endif
                    @if($permissionToPrintTripTickets)
                        <a class="dropdown-item download-excel-to-print-btn"
                           data-uuid="{{ $tripTicket->uuid }}" style="cursor: pointer">
                            <i class="fa fa-file-excel-o"></i> Печать ПЛ
                        </a>
                    @endif
                    @if($permissionToDelete)
                        <a
                            href="{{ route('trip-tickets.trash', ['id' => $tripTicket->uuid, 'action' => request()->get('trash') ? 0 : 1]) }}"
                            class="hv-btn-trash dropdown-item delete"
                            data-id="{{ $tripTicket->id }}"
                            data-action="{{ request()->get('trash') ? 0 : 1 }}">
                            @if(request()->get('trash', 0))
                                <i class="fa fa-undo"></i> Восстановить
                            @else
                                <i class="fa fa-trash"></i> Удалить
                            @endisset
                        </a>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

@component('trip-tickets.components.form-actions-modal')@endcomponent
@component('trip-tickets.components.photos-modal')@endcomponent
