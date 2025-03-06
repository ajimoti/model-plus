<?php

declare(strict_types=1);

namespace Vendor\ModelPlus\Helpers;

final class ViewHelpers
{
    public static function getSortIcon(string $column, ?string $currentSort, string $currentDirection = 'asc'): string
    {
        if ($currentSort !== $column) {
            return <<<HTML
                <svg class="w-3 h-3 ml-1.5 opacity-0 group-hover:opacity-50" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
HTML;
        }
        
        if ($currentDirection === 'asc') {
            return <<<HTML
                <svg class="w-3 h-3 ml-1.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
HTML;
        }
        
        return <<<HTML
            <svg class="w-3 h-3 ml-1.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
HTML;
    }
} 