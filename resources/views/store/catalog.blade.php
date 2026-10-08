<x-layouts.store :title="$category?->name ?? 'Catálogo'" :description="$category?->description">

    <section class="container-kira pt-14 sm:pt-20">
        <x-store.section-title :eyebrow="$category ? 'Catálogo' : 'Joyitas Kira'" :title="$category?->name ?? 'Catálogo'" class="mb-10" />

        {{-- Filtros --}}
        <div class="mb-12 flex flex-col gap-6">
            <nav class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:flex-wrap sm:justify-center" aria-label="Categorías">
                <a href="{{ route('catalog', array_filter(['q' => $search ?: null, 'orden' => $sort !== 'recientes' ? $sort : null])) }}"
                   @class(['shrink-0 rounded-full border px-5 py-2 text-xs tracking-[0.15em] uppercase transition',
                           'border-kira-500 bg-kira-500 text-white' => ! $category,
                           'border-cream-300 hover:border-gold-400' => $category])>Todo</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('catalog', array_filter(['categoria' => $cat->slug, 'q' => $search ?: null, 'orden' => $sort !== 'recientes' ? $sort : null])) }}"
                       @class(['shrink-0 rounded-full border px-5 py-2 text-xs tracking-[0.15em] uppercase transition',
                               'border-kira-500 bg-kira-500 text-white' => $category?->is($cat),
                               'border-cream-300 hover:border-gold-400' => ! $category?->is($cat)])>{{ $cat->name }}</a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('catalog') }}" class="mx-auto flex w-full max-w-2xl flex-col gap-3 sm:flex-row">
                @if ($category)
                    <input type="hidden" name="categoria" value="{{ $category->slug }}">
                @endif
                <input type="search" name="q" value="{{ $search }}" placeholder="Buscar…" class="input-kira flex-1" aria-label="Buscar">
                <select name="orden" class="input-kira sm:w-48" aria-label="Ordenar" onchange="this.form.submit()">
                    @foreach ($sorts as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary py-2.5">Buscar</button>
            </form>
        </div>

        {{-- Productos --}}
        @if ($products->isEmpty())
            <div class="py-20 text-center">
                <img src="{{ asset('images/logo-mark.png') }}" alt="" class="mx-auto size-20 opacity-30">
                <p class="mt-6 text-ink-soft">
                    @if ($search)
                        No encontramos productos para “{{ $search }}”.
                    @else
                        Muy pronto vas a encontrar productos acá.
                    @endif
                </p>
                @if ($search || $category)
                    <a href="{{ route('catalog') }}" class="btn-outline mt-6">Ver todo</a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-16">
                {{ $products->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

</x-layouts.store>
