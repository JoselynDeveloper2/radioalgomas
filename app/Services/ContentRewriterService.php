<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ContentRewriterService
{
    private array $synonyms = [
        // Sustantivos comunes
        'presidente' => ['mandatario', 'dirigente', 'líder político', 'jefe de estado'],
        'gobierno' => ['administración', 'gestión', 'dirección política', 'poder ejecutivo'],
        'partido' => ['organización política', 'agrupación', 'formación política', 'movimiento'],
        'empresa' => ['compañía', 'corporación', 'entidad', 'organización'],
        'país' => ['nación', 'territorio', 'estado', 'república'],
        'ciudad' => ['municipio', 'localidad', 'urbe', 'población'],
        'personas' => ['ciudadanos', 'individuos', 'habitantes', 'población'],
        'trabajo' => ['empleo', 'labor', 'ocupación', 'actividad laboral'],
        'proyecto' => ['iniciativa', 'propuesta', 'plan', 'programa'],
        'sistema' => ['mecanismo', 'estructura', 'organización', 'método'],
        
        // Verbos comunes
        'anunció' => ['declaró', 'informó', 'reveló', 'comunicó'],
        'dijo' => ['expresó', 'manifestó', 'indicó', 'señaló'],
        'confirmó' => ['ratificó', 'validó', 'corroboró', 'verificó'],
        'decidió' => ['optó por', 'resolvió', 'determinó', 'estableció'],
        'presentó' => ['expuso', 'mostró', 'dio a conocer', 'exhibió'],
        'realizó' => ['ejecutó', 'llevó a cabo', 'efectuó', 'desarrolló'],
        'implementó' => ['puso en marcha', 'estableció', 'aplicó', 'ejecutó'],
        
        // Adjetivos
        'importante' => ['relevante', 'significativo', 'fundamental', 'crucial'],
        'nuevo' => ['reciente', 'actual', 'moderno', 'innovador'],
        'grande' => ['amplio', 'extenso', 'considerable', 'significativo'],
        'mejor' => ['superior', 'óptimo', 'excelente', 'destacado'],
        
        // Conectores temporales
        'durante' => ['a lo largo de', 'en el transcurso de', 'mientras', 'en el período de'],
        'después' => ['posteriormente', 'luego', 'a continuación', 'más tarde'],
        'antes' => ['previamente', 'anteriormente', 'con anterioridad', 'primero'],
        'ahora' => ['actualmente', 'en la actualidad', 'hoy en día', 'en estos momentos'],
    ];

    private array $titlePrefixes = [
        'Últimas noticias:',
        'Actualidad:',
        'Información exclusiva:',
        'Se confirma:',
        'Breaking:',
        'Desarrollo:',
        'En directo:',
        'Reportan que',
        'Análisis:',
        'Especial:'
    ];

    private array $introTemplates = [
        'En un desarrollo reciente, {content}',
        'Según información actualizada, {content}',
        'De acuerdo a fuentes oficiales, {content}',
        'En una decisión que marca un hito, {content}',
        'Los más recientes reportes indican que {content}',
        'En una medida sin precedentes, {content}',
        'Tras las últimas evaluaciones, se confirma que {content}',
        'En el marco de los acontecimientos actuales, {content}',
        'Como parte de las acciones implementadas, {content}',
        'En respuesta a la situación actual, {content}',
    ];

    public function rewriteArticle(Article $article): array
    {
        try {
            $originalTitle = $article->title;
            $originalContent = $article->content;
            
            // Guardar contenido original
            $article->original_content = $originalContent;
            
            // Reescribir título
            $rewrittenTitle = $this->rewriteTitle($originalTitle);
            
            // Reescribir contenido
            $rewrittenContent = $this->rewriteContent($originalContent);
            
            // Generar metadata SEO optimizada
            $seoData = $this->generateSEOData($rewrittenTitle, $rewrittenContent, $article);
            
            $result = [
                'original_title' => $originalTitle,
                'rewritten_title' => $rewrittenTitle,
                'original_content' => $originalContent,
                'rewritten_content' => $rewrittenContent,
                'seo_data' => $seoData,
                'word_similarity' => $this->calculateSimilarity($originalContent, $rewrittenContent),
            ];

            Log::info('Content rewritten successfully', [
                'article_id' => $article->id,
                'original_words' => str_word_count(strip_tags($originalContent)),
                'rewritten_words' => str_word_count(strip_tags($rewrittenContent)),
                'similarity' => $result['word_similarity']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error rewriting article content', [
                'article_id' => $article->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function rewriteTitle(string $title): string
    {
        // Remover prefijos comunes de RSS
        $cleanTitle = $this->removeCommonPrefixes($title);
        
        // Aplicar sinónimos
        $rewrittenTitle = $this->applySynonyms($cleanTitle);
        
        // Agregar prefijo aleatorio ocasionalmente
        if (rand(1, 3) === 1) {
            $prefix = $this->titlePrefixes[array_rand($this->titlePrefixes)];
            $rewrittenTitle = $prefix . ' ' . $rewrittenTitle;
        }
        
        return Str::title(trim($rewrittenTitle));
    }

    private function rewriteContent(string $content): string
    {
        // Limpiar HTML y obtener texto plano
        $plainText = strip_tags($content);
        
        // Dividir en párrafos
        $paragraphs = array_filter(explode("\n", $plainText));
        
        if (empty($paragraphs)) {
            return $content;
        }

        $rewrittenParagraphs = [];
        
        foreach ($paragraphs as $index => $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) continue;
            
            if ($index === 0) {
                // Primera oración: agregar introducción
                $template = $this->introTemplates[array_rand($this->introTemplates)];
                $rewrittenParagraph = str_replace('{content}', strtolower($paragraph), $template);
                $rewrittenParagraph = ucfirst($rewrittenParagraph);
            } else {
                // Resto de párrafos: aplicar sinónimos y reestructurar
                $rewrittenParagraph = $this->applySynonyms($paragraph);
                $rewrittenParagraph = $this->restructureSentences($rewrittenParagraph);
            }
            
            $rewrittenParagraphs[] = $rewrittenParagraph;
        }
        
        return implode("\n\n", $rewrittenParagraphs);
    }

    private function applySynonyms(string $text): string
    {
        $words = explode(' ', $text);
        $rewrittenWords = [];
        
        foreach ($words as $word) {
            $cleanWord = strtolower(trim($word, '.,!?;:()[]{}'));
            
            if (isset($this->synonyms[$cleanWord]) && rand(1, 3) === 1) {
                $synonym = $this->synonyms[$cleanWord][array_rand($this->synonyms[$cleanWord])];
                $rewrittenWord = str_replace($cleanWord, $synonym, strtolower($word));
                $rewrittenWords[] = $rewrittenWord;
            } else {
                $rewrittenWords[] = $word;
            }
        }
        
        return implode(' ', $rewrittenWords);
    }

    private function restructureSentences(string $text): string
    {
        $sentences = explode('.', $text);
        $rewrittenSentences = [];
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;
            
            // Cambiar orden de frases ocasionalmente
            if (rand(1, 4) === 1 && str_word_count($sentence) > 10) {
                $sentence = $this->changeWordOrder($sentence);
            }
            
            $rewrittenSentences[] = $sentence;
        }
        
        return implode('. ', $rewrittenSentences) . '.';
    }

    private function changeWordOrder(string $sentence): string
    {
        // Buscar patrones para reordenar
        // Ejemplo: "X anunció que Y" -> "Según X, Y"
        $patterns = [
            '/(.+) anunció que (.+)/' => 'Según $1, $2',
            '/(.+) confirmó que (.+)/' => 'De acuerdo con $1, $2',
            '/(.+) informó que (.+)/' => 'Según informes de $1, $2',
            '/(.+) declaró que (.+)/' => 'En declaraciones, $1 manifestó que $2',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $sentence)) {
                return preg_replace($pattern, $replacement, $sentence);
            }
        }
        
        return $sentence;
    }

    private function removeCommonPrefixes(string $title): string
    {
        $prefixes = [
            'Breaking:',
            'BREAKING:',
            'Urgent:',
            'URGENT:',
            'News:',
            'NOTICIA:',
            'ACTUALIDAD:',
            'ÚLTIMO:',
        ];
        
        foreach ($prefixes as $prefix) {
            if (Str::startsWith($title, $prefix)) {
                return trim(Str::after($title, $prefix));
            }
        }
        
        return $title;
    }

    private function generateSEOData(string $title, string $content, Article $article): array
    {
        $plainContent = strip_tags($content);
        
        return [
            'seo_title' => Str::limit($title, 58),
            'seo_meta_description' => Str::limit($plainContent, 155),
            'seo_canonical_url' => url('/articulos/' . $article->slug),
            'seo_keywords' => $this->extractKeywords($plainContent),
        ];
    }

    private function extractKeywords(string $content): string
    {
        $words = explode(' ', strtolower($content));
        $words = array_filter($words, function($word) {
            return strlen($word) > 4 && !in_array($word, [
                'para', 'este', 'esta', 'desde', 'hasta', 'sobre', 'entre', 'durante',
                'mientras', 'después', 'antes', 'cuando', 'donde', 'como', 'porque'
            ]);
        });
        
        $wordFreq = array_count_values($words);
        arsort($wordFreq);
        
        return implode(', ', array_slice(array_keys($wordFreq), 0, 8));
    }

    private function calculateSimilarity(string $text1, string $text2): float
    {
        $words1 = explode(' ', strtolower(strip_tags($text1)));
        $words2 = explode(' ', strtolower(strip_tags($text2)));
        
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));
        
        return count($intersection) / count($union);
    }

    public function shouldRewrite(Article $article): bool
    {
        // Solo reescribir si no es contenido original
        return !$article->is_original || 
               empty($article->rewritten_content) || 
               $article->rewrite_status === 'failed';
    }
}