@props(['route', 'active' => false])

<a href="{{ route($route) }}" {{ $attributes->class([
    'font-semibold text-red-600' => $active,
    'text-gray-600 hover:text-gray-900' => ! $active,
]) }}@if ($active) aria-current="page"@endif>{{ $slot }}</a>
