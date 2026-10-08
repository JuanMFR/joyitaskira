@props(['whatsapp' => ''])

<div x-cloak x-show="$store.cart.open" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Carrito">
    <div x-show="$store.cart.open" x-transition.opacity class="absolute inset-0 bg-ink/40 backdrop-blur-sm" @click="$store.cart.open = false"></div>

    <aside x-show="$store.cart.open"
           x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
           class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-cream shadow-2xl">

        <div class="flex items-center justify-between border-b border-cream-300 px-6 py-5">
            <h2 class="title-display text-2xl">Tu carrito</h2>
            <button type="button" class="p-2 text-ink-soft hover:text-ink" @click="$store.cart.open = false" aria-label="Cerrar">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        {{-- Vacío --}}
        <div x-show="$store.cart.items.length === 0" class="flex flex-1 flex-col items-center justify-center gap-5 px-6 text-center">
            <img src="{{ asset('images/logo-mark.png') }}" alt="" class="size-20 opacity-40">
            <p class="text-ink-soft">Todavía no agregaste nada.</p>
            <a href="{{ route('catalog') }}" class="btn-outline">Ver catálogo</a>
        </div>

        {{-- Productos --}}
        <ul x-show="$store.cart.items.length > 0" class="flex-1 divide-y divide-cream-300 overflow-y-auto px-6">
            <template x-for="item in $store.cart.items" :key="item.id">
                <li class="flex gap-4 py-5">
                    <a :href="item.url" class="size-20 shrink-0 overflow-hidden rounded-xl bg-cream-200">
                        <img x-show="item.image" :src="item.image" :alt="item.name" class="size-full object-cover">
                        <img x-show="!item.image" src="{{ asset('images/logo-mark.png') }}" alt="" class="size-full object-contain p-3 opacity-40">
                    </a>
                    <div class="flex flex-1 flex-col">
                        <div class="flex justify-between gap-2">
                            <a :href="item.url" class="font-display text-lg leading-tight hover:text-kira-600" x-text="item.name"></a>
                            <button type="button" class="self-start p-1 text-ink-soft hover:text-red-700" @click="$store.cart.remove(item.id)" aria-label="Quitar">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <template x-if="item.purchasable">
                            <p class="mt-auto pt-3 text-sm font-medium" x-text="item.priceLabel"></p>
                        </template>
                        <template x-if="!item.purchasable">
                            <p class="mt-auto pt-3 text-xs tracking-widest text-gold-600 uppercase">
                                <span x-text="item.statusLabel"></span> · no se incluye en el pedido
                            </p>
                        </template>
                    </div>
                </li>
            </template>
        </ul>

        {{-- Resumen y WhatsApp --}}
        <div x-show="$store.cart.items.length > 0" class="space-y-4 border-t border-cream-300 bg-cream-200/50 px-6 py-6">
            <div class="flex items-baseline justify-between">
                <span class="eyebrow">Total</span>
                <span class="font-display text-3xl" x-text="$store.cart.totalLabel"></span>
            </div>

            <input type="text" x-model="$store.cart.customerName" class="input-kira" placeholder="Tu nombre (opcional)" maxlength="80">
            <textarea x-model="$store.cart.note" rows="2" class="input-kira rounded-2xl" placeholder="Comentario: envío, retiro, regalo… (opcional)" maxlength="300"></textarea>

            @if ($whatsapp)
                <a :href="$store.cart.whatsappUrl" target="_blank" rel="noopener"
                   :class="{ 'pointer-events-none opacity-50': $store.cart.available.length === 0 }"
                   class="btn-whatsapp w-full py-4">
                    <x-store.whatsapp-icon class="size-5" />
                    Enviar pedido por WhatsApp
                </a>
                <p class="text-center text-xs text-ink-soft">Se abre WhatsApp con tu pedido listo para enviar. Coordinamos pago y entrega por ahí.</p>
            @else
                <p class="rounded-xl bg-gold-200/50 p-3 text-center text-xs text-ink-soft">Muy pronto vas a poder enviarnos tu pedido por WhatsApp.</p>
            @endif

            <button type="button" class="w-full text-center text-xs text-ink-soft underline-offset-4 hover:underline" @click="if (confirm('¿Vaciar el carrito?')) $store.cart.clear()">Vaciar carrito</button>
        </div>
    </aside>
</div>
