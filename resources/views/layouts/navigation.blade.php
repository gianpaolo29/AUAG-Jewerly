<header x-data="{ open: false }"
        class="fixed inset-x-0 top-0 z-50 border-b border-black/10 text-white backdrop-blur-md transition-all bg-header/70">

    <div class="max-w-7xl mx-auto px-4">
        <div class="h-16 flex items-center justify-between gap-4">

            {{-- Left: Brand --}}
            <div class="flex items-center gap-x-3">
                <img src="{{ asset('Auag.jpg') }}" alt="Luxe Jewelry Logo" class="h-10 w-auto">
            </div>

            {{-- Center Navigation (Desktop) --}}
            <nav class="hidden md:flex items-center justify-center gap-10">
                <a href="{{ url('/') }}"
                   class="font-medium hover:text-white {{ request()->is('/') ? 'text-white' : 'text-white/90' }}">
                    Home
                </a>
                <a href="{{ url('/shop') }}"
                   class="font-medium hover:text-white {{ request()->is('shop*') ? 'text-white' : 'text-white/90' }}">
                    Shop
                </a>
                <a href="{{ url('/pawn') }}"
                   class="font-medium hover:text-white {{ request()->is('pawn*') ? 'text-white' : 'text-white/90' }}">
                    Contact
                </a>
                <a href="{{ url('/repair') }}"
                   class="font-medium hover:text-white {{ request()->is('repair*') ? 'text-white' : 'text-white/90' }}">
                    About Us
                </a>
                <a href="{{ url('/sell') }}"
                   class="font-medium hover:text-white {{ request()->is('sell*') ? 'text-white' : 'text-white/90' }}">
                    Appraisal
                </a>
            </nav>

            {{-- Right: EMPTY (Removed Search, Cart, Auth) --}}
            <div class="hidden md:flex items-center justify-end opacity-0 pointer-events-none">
                <!-- Empty container to preserve spacing -->
                <span></span>
            </div>

            {{-- Mobile Hamburger --}}
            <div class="md:hidden">
                <button @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-white/80 hover:text-white hover:bg-white/10 transition"
                        aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-cloak x-show="open" @click.outside="open=false" class="md:hidden border-t border-white/10">
        <div class="px-4 py-3 space-y-2">

            <a href="{{ url('/') }}"
               class="block py-2 font-medium {{ request()->is('/') ? 'text-white' : 'text-white/90 hover:text-white' }}">
                Home
            </a>

            <a href="{{ url('/shop') }}"
               class="block py-2 text-white/90 hover:text-white font-medium">
                Shop
            </a>

            <a href="{{ url('/pawn') }}"
               class="block py-2 text-white/90 hover:text-white font-medium">
                Pawn
            </a>

            <a href="{{ url('/repair') }}"
               class="block py-2 text-white/90 hover:text-white font-medium">
                Repair
            </a>

            <a href="{{ url('/sell') }}"
               class="block py-2 text-white/90 hover:text-white font-medium">
                Sell
            </a>

        </div>
    </div>
</header>
