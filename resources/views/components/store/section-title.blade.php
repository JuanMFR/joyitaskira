@props(['eyebrow' => null, 'title'])

<div {{ $attributes->class('mb-12 text-center') }}>
    @if ($eyebrow)
        <p class="eyebrow mb-3">{{ $eyebrow }}</p>
    @endif
    <h2 class="title-display text-4xl sm:text-5xl">{{ $title }}</h2>
    <div class="divider-sparkle mt-5" aria-hidden="true">✦</div>
</div>
