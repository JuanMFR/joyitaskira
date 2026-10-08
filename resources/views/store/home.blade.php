<x-layouts.store>

    {{-- Carrusel de noticias (o portada con el logo si no hay ninguna) --}}
    @if ($news->isNotEmpty())
        <section class="relative" aria-label="Noticias" x-data="{
                current: 0,
                total: {{ $news->count() }},
                timer: null,
                go(i) { this.current = (i + this.total) % this.total; this.restart(); },
                restart() { clearInterval(this.timer); if (this.total > 1) this.timer = setInterval(() => this.current = (this.current + 1) % this.total, 6000); },
            }" x-init="restart()">
            <div class="relative h-[70vh] min-h-[420px] overflow-hidden sm:h-[80vh]">
                @foreach ($news as $i => $item)
                    <div x-show="current === {{ $i }}" @if ($i > 0) x-cloak @endif
                         x-transition:enter="transition duration-1000" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="transition duration-1000 absolute inset-0" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         @class(['group absolute inset-0', 'cursor-pointer' => $item->link_url])>
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}"
                             class="size-full object-cover" @if ($i > 0) loading="lazy" @endif>
                        <div class="absolute inset-0 bg-linear-to-t from-ink/70 via-ink/15 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 pb-20 text-center text-cream sm:pb-24">
                            <div class="container-kira">
                                <h2 class="font-display text-5xl font-medium sm:text-7xl">{{ $item->title }}</h2>
                                @if ($item->description)
                                    <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-cream/90 sm:text-lg">{{ $item->description }}</p>
                                @endif
                                @if ($item->link_url)
                                    <span class="btn mt-8 border border-cream/70 text-cream transition group-hover:bg-cream group-hover:text-ink">Ver más</span>
                                @endif
                            </div>
                        </div>
                        {{-- Con link, toda la noticia es clickeable --}}
                        @if ($item->link_url)
                            <a href="{{ $item->link_url }}" class="absolute inset-0" aria-label="{{ $item->title }}"
                               @if ($item->isExternalLink()) target="_blank" rel="noopener" @endif></a>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($news->count() > 1)
                <button type="button" @click="go(current - 1)" class="absolute top-1/2 left-4 -translate-y-1/2 rounded-full bg-cream/70 p-3 text-ink backdrop-blur transition hover:bg-cream" aria-label="Anterior">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </button>
                <button type="button" @click="go(current + 1)" class="absolute top-1/2 right-4 -translate-y-1/2 rounded-full bg-cream/70 p-3 text-ink backdrop-blur transition hover:bg-cream" aria-label="Siguiente">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                </button>
                <div class="absolute inset-x-0 bottom-6 flex justify-center gap-2">
                    @foreach ($news as $i => $item)
                        <button type="button" @click="go({{ $i }})" class="h-1.5 rounded-full transition-all"
                                :class="current === {{ $i }} ? 'w-8 bg-gold-400' : 'w-3 bg-cream/70'" aria-label="Ir a la noticia {{ $i + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,var(--color-kira-50),transparent_60%)]"></div>
            <div class="container-kira relative flex flex-col items-center py-20 text-center sm:py-28">
                <img src="{{ asset('images/logo-full.png') }}" alt="Kira joyas" class="w-56 animate-fade-in sm:w-72">
                <p class="mt-10 max-w-md font-display text-2xl text-ink-soft italic sm:text-3xl">Pequeños detalles que brillan con vos</p>
                <a href="{{ route('catalog') }}" class="btn-primary mt-10">Ver catálogo</a>
            </div>
        </section>
    @endif

    {{-- Categorías --}}
    @if ($categories->isNotEmpty())
        <section class="container-kira pt-20">
            <div class="flex flex-wrap justify-center gap-3">
                @foreach ($categories as $category)
                    <a href="{{ route('catalog', ['categoria' => $category->slug]) }}"
                       class="rounded-full border border-cream-300 px-6 py-2.5 text-xs tracking-[0.2em] uppercase transition hover:border-gold-400 hover:bg-white/60 hover:text-kira-600">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Destacados --}}
    @if ($featured->isNotEmpty())
        <section class="container-kira pt-20">
            <x-store.section-title eyebrow="Nuestra selección" title="Destacados" />
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 lg:grid-cols-4">
                @foreach ($featured as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-14 text-center">
                <a href="{{ route('catalog') }}" class="btn-outline">Ver todo el catálogo</a>
            </div>
        </section>
    @endif

    {{-- Cómo comprar --}}
    <section class="container-kira pt-24">
        <x-store.section-title eyebrow="Simple y rápido" title="¿Cómo comprar?" />
        <div class="grid gap-10 text-center sm:grid-cols-3">
            @foreach ([
                ['1', 'Elegí', 'Recorré el catálogo y sumá tus favoritas al carrito.'],
                ['2', 'Enviá', 'Mandanos tu pedido por WhatsApp con un solo clic.'],
                ['3', 'Coordinamos', 'Confirmamos disponibilidad, pago y entrega por chat.'],
            ] as [$step, $title, $text])
                <div class="flex flex-col items-center">
                    <span class="flex size-14 items-center justify-center rounded-full border border-gold-400 font-display text-2xl text-gold-600">{{ $step }}</span>
                    <h3 class="mt-5 font-display text-2xl">{{ $title }}</h3>
                    <p class="mt-2 max-w-xs text-sm leading-relaxed text-ink-soft">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

</x-layouts.store>
