<?php

// The 20-language roster the switcher offers, plus English (the source locale,
// always present and never machine-generated). 'dir' drives <html dir="...">
// for RTL locales (Arabic, Hebrew) — the existing ltr:/rtl: Tailwind classes
// already used in components/dropdown.blade.php activate correctly once this is set.
return [
    'en' => ['native_name' => 'English', 'dir' => 'ltr'],
    'es' => ['native_name' => 'Español', 'dir' => 'ltr'],
    'fr' => ['native_name' => 'Français', 'dir' => 'ltr'],
    'de' => ['native_name' => 'Deutsch', 'dir' => 'ltr'],
    'pt' => ['native_name' => 'Português', 'dir' => 'ltr'],
    'it' => ['native_name' => 'Italiano', 'dir' => 'ltr'],
    'ar' => ['native_name' => 'العربية', 'dir' => 'rtl'],
    'zh' => ['native_name' => '中文', 'dir' => 'ltr'],
    'ja' => ['native_name' => '日本語', 'dir' => 'ltr'],
    'ko' => ['native_name' => '한국어', 'dir' => 'ltr'],
    'hi' => ['native_name' => 'हिन्दी', 'dir' => 'ltr'],
    'ru' => ['native_name' => 'Русский', 'dir' => 'ltr'],
    'tr' => ['native_name' => 'Türkçe', 'dir' => 'ltr'],
    'vi' => ['native_name' => 'Tiếng Việt', 'dir' => 'ltr'],
    'th' => ['native_name' => 'ไทย', 'dir' => 'ltr'],
    'id' => ['native_name' => 'Bahasa Indonesia', 'dir' => 'ltr'],
    'nl' => ['native_name' => 'Nederlands', 'dir' => 'ltr'],
    'pl' => ['native_name' => 'Polski', 'dir' => 'ltr'],
    'sv' => ['native_name' => 'Svenska', 'dir' => 'ltr'],
    'el' => ['native_name' => 'Ελληνικά', 'dir' => 'ltr'],
    'he' => ['native_name' => 'עברית', 'dir' => 'rtl'],
];
