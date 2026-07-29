@props(['group'])

@if ($group)
    <span {{ $attributes->class(['premium-badge'])->merge(['style' => $group->badgeStyle()]) }}>
        {{ trim((string) $slot) !== '' ? $slot : $group->name }}
    </span>
@else
    <span {{ $attributes->class(['premium-badge']) }}>—</span>
@endif
