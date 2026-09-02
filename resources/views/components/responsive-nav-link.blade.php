@props(['active'])
@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-pine-500 text-start text-base font-medium text-pine-700 bg-pine-50 focus:outline-none focus:text-pine-800 focus:bg-pine-100 focus:border-pine-600 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-muted hover:text-ink hover:bg-paper focus:outline-none focus:text-ink focus:bg-paper transition duration-150 ease-in-out';
@endphp
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>