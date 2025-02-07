@php
    /** @var \App\Models\Forms\Form $anketa */
    $disabled = $anketa->trip_tickets_id !== null;
    $trash = request()->get('trash', 0) ?? 1;
    $icon = $trash ? "fa-undo" : "fa-trash";
    $color = $disabled ? "btn-secondary" : "btn-warning";
    $route = $disabled ? '#' : route('forms.trash', ['id' => $anketa->id, 'action' => !$trash]);
@endphp

<a
    href="{{ $route }}"
    class="btn {{ $color }} btn-sm hv-btn-trash mr-1"
    data-id="{{ $anketa->id }}"
    @disabled($disabled)>
    <i class="fa {{ $icon }}"></i>
</a>
