@props(['text', 'label' => 'Copy'])

<button
    type="button"
    x-data="{ copied: false }"
    @click="navigator.clipboard.writeText(@js($text)).then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
    {{ $attributes->merge(['class' => 'btn-secondary px-3 py-1.5']) }}
>
    <span x-text="copied ? 'Copied!' : @js($label)">{{ $label }}</span>
</button>
