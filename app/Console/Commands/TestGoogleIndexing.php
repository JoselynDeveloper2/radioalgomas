<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client as GoogleClient;
use Google\Service\Indexing as GoogleIndexingService;
use Google\Service\Indexing\UrlNotification;
use Illuminate\Support\Facades\Log;

class TestGoogleIndexing extends Command
{
    protected $signature = 'google:test-indexing {url?}';
    protected $description = 'Test Google Indexing API with a specific URL';

    public function handle()
    {
        $url = $this->argument('url') ?: 'https://tucanaltv.tv/articulos/test-article';
        
        $this->info("Testing Google Indexing API with URL: {$url}");

        try {
            $client = $this->getGoogleClient();
            
            if (!$client) {
                $this->error('Google client could not be initialized. Check your service account configuration.');
                return 1;
            }

            $this->info('✓ Google client initialized successfully');

            $indexingService = new GoogleIndexingService($client);
            
            $urlNotification = new UrlNotification();
            $urlNotification->setUrl($url);
            $urlNotification->setType('URL_UPDATED');

            $this->info('Sending indexing notification...');
            
            $response = $indexingService->urlNotifications->publish($urlNotification);

            $this->info('✓ Indexing notification sent successfully!');
            $this->line("Response: " . json_encode($response));
            $this->line("URL: {$url}");

            return 0;

        } catch (\Google\Service\Exception $e) {
            $this->error("Google API Error:");
            $this->error("Code: " . $e->getCode());
            $this->error("Message: " . $e->getMessage());
            
            $errors = $e->getErrors();
            if (!empty($errors)) {
                $this->error("Details:");
                foreach ($errors as $error) {
                    $this->error("  - {$error['message']} (Domain: {$error['domain']}, Reason: {$error['reason']})");
                }
            }
            
            return 1;
            
        } catch (\Exception $e) {
            $this->error("General Error: " . $e->getMessage());
            return 1;
        }
    }

    private function getGoogleClient(): ?GoogleClient
    {
        try {
            $serviceAccountJson = config('services.google.indexing.service_account_json');
            $serviceAccountPath = config('services.google.indexing.service_account_path');
            $scopes = config('services.google.indexing.scopes', ['https://www.googleapis.com/auth/indexing']);

            if (!$serviceAccountJson && !$serviceAccountPath) {
                $this->error('Google Indexing service account credentials not configured');
                return null;
            }

            $client = new GoogleClient();
            $client->setScopes($scopes);

            if ($serviceAccountJson) {
                $credentials = json_decode($serviceAccountJson, true);
                if (!$credentials) {
                    $this->error('Invalid JSON format in GOOGLE_INDEXING_SERVICE_ACCOUNT_JSON');
                    return null;
                }
                $client->setAuthConfig($credentials);
                $this->info('Using service account JSON string');
            } elseif ($serviceAccountPath) {
                // Si la ruta es absoluta, usarla tal como está
                if (str_starts_with($serviceAccountPath, '/') || str_contains($serviceAccountPath, ':\\')) {
                    $fullPath = $serviceAccountPath;
                } else {
                    // Si es relativa y empieza con 'storage/', usar base_path()
                    if (str_starts_with($serviceAccountPath, 'storage/')) {
                        $fullPath = base_path($serviceAccountPath);
                    } else {
                        // Si es solo el archivo, asumir que está en storage/app/
                        $fullPath = storage_path('app/' . ltrim($serviceAccountPath, '/'));
                    }
                }
                
                if (file_exists($fullPath)) {
                    $client->setAuthConfig($fullPath);
                    $this->info("Using service account file: {$fullPath}");
                } else {
                    $this->error("Service account file not found: {$fullPath}");
                    return null;
                }
            }

            $client->useApplicationDefaultCredentials();
            return $client;

        } catch (\Exception $e) {
            $this->error('Error configuring Google client: ' . $e->getMessage());
            return null;
        }
    }
}