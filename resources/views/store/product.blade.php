@php($cartItem = $product->toCartArray())

<x-layouts.store :title="$product->name" :description="Str::limit($product->description ?: $product->name.' · '.$cartItem['priceLabel'], 155)" :image="$product->mainImage?->url">

    <section class="container-kira pt-8 sm:pt-12">
        {{-- Migas --}}
        <nav class="mb-8 text-xs tracking-widest text-ink-soft uppercase" aria-label="Ubicación">
            <a href="{{ route('catalog') }}" class="hover:text-kira-600">Catálogo</a>
            @if ($product->category)
                <span class="mx-2 text-gold-500">✦</span>
                <a href="{{ route('catalog', ['categoria' => $product->category->slug]) }}" class="hover:text-kira-600">{{ $product->category->name }}</a>
            @endif
        </nav>

        <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
            {{-- Galería --}}
            @php($images = $product->images->sortByDesc('is_main')->values())
            <div x-data="{ active: 0 }" class="flex flex-col gap-4">
                <div class="relative aspect-square overflow-hidden rounded-3xl bg-cream-200">
                    @forelse ($images as $i => $image)
                        <img x-show="active === {{ $i }}" @if ($i > 0) x-cloak @endif x-transition.opacity.duration.300ms
                             src="{{ $image->url }}" alt="{{ $product->name }}" class="absolute inset-0 size-full object-cover">
                    @empty
                        <img src="{{ asset('images/logo-mark.png') }}" alt="" class="size-full object-contain p-20 opacity-25">
                    @endforelse
                </div>

                @if ($images->count() > 1)
                    <div class="grid grid-cols-5 gap-3">
                        @foreach ($images as $i => $image)
                            <button type="button" @click="active = {{ $i }}" class="aspect-square overflow-hidden rounded-xl ring-offset-2 ring-offset-cream transition"
                                    :class="active === {{ $i }} ? 'ring-2 ring-gold-400' : 'opacity-70 hover:opacity-100'" aria-label="Ver foto {{ $i + 1 }}">
                                <img src="{{ $image->url }}" alt="" loading="lazy" class="size-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Información --}}
            <div class="flex flex-col lg:py-6">
                @if ($product->category)
                    <p class="eyebrow">{{ $product->category->name }}</p>
                @endif
                <h1 class="title-display mt-3 text-4xl leading-tight sm:text-5xl">{{ $product->name }}</h1>
                <p class="mt-5 text-2xl tracking-wide text-kira-700">{{ $cartItem['priceLabel'] }}</p>

                <div class="divider-sparkle my-8 justify-start before:hidden" aria-hidden="true">✦</div>

                @if ($product->description)
                    <div class="prose-kira">
                        @foreach (preg_split('/\n\s*\n/', trim($product->description)) as $paragraph)
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endforeach
                    </div>
                @endif

                <div class="mt-8 space-y-4">
                    @if ($cartItem['purchasable'])
                        <button type="button" x-show="! $store.cart.has({{ $product->id }})" class="btn-primary w-full py-4" @click="$store.cart.add(@js($cartItem))">
                            Agregar al carrito
                        </button>
                        <button type="button" x-cloak x-show="$store.cart.has({{ $product->id }})" class="btn-outline w-full py-4" @click="$store.cart.openCart()">
                            ✓ En tu carrito · Ver carrito
                        </button>
                        <p class="text-center text-xs tracking-widest text-ink-soft uppercase">Pieza única · 1 disponible</p>
                    @else
                        <p class="rounded-2xl border border-gold-400/50 bg-gold-200/30 px-5 py-4 text-sm text-ink-soft">
                            <span class="font-medium text-gold-600">{{ $cartItem['statusLabel'] }}.</span>
                            Escribinos y te contamos si vuelve a estar disponible.
                        </p>
                    @endif

                    <template x-if="$store.cart.whatsapp">
                        <a :href="$store.cart.productQuestionUrl(@js($cartItem))" target="_blank" rel="noopener" class="btn-outline w-full py-4">
                            <x-store.whatsapp-icon class="size-5 text-[#25d366]" />
                            Consultar por WhatsApp
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="container-kira pt-24">
            <x-store.section-title eyebrow="Te puede gustar" title="También te va a encantar" />
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-4">
                @foreach ($related as $item)
                    <x-store.product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif

</x-layouts.store>
