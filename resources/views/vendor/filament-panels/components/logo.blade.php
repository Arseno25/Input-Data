@php
    use App\Settings\WebsiteSettings;

    $settings = app(WebsiteSettings::class);
    $brandName = $settings->website_title;
    $logoUrl = $settings->use_logo ? \Illuminate\Support\Facades\Storage::url('public/' . $settings->website_logo) : null;

    $brandName = filament()->getBrandName();
    $brandLogo = filament()->getBrandLogo();
    $brandLogoHeight = filament()->getBrandLogoHeight() ?? '1.5rem';
    $darkModeBrandLogo = filament()->getDarkModeBrandLogo();
    $hasDarkModeBrandLogo = filled($darkModeBrandLogo);

    $getLogoClasses = fn (bool $isDarkMode): string => \Illuminate\Support\Arr::toCssClasses([
        'fi-logo',
        'flex' => ! $hasDarkModeBrandLogo,
        'flex dark:hidden' => $hasDarkModeBrandLogo && (! $isDarkMode),
        'hidden dark:flex' => $hasDarkModeBrandLogo && $isDarkMode,
    ]);

    $logoStyles = "height: 40px";

    $isDarkMode = false
@endphp


<img
        alt="{{ __('filament-panels::layout.logo.alt', ['name' => $brandName]) }}"
        src="{{ $logoUrl }}"
        {{
            $attributes
                ->class([$getLogoClasses($isDarkMode)])
                ->style([$logoStyles])
        }}
/>