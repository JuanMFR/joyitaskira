@props(['product'])

@php($cartItem = $product->toCartArray())

<article class="group flex flex-col">
    <a href="{{ $cartItem['url'] }}" class="relative block aspect-[4/5] overflow-hidden rounded-2xl bg-cream-200">
        @if ($product->mainImage)
            <img src="{{ $product->mainImage->url }}" alt="{{ $product->name }}" loading="lazy"
                 @class(['size-full object-cover transition duration-700 group-hover:scale-105', 'opacity-60 grayscale-[40%]' => ! $cartItem['purchasable']])>
        @else
            <img src="{{ asset('images/logo-mark.png') }}" alt="" class="size-full object-contain p-12 opacity-25">
        @endif

        @unless ($cartItem['purchasable'])
            <span class="absolute top-3 left-3 rounded-full bg-cream/90 px-3 py-1 text-[10px] tracking-[0.2em] text-ink-soft uppercase">{{ $cartItem['statusLabel'] }}</span>
        @endunless
    </a>

    <div class="flex flex-1 flex-col items-center px-2 pt-4 text-center">
        <h3 class="font-display text-xl leading-snug">
            <a href="{{ $cartItem['url'] }}" class="hover:text-kira-600">{{ $product->name }}</a>
        </h3>
        <p class="mt-1 text-sm tracking-wide text-kira-700">{{ $cartItem['priceLabel'] }}</p>

        @if ($cartItem['purchasable'])
            <button type="button" class="mt-3 text-xs tracking-[0.2em] text-gold-600 uppercase underline-offset-8 transition hover:text-kira-600 hover:underline"
                    @click="$store.cart.has({{ $product->id }}) ? $store.cart.openCart() : $store.cart.add(@js($cartItem))">
                <span x-text="$store.cart.has({{ $product->id }}) ? '✓ En tu carrito' : 'Agregar al carrito'">Agregar al carrito</span>
            </button>
        @endif
    </div>
</article>
