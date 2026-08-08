@props(['size' => 'sm'])

@php
    $sizes = [
        'sm' => ['icon' => 'h-9 w-9', 'text' => 'text-base'],
        'md' => ['icon' => 'h-10 w-10', 'text' => 'text-lg'],
        'lg' => ['icon' => 'h-16 w-16', 'text' => 'text-2xl'],
    ][$size] ?? ['icon' => 'h-9 w-9', 'text' => 'text-base'];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <img src="{{ asset('images/logo.png') }}" alt="" aria-hidden="true" class="{{ $sizes['icon'] }} shrink-0 object-contain">
    <span class="{{ $sizes['text'] }} whitespace-nowrap font-bold tracking-tight text-stone-900 dark:text-stone-100">Second <span class="text-sb-accent">Brain</span></span>
</div>
