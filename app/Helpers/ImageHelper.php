<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Generate a beautiful placeholder image URL based on category
     */
    public static function getPlaceholderImage($category = null, $width = 600, $height = 400): string
    {
        // Color palette for different categories - subtle and professional
        $categoryColors = [
            'noticias' => ['f8fafc', '64748b'],          // Light gray to slate
            'deportes' => ['ecfdf5', '059669'],          // Light green to emerald
            'entretenimiento' => ['fef3c7', 'd97706'],   // Light amber to orange
            'política' => ['ede9fe', '7c3aed'],          // Light purple to violet
            'tecnología' => ['dbeafe', '2563eb'],        // Light blue to blue
            'salud' => ['f0fdf4', '16a34a'],             // Light green to green
            'economía' => ['fef7cd', 'ca8a04'],          // Light yellow to yellow
            'cultura' => ['fce7f3', 'be185d'],           // Light pink to pink
            'internacional' => ['f1f5f9', '475569'],     // Light slate to slate
            'default' => ['f8fafc', '64748b']            // Default neutral
        ];

        $slug = $category ? strtolower($category) : 'default';
        $colors = $categoryColors[$slug] ?? $categoryColors['default'];
        
        // Use a subtle gradient with professional colors
        $bgColor = $colors[0];
        $textColor = $colors[1];
        
        // Create a subtle, professional placeholder
        return "https://placehold.co/{$width}x{$height}/{$bgColor}/{$textColor}?text=" . 
               urlencode(ucfirst($slug === 'default' ? 'Noticia' : $slug));
    }

    /**
     * Generate a beautiful gradient placeholder
     */
    public static function getGradientPlaceholder($width = 600, $height = 400, $seed = null): string
    {
        // Professional gradient combinations
        $gradients = [
            ['from' => 'f1f5f9', 'to' => 'cbd5e1'], // slate
            ['from' => 'f8fafc', 'to' => 'e2e8f0'], // gray
            ['from' => 'fafbfb', 'to' => 'd1d5db'], // neutral
            ['from' => 'f9fafb', 'to' => 'd1d5db'], // warm gray
            ['from' => 'f8f9fa', 'to' => 'dee2e6'], // cool gray
        ];
        
        $gradient = $gradients[($seed ?? rand(0, count($gradients) - 1)) % count($gradients)];
        
        return "https://placehold.co/{$width}x{$height}/{$gradient['from']}/{$gradient['to']}?text=Imagen";
    }

    /**
     * Get placeholder with subtle branding
     */
    public static function getBrandedPlaceholder($width = 600, $height = 400): string
    {
        return "https://placehold.co/{$width}x{$height}/f8fafc/1b3a5c?text=Radio+Algo+M%C3%A1s";
    }
}