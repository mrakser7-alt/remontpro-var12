<?php
/**
 * Векторные иллюстрации интерьеров для портфолио и баннера.
 * Позволяют сайту выглядеть целостно без внешних изображений.
 */

function room_palette(string $tone): array
{
    return match ($tone) {
        'sage' => ['wall1' => '#e4eadc', 'wall2' => '#cdd9c2', 'floor1' => '#b8a685', 'floor2' => '#9c8766', 'sofa' => '#6f8b6a', 'sofa2' => '#5c7758', 'accent' => '#c98b4b', 'rug' => '#efe8d8', 'glow' => '#fff7e6', 'ink' => '#2c3a2a'],
        'cool' => ['wall1' => '#e2ecf2', 'wall2' => '#c5d8e3', 'floor1' => '#bfae94', 'floor2' => '#a08c70', 'sofa' => '#5d8499', 'sofa2' => '#4a6e82', 'accent' => '#d7a35a', 'rug' => '#f1efe9', 'glow' => '#f0f8ff', 'ink' => '#22323d'],
        'clay' => ['wall1' => '#f5e3d8', 'wall2' => '#e8cbb9', 'floor1' => '#c4a184', 'floor2' => '#a9856a', 'sofa' => '#b57155', 'sofa2' => '#9b5c43', 'accent' => '#4f7d74', 'rug' => '#f4ece2', 'glow' => '#fff4e8', 'ink' => '#3c2620'],
        'sand' => ['wall1' => '#f3ead7', 'wall2' => '#e2d4b9', 'floor1' => '#c8b492', 'floor2' => '#ab9572', 'sofa' => '#a08b64', 'sofa2' => '#877451', 'accent' => '#3f6b63', 'rug' => '#faf5ea', 'glow' => '#fff9ec', 'ink' => '#3a3323'],
        'night' => ['wall1' => '#33404f', 'wall2' => '#26313d', 'floor1' => '#1d2833', 'floor2' => '#151d26', 'sofa' => '#516c80', 'sofa2' => '#3f586b', 'accent' => '#e8a33d', 'rug' => '#2e3b48', 'glow' => '#ffd992', 'ink' => '#e8edf2'],
        default => ['wall1' => '#f7e8cd', 'wall2' => '#eed5b0', 'floor1' => '#c9a173', 'floor2' => '#ad8659', 'sofa' => '#d3a877', 'sofa2' => '#bd8f5f', 'accent' => '#a9552f', 'rug' => '#f7efe2', 'glow' => '#fff8e6', 'ink' => '#3b2a17'],
    };
}

