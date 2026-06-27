<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SampleCoverArt
{
    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public static function palette(string $category): array
    {
        return match (Str::slug($category)) {
            'research' => ['#132E54', '#1a2744', '#ffffff'],
            'training-materials' => ['#8D1436', '#6b1029', '#ffffff'],
            'education' => ['#FFB61C', '#e5a419', '#132E54'],
            default => ['#00563F', '#044532', '#ffffff'],
        };
    }

    public static function ensure(
        string $slug,
        string $title,
        string $category,
        string $typeSlug,
        ?string $fileType = null,
    ): string {
        $relativeDirectory = 'covers/samples';
        $directory = storage_path('app/public/'.$relativeDirectory);
        File::ensureDirectoryExists($directory);

        $filename = $slug.'.svg';
        $absolutePath = $directory.'/'.$filename;
        $iconKey = self::resolveIconKey($typeSlug, $fileType);

        File::put($absolutePath, self::svg($title, $category, $iconKey));

        return '/storage/'.$relativeDirectory.'/'.$filename;
    }

    private static function resolveIconKey(string $typeSlug, ?string $fileType): string
    {
        if ($fileType) {
            $iconKey = match (strtolower($fileType)) {
                'pdf' => 'document',
                'epub' => 'books',
                'mp4', 'mov', 'webm' => 'video',
                'mp3', 'wav', 'm4a' => 'audio',
                'ppt', 'pptx' => 'presentation',
                default => null,
            };

            if ($iconKey) {
                return $iconKey;
            }
        }

        return match (true) {
            str_contains($typeSlug, 'video') => 'video',
            str_contains($typeSlug, 'audio') => 'audio',
            str_contains($typeSlug, 'ebook') => 'books',
            str_contains($typeSlug, 'research-paper') => 'academic-cap',
            str_contains($typeSlug, 'journal') => 'newspaper',
            str_contains($typeSlug, 'presentation') => 'presentation',
            default => 'document',
        };
    }

    private static function svg(string $title, string $category, string $iconKey): string
    {
        [$top, $bottom, $textColor] = self::palette($category);
        $lines = self::wrapTitle($title);
        $typeLabel = self::typeLabel($iconKey);
        $categoryLabel = htmlspecialchars(strtoupper($category), ENT_QUOTES | ENT_XML1);
        $lineElements = '';

        $startY = count($lines) === 1 ? 430 : (count($lines) === 2 ? 400 : 372);

        foreach ($lines as $index => $line) {
            $y = $startY + ($index * 42);
            $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_XML1);
            $lineElements .= <<<SVG
  <text x="300" y="{$y}" text-anchor="middle" fill="{$textColor}" font-family="'Agora Display', 'Segoe UI', sans-serif" font-size="34" font-weight="700">{$escaped}</text>

SVG;
        }

        $iconMarkup = self::heroIconMarkup($iconKey, $top);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800" role="img" aria-label="{$categoryLabel}">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$top}"/>
      <stop offset="100%" stop-color="{$bottom}"/>
    </linearGradient>
    <filter id="soft" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.18"/>
    </filter>
  </defs>
  <rect width="600" height="800" fill="url(#bg)"/>
  <circle cx="520" cy="120" r="140" fill="#ffffff" opacity="0.06"/>
  <circle cx="80" cy="700" r="180" fill="#ffffff" opacity="0.05"/>
  <rect x="48" y="48" width="504" height="704" rx="28" fill="#ffffff" opacity="0.08"/>
  <g filter="url(#soft)" transform="translate(300 250)">
    {$iconMarkup}
  </g>
  {$lineElements}
  <rect x="48" y="688" width="120" height="36" rx="18" fill="#ffffff" opacity="0.92"/>
  <text x="108" y="712" text-anchor="middle" fill="{$top}" font-family="'Agora Sans', 'Segoe UI', sans-serif" font-size="16" font-weight="700">{$typeLabel}</text>
  <text x="300" y="748" text-anchor="middle" fill="{$textColor}" opacity="0.82" font-family="'Agora Sans', 'Segoe UI', sans-serif" font-size="15" font-weight="600" letter-spacing="0.12em">{$categoryLabel}</text>
</svg>
SVG;
    }

    private static function typeLabel(string $iconKey): string
    {
        return match ($iconKey) {
            'video' => 'VIDEO',
            'audio' => 'AUDIO',
            'books' => 'EBOOK',
            default => 'PDF',
        };
    }

    private static function heroIconMarkup(string $iconKey, string $iconColor): string
    {
        $path = self::heroIconPath($iconKey);
        $stroke = htmlspecialchars($iconColor, ENT_QUOTES | ENT_XML1);

        return <<<SVG
<circle r="72" fill="#ffffff" opacity="0.96"/>
<g transform="translate(-42 -42) scale(3.5)" fill="none" stroke="{$stroke}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  <path d="{$path}"/>
</g>
SVG;
    }

    private static function heroIconPath(string $iconKey): string
    {
        return match ($iconKey) {
            'video' => 'm15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z',
            'audio' => 'M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.592-.712-1.593-1.593v-4.286c0-.88.713-1.592 1.593-1.593h2.24Z',
            'books' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
            'academic-cap' => 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
            'newspaper' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z',
            'presentation' => 'M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3.75M9 16.5v1.875c0 .621.504 1.125 1.125 1.125h3.75c.621 0 1.125-.504 1.125-1.125V16.5m-9 0h9',
            'archive' => 'm20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
            default => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        };
    }

    /**
     * @return list<string>
     */
    private static function wrapTitle(string $title): array
    {
        $words = preg_split('/\s+/', trim($title)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;

            if (strlen($candidate) > 24 && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return array_slice($lines, 0, 3);
    }
}
