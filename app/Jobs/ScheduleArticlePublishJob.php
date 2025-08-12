<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScheduleArticlePublishJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 60;
    private Article $article;

    /**
     * Create a new job instance.
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $article = Article::find($this->article->id);
            
            if (!$article) {
                Log::warning('Article not found during scheduled publish', [
                    'article_id' => $this->article->id
                ]);
                return;
            }

            // Solo publicar si está programado y la fecha es correcta
            if ($article->status === 'scheduled' && 
                $article->scheduled_publish_at && 
                $article->scheduled_publish_at->isPast()) {
                
                $article->update([
                    'status' => 'published',
                    'published_at' => now(),
                    'scheduled_publish_at' => null
                ]);

                Log::info('Article published via scheduled job', [
                    'article_id' => $article->id,
                    'title' => $article->title,
                    'published_at' => now()->toDateTimeString()
                ]);
            } else {
                Log::warning('Article not eligible for scheduled publishing', [
                    'article_id' => $article->id,
                    'current_status' => $article->status,
                    'scheduled_publish_at' => $article->scheduled_publish_at?->toDateTimeString(),
                    'is_past_due' => $article->scheduled_publish_at?->isPast()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in scheduled article publish job', [
                'article_id' => $this->article->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'scheduled-publish',
            'article:' . $this->article->id
        ];
    }
}
