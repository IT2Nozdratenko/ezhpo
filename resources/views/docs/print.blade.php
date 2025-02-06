<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Наклейка осмотра {{ $form->id }}</title>
    <style>
        @font-face {
            font-family: 'Arial';
            src: url({{ storage_path('fonts//ArialMT.eot') }}) format('embedded-opentype'),
            url({{ storage_path('fonts/ArialMT.woff') }}) format('woff'),
            url({{ storage_path('fonts/ArialMT.ttf') }}) format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        @page {
            margin: 0;
        }

        body {
            font-family: "Arial";
            margin: 0;
        }

        h2, h3 {
            font-size: 12px;
            margin: 0;
        }

        b {
            font-weight: normal;
        }

        .print {
            text-align: center;
            font-size: 7.7px;
            width: 215px;
            padding-top: 5.5px;
            line-height: 6px;
        }

        .title {
            font-size: 10.5px;
            padding: 0;
            margin: 0;
            display: inline;
        }

        .licence {
            font-size: 5.1px;
            margin-bottom: 1.5px;
            display: inline-block;
        }

        .validity {
            font-size: 7px;
            line-height: 5px;
            display: inline-block;
        }

        .name {
            display: inline-block;
        }

    </style>
</head>
<body>

<div class="print">
    @php /** @var $stamp \App\Services\TripTicketExporter\ViewModels\StampViewModel */@endphp
    <span class="title">{{ $stamp->getReqName() }}</span><br>
    <span class="licence">
            {{ $stamp->getLicense() }}
    </span><br>
    <span class="name">{{ $form->driver->fio }}</span><br>
    прошел {{ $form->details->type_view }}<br>
    Медицинский осмотр<br>
    @if(Str::contains(Str::lower($form->details->type_view), 'пред'))
        к исполнению трудовых обязаностей<br>
        допущен
    @endif

    <br><br>
    {{ $form->date ?? '0000-00-00 00:00:00' }}<br>
    {{ $user->name ?? 'неизвестный сотрудник' }}<br>
    ЭЦП {{ $form->user_eds ?? 'неизвестная-подпись' }}<br>
    @if ($validity)
        <span class="validity">{{ $validity }}</span>
    @endif
</div>
</body>
</html>
