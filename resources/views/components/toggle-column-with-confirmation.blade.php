@props(['record', 'field'])

<div
    x-data="{ confirmed: false }"
    @click.prevent="if (!confirmed) { if (confirm('Are you sure you want to change this status?')) { confirmed = true; $dispatch('toggle', { recordId: '{{ $record->id }}', field: '{{ $field }}' }); } } else { $dispatch('toggle', { recordId: '{{ $record->id }}', field: '{{ $field }}' }); }"
    x-on:toggle.window="$wire.call('toggle', $event.detail.recordId, $event.detail.field)"
>
    <input type="checkbox" {{ $record->$field ? 'checked' : '' }} class="toggle-checkbox" readonly>
</div>
