@props([
    'name',
    'id' => null,
    'selected' => null,
])

@php
    $id = $id ?: str_replace(['[', ']'], ['_', ''], $name);
    $current = old($name, $selected ?: '+49|DE');

    $countries = \App\Support\PhoneCountries::all();

    $isSelected = function (array $country) use ($current): bool {
        if ($current === $country['value']) {
            return true;
        }

        if ($current === $country['dial'] && $country['iso'] === 'DE') {
            return true;
        }

        return false;
    };
@endphp

<select
    id="{{ $id }}"
    name="{{ $name }}"
    class="premium-select phone-country-select"
    data-search="true"
    data-placeholder="Land / Vorwahl suchen"
    required
>
    @foreach ($countries as $country)
        <option
            value="{{ $country['value'] }}"
            data-iso="{{ strtolower($country['iso']) }}"
            data-name="{{ $country['name'] }}"
            data-dial="{{ $country['dial'] }}"
            @selected($isSelected($country))
        >
            {{ $country['name'] }} {{ $country['dial'] }}
        </option>
    @endforeach
</select>
