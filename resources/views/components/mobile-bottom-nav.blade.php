@php
    use App\Support\MobileBottomNav;

    $navItems = MobileBottomNav::items();
    $navClass = $navClass ?? 'bottom-nav-mobile';
    $itemClass = $itemClass ?? 'bottom-nav-mobile-item';
@endphp

@if(count($navItems) > 0)
<nav class="{{ $navClass }}">
    @foreach($navItems as $item)
        @php
            $active = false;
            foreach ($item['patterns'] as $pattern) {
                if (request()->routeIs($pattern)) {
                    $active = true;
                    break;
                }
            }
            $href = route($item['route'], $item['params'] ?? []);
            if (! empty($item['mobile'])) {
                $href .= (str_contains($href, '?') ? '&' : '?').'mobile=1';
            }
        @endphp
        <a href="{{ $href }}" class="{{ $itemClass }}{{ $active ? ' active' : '' }}">
            <i class="fas {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
@endif
