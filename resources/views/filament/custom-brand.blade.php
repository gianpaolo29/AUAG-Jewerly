<a href="{{ url('/admin') }}" class="flex items-center gap-2">
    {{-- Logo (light mode) --}}
    <img src="{{ asset('Auag.jpg') }}" alt="AUAG Logo" class="h-7 w-auto dark:hidden">

    {{-- Logo (dark mode, optional) --}}
    <img src="{{ asset('Auag.jpg') }}" alt="AUAG Logo Dark" class="h-7 w-auto hidden dark:block">

    {{-- Brand text --}}
    <span class="font-semibold text-primary-700 dark:text-primary-400">
        AUAG Jewelry
    </span>
</a>
