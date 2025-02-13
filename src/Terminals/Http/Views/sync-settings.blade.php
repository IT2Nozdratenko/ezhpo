@extends('layouts.app')

@section('title', 'Обновление настроек терминалов')
@section('sidebar', 1)

@php
    use Carbon\Carbon;
    $created = \Illuminate\Support\Facades\Session::get('created', []);
    $createByForms = $createByForms ?? request()->get('createByForms', '0') === '1';
@endphp

@section('custom-scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            const datesSelector = $("input[name='additional_dates']")
            initDatePicker(datesSelector)

            let ctr = 1;

            $('#trip-ticket-clone-btn').click(e => {
                const CLONE_ID = 'clone'

                if (ctr + 1 === 32) {
                    swal.fire({
                        title: 'Нельзя добавлять более 31 путевого листа',
                        icon: 'warning'
                    });

                    return
                }

                let firstClone = $(`#first-${CLONE_ID}`)
                let cloneTo = $('#clone-stack')
                let clone = firstClone.clone()
                let randId = 'trip_ticket_' + ctr

                if (cloneTo.find('.cloning-clone').length) {
                    clone = cloneTo.find('.cloning-clone').last().clone()
                }

                clone.removeAttr('id').addClass('cloning-clone')
                clone.attr('id', randId)
                cloneTo.append(clone)

                clone.find('input,select').each(function () {
                    this.name = this.name.replace('trip_ticket[' + (ctr - 1) + ']', 'trip_ticket[' + ctr + ']')
                })

                ctr++

                clone.find('.trip-ticket-delete').html('<a href="" onclick="' + randId + '.remove(); return false;" class="text-danger">Удалить</a>')
            })

            $('#trip-ticket-print-btn').click(function () {
                const spinner = $('.spinner-btn')
                const created = JSON.parse('@json($created)')
                const ids = created.map(item => item.uuid)

                spinner.attr('style', '')
                $(this).attr('style', 'display:none')

                axios({
                    method: 'post',
                    url: '{{ route('trip-tickets.mass-print') }}',
                    data: {
                        ids: ids,
                    },
                    responseType: 'blob',
                })
                    .then((response) => {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');

                        link.href = url;
                        link.setAttribute('download', 'Путевой лист.xlsx');

                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    })
                    .catch((error) => {
                        if (error.response && error.response.data instanceof Blob) {
                            const blob = error.response.data;

                            blob.text().then((text) => {
                                try {
                                    const errorData = JSON.parse(text);
                                    let message = errorData.error ?? '';

                                    swal.fire({
                                        title: 'При формировании файла произошла ошибка',
                                        text: message,
                                        icon: 'error'
                                    });
                                } catch (e) {
                                    swal.fire({
                                        title: 'При формировании файла произошла ошибка',
                                        text: text,
                                        icon: 'error'
                                    });
                                }
                            });
                        } else {
                            swal.fire({
                                title: 'При формировании файла произошла ошибка',
                                icon: 'error'
                            });
                        }
                    })
                    .finally(() => {
                        spinner.attr('style', 'display:none')
                        $(this).attr('style', '')
                    })
            })
        })
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <p><b>Синхронизания настроек терминалов</b></p>
                    <main-settings props-settings="{{ json_encode($settings) }}"></main-settings>
                </div>
            </div>
        </div>

    </div>
@endsection
