@props(['model', 'type' => 'text', 'options' => [], 'placeholder' => '', 'compact' => false])

@php
    $base = $compact
        ? 'w-full border border-gray-300 rounded-lg text-sm px-2.5 py-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-200 outline-none'
        : 'w-full border border-gray-300 rounded-xl text-[15px] px-3.5 py-2.5 focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none';
@endphp

@switch($type)
    @case('textarea')
        <textarea wire:model="{{ $model }}" rows="3" class="{{ $base }} resize-none" placeholder="{{ $placeholder }}"></textarea>
        @break
    @case('number')
        <input type="number" inputmode="decimal" wire:model="{{ $model }}" class="{{ $base }}" placeholder="{{ $placeholder }}">
        @break
    @case('date')
        <input type="date" wire:model="{{ $model }}" class="{{ $base }}">
        @break
    @case('select')
        <select wire:model="{{ $model }}" class="{{ $base }} bg-white">
            <option value="">— Chọn —</option>
            @foreach($options as $o)
                <option value="{{ $o }}">{{ $o }}</option>
            @endforeach
        </select>
        @break
    @default
        <input type="text" wire:model="{{ $model }}" class="{{ $base }}" placeholder="{{ $placeholder }}">
@endswitch
