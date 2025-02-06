<table>
    <thead>
    <tr>
        @foreach($fields as $key => $field)
            <th
                height="60" width="{{ $key === 'admitted' ? '45' : '15' }}" valign="center" align="center" style="word-wrap: break-word"
            ><b>{{ $field }}</b></th>
        @endforeach
    </tr>
    </thead>

    <tbody>
    @foreach($data as $item)
        <tr>
            @foreach($fields as $key => $field)
                @if ($item['type_anketa'] === \App\Enums\FormTypeEnum::BDD && $key === 'date')
                    <td>{{ date('d-m-Y', strtotime($item[$key])) }}</td>
                @elseif($key === 'date' || $key === 'created_at' || $key === 'updated_at')
                    <td>{{$item[$key] ? \Carbon\Carbon::parse($item[$key])->format("d.m.Y H:i:s") : '' }}</td>
                @elseif($item['type_anketa'] === \App\Enums\FormTypeEnum::MEDIC && $key === 'admitted' && in_array($item[$key], ['Допущен', 'Не допущен', 'Недопущен']))
                    @if($item['type_view'] === 'Предрейсовый/Предсменный')
                        <td>Прошел предсменный/предрейсовый медицинский осмотр, к исполнению трудовых обязанностей @if($item[$key] !== 'Допущен') НЕ @endif допущен</td>
                    @else
                        <td>Прошел послесменный/послерейсовый медицинский осмотр@if($item[$key] !== 'Допущен'), выявлены признаки отклонения@endif</td>
                    @endif
                @else
                    <td>{{ $item[$key] }}</td>
                @endif
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
