@props(['title' => null, 'description' => null, 'image' => null])

@php
    $pageTitle = $title ? "{$title} · Joyitas Kira" : 'Joyitas Kira · Joyería';
    $pageDescription = $description ?? 'Joyitas Kira: aros, collares, anillos y pulseras. Elegí tus favoritas y consultanos por WhatsApp.';
    $whatsapp = $settings['whatsapp_number'] ?? '';
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $image ?? asset('images/logo-full.png') }}">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#faf7f0">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon-192.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">

    <script>
        window.KIRA = @js([
            'whatsapp' => $whatsapp,
            'greeting' => $settings['whatsapp_greeting'] ?? null,
            'cartDataUrl' => route('cart.data'),
        ]);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col" x-data="{ menu: false }" @keydown.escape.window="menu = false; $store.cart.open = false">

    {{-- Aviso superior configurable --}}
    @if (filled($settings['announcement'] ?? null))
        <div class="bg-kira-700 px-4 py-2 text-center text-xs tracking-[0.2em] text-cream uppercase">
            {{ $settings['announcement'] }}
        </div>
    @endif

    {{-- Encabezado --}}
    <header class="sticky top-0 z-30 border-b border-cream-300/70 bg-cream/90 backdrop-blur">
        <div class="container-kira flex h-20 items-center justify-between gap-4">
            <button type="button" class="-ml-2 p-2 text-ink lg:hidden" @click="menu = !menu" aria-label="Menú">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" /></svg>
            </button>

            <a href="{{ route('home') }}" class="shrink-0" aria-label="Joyitas Kira - Inicio">
                <img src="{{ asset('images/logo-wordmark.png') }}" alt="Kira joyas" class="h-12 w-auto sm:h-14">
            </a>

            <nav class="hidden items-center gap-8 text-sm tracking-[0.15em] uppercase lg:flex">
                <a href="{{ route('home') }}" @class(['transition hover:text-kira-500', 'text-kira-500' => request()->routeIs('home')])>Inicio</a>
                <div class="group relative">
                    <a href="{{ route('catalog') }}" @class(['transition hover:text-kira-500', 'text-kira-500' => request()->routeIs('catalog', 'products.show')])>Catálogo</a>
                    @if ($navCategories->isNotEmpty())
                        <div class="invisible absolute top-full left-1/2 z-40 w-52 -translate-x-1/2 pt-4 opacity-0 transition group-hover:visible group-hover:opacity-100">
                            <div class="rounded-2xl border border-cream-300 bg-cream p-3 shadow-lg shadow-ink/5">
                                @foreach ($navCategories as $navCategory)
                                    <a href="{{ route('catalog', ['categoria' => $navCategory->slug]) }}" class="block rounded-xl px-4 py-2 text-xs normal-case tracking-wide hover:bg-kira-50 hover:text-kira-600">{{ $navCategory->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <a href="#contacto" class="transition hover:text-kira-500">Contacto</a>
            </nav>

            <button type="button" class="relative -mr-2 p-2 text-ink transition hover:text-kira-500" @click="$store.cart.openCart()" aria-label="Abrir carrito">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                <span x-cloak x-show="$store.cart.count > 0" x-text="$store.cart.count"
                      class="absolute -top-0.5 -right-0.5 flex size-5 items-center justify-center rounded-full bg-gold-500 text-[10px] font-medium text-white"></span>
            </button>
        </div>

        {{-- Menú mobile --}}
        <nav x-cloak x-show="menu" x-collapse class="border-t border-cream-300 lg:hidden">
            <div class="container-kira flex flex-col py-4 text-sm tracking-[0.15em] uppercase">
                <a href="{{ route('home') }}" class="py-3">Inicio</a>
                <a href="{{ route('catalog') }}" class="py-3">Catálogo</a>
                @foreach ($navCategories as $navCategory)
                    <a href="{{ route('catalog', ['categoria' => $navCategory->slug]) }}" class="py-2 pl-4 text-xs normal-case tracking-wide text-ink-soft">{{ $navCategory->name }}</a>
                @endforeach
                <a href="#contacto" class="py-3" @click="menu = false">Contacto</a>
            </div>
        </nav>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Pie --}}
    <footer id="contacto" class="mt-24 border-t border-cream-300 bg-cream-200/60">
        <div class="container-kira grid gap-12 py-16 md:grid-cols-3">
            <div class="flex flex-col items-center gap-4 text-center md:items-start md:text-left">
                <img src="{{ asset('images/logo-mark.png') }}" alt="" class="size-20">
                <p class="max-w-xs text-sm leading-relaxed text-ink-soft">
                    {{ $settings['about_text'] ?? null ?: 'Joyas pensadas para acompañarte todos los días.' }}
                </p>
            </div>

            <div class="text-center md:text-left">
                <h3 class="eyebrow mb-5">Tienda</h3>
                <ul class="space-y-3 text-sm text-ink-soft">
                    <li><a href="{{ route('catalog') }}" class="hover:text-kira-500">Catálogo</a></li>
                    @foreach ($navCategories->take(5) as $navCategory)
                        <li><a href="{{ route('catalog', ['categoria' => $navCategory->slug]) }}" class="hover:text-kira-500">{{ $navCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="text-center md:text-left">
                <h3 class="eyebrow mb-5">Contacto</h3>
                <ul class="space-y-3 text-sm text-ink-soft">
                    @if ($whatsapp)
                        <li><a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="hover:text-kira-500">WhatsApp</a></li>
                    @endif
                    @if (filled($settings['instagram_url'] ?? null))
                        <li><a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener" class="hover:text-kira-500">Instagram</a></li>
                    @endif
                    @if (filled($settings['contact_email'] ?? null))
                        <li><a href="mailto:{{ $settings['contact_email'] }}" class="hover:text-kira-500">{{ $settings['contact_email'] }}</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="border-t border-cream-300 py-6 text-center text-xs tracking-widest text-ink-soft uppercase">
            © {{ date('Y') }} Joyitas Kira
        </div>
    </footer>

    <x-store.cart-drawer :whatsapp="$whatsapp" />

    {{-- Aviso "agregado al carrito" --}}
    <div x-cloak x-show="$store.cart.toast" x-transition.opacity
         class="fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-full bg-ink px-6 py-3 text-sm text-cream shadow-xl">
        <span x-text="$store.cart.toast"></span>
        <button type="button" class="ml-3 text-gold-400 underline-offset-4 hover:underline" @click="$store.cart.openCart(); $store.cart.toast = null">Ver carrito</button>
    </div>

    {{-- Botón flotante de WhatsApp --}}
    @if ($whatsapp)
        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="Escribinos por WhatsApp"
           class="fixed right-5 bottom-5 z-30 flex size-14 items-center justify-center rounded-full bg-[#25d366] text-white shadow-lg transition hover:scale-105">
            <x-store.whatsapp-icon class="size-7" />
        </a>
    @endif
</body>
</html>