function room_art(string $tone = 'warm', string $icon = '🛋️', bool $hero = false): string
{
    static $n = 0;
    $n++;
    $p = room_palette($tone);
    $u = 'r' . $n;
    $icon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');

    $night = $tone === 'night';

    return <<<SVG
<svg viewBox="0 0 800 500" role="img" aria-label="Иллюстрация интерьера" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="wall{$u}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="{$p['wall1']}"/>
            <stop offset="1" stop-color="{$p['wall2']}"/>
        </linearGradient>
        <linearGradient id="floor{$u}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="{$p['floor1']}"/>
            <stop offset="1" stop-color="{$p['floor2']}"/>
        </linearGradient>
        <linearGradient id="glow{$u}" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="{$p['glow']}"/>
            <stop offset="1" stop-color="{$p['accent']}" stop-opacity=".45"/>
        </linearGradient>
        <linearGradient id="sofa{$u}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="{$p['sofa']}"/>
            <stop offset="1" stop-color="{$p['sofa2']}"/>
        </linearGradient>
    </defs>

    <rect width="800" height="500" fill="url(#wall{$u})"/>
    <rect y="352" width="800" height="148" fill="url(#floor{$u})"/>
    <rect y="352" width="800" height="10" fill="#000" opacity=".08"/>

    <!-- Панель на стене -->
    <g stroke="{$p['ink']}" stroke-opacity=".09" stroke-width="2">
        <line x1="60" y1="0" x2="60" y2="352"/>
        <line x1="360" y1="0" x2="360" y2="352"/>
        <line x1="480" y1="0" x2="480" y2="352"/>
    </g>

    <!-- Окно -->
    <g>
        <rect x="512" y="58" width="238" height="250" rx="10" fill="{$p['ink']}" opacity=".12"/>
        <rect x="524" y="70" width="214" height="226" rx="7" fill="url(#glow{$u})"/>
        <rect x="524" y="70" width="214" height="226" rx="7" fill="none" stroke="{$p['ink']}" stroke-opacity=".28" stroke-width="4"/>
        <line x1="631" y1="70" x2="631" y2="296" stroke="{$p['ink']}" stroke-opacity=".25" stroke-width="4"/>
        <line x1="524" y1="168" x2="738" y2="168" stroke="{$p['ink']}" stroke-opacity=".2" stroke-width="4"/>
        <rect x="512" y="300" width="250" height="14" rx="6" fill="{$p['ink']}" opacity=".18"/>
        <!-- Шторы -->
        <rect x="492" y="54" width="26" height="260" rx="12" fill="{$p['accent']}" opacity=".55"/>
        <rect x="744" y="54" width="26" height="260" rx="12" fill="{$p['accent']}" opacity=".55"/>
    </g>

    <!-- Картина -->
    <g>
        <rect x="92" y="86" width="150" height="112" rx="6" fill="{$p['ink']}" opacity=".14"/>
        <rect x="84" y="78" width="150" height="112" rx="6" fill="{$p['glow']}" opacity=".92"/>
        <circle cx="138" cy="118" r="20" fill="{$p['accent']}" opacity=".7"/>
        <path d="M92 172 L134 132 L164 156 L196 122 L226 172 Z" fill="{$p['ink']}" opacity=".35"/>
    </g>

    <!-- Торшер -->
    <g opacity=".9">
        <line x1="716" y1="330" x2="716" y2="420" stroke="{$p['ink']}" stroke-width="5" stroke-linecap="round" opacity=".4"/>
        <path d="M684 300 L748 300 L734 260 L698 260 Z" fill="{$p['glow']}"/>
        <ellipse cx="716" cy="340" rx="52" ry="16" fill="{$p['glow']}" opacity=".35"/>
    </g>

    <!-- Ковер -->
    <ellipse cx="300" cy="420" rx="252" ry="52" fill="{$p['rug']}" opacity=".85"/>
    <ellipse cx="300" cy="420" rx="252" ry="52" fill="none" stroke="{$p['ink']}" stroke-opacity=".08" stroke-width="3"/>

    <!-- Диван -->
    <g>
        <rect x="78" y="252" width="360" height="126" rx="28" fill="url(#sofa{$u})"/>
        <rect x="98" y="212" width="320" height="86" rx="24" fill="{$p['sofa2']}"/>
        <rect x="118" y="228" width="86" height="58" rx="16" fill="#fff" opacity=".22"/>
        <rect x="222" y="228" width="86" height="58" rx="16" fill="#fff" opacity=".16"/>
        <rect x="326" y="228" width="72" height="58" rx="16" fill="#fff" opacity=".22"/>
        <rect x="56" y="270" width="34" height="104" rx="14" fill="{$p['sofa2']}"/>
        <rect x="426" y="270" width="34" height="104" rx="14" fill="{$p['sofa2']}"/>
        <rect x="108" y="372" width="14" height="22" rx="5" fill="{$p['ink']}" opacity=".35"/>
        <rect x="398" y="372" width="14" height="22" rx="5" fill="{$p['ink']}" opacity=".35"/>
        <rect x="150" y="214" width="64" height="54" rx="14" fill="{$p['accent']}" opacity=".85" transform="rotate(-8 182 241)"/>
    </g>

    <!-- Журнальный столик -->
    <g>
        <ellipse cx="268" cy="432" rx="104" ry="20" fill="{$p['ink']}" opacity=".10"/>
        <rect x="196" y="392" width="150" height="12" rx="6" fill="{$p['ink']}" opacity=".55"/>
        <rect x="212" y="404" width="8" height="32" rx="4" fill="{$p['ink']}" opacity=".4"/>
        <rect x="322" y="404" width="8" height="32" rx="4" fill="{$p['ink']}" opacity=".4"/>
        <rect x="252" y="375" width="34" height="17" rx="4" fill="{$p['accent']}"/>
        <rect x="292" y="379" width="24" height="13" rx="4" fill="{$p['ink']}" opacity=".4"/>
    </g>

    <!-- Растение -->
    <g>
        <path d="M638 392 C620 350 626 320 648 298 C660 330 660 360 650 392 Z" fill="{$p['sofa2']}" opacity=".8"/>
        <path d="M646 394 C666 352 690 336 706 332 C700 366 678 388 656 398 Z" fill="{$p['sofa']}" opacity=".9"/>
        <path d="M636 396 C610 372 596 350 596 330 C620 340 636 366 642 390 Z" fill="{$p['sofa2']}" opacity=".65"/>
        <rect x="620" y="392" width="46" height="52" rx="8" fill="{$p['accent']}" opacity=".85"/>
      </g>

    <!-- Плашка с иконкой -->
    <g transform="translate(40, 40)">
        <rect width="56" height="56" rx="18" fill="#ffffff" opacity=".82"/>
        <text x="28" y="38" text-anchor="middle" font-size="28" font-family="Apple Color Emoji, Segoe UI Emoji, sans-serif">{$icon}</text>
    </g>
</svg>
SVG;
}
