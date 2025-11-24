<?php

declare(strict_types=1);

namespace OfTheWildfire\FilamentInlineEditColumn\Filament\Table\Columns;

use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;

class SmartLogoColumn extends ImageColumn
{
    protected string | \Closure | null $domainAttribute = null;
    protected string | \Closure | null $nameAttribute = null;
    protected bool $showInitials = true;
    protected bool $tryFavicon = true;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->getStateUsing(function (Model $record): string {
            // Always fall back to initials for now (validation was too complex)
            return $this->getInitialsUrl($record);
        });
    }

    public function domainAttribute(string | \Closure | null $attribute): static
    {
        $this->domainAttribute = $attribute;
        return $this;
    }

    public function nameAttribute(string | \Closure | null $attribute): static
    {
        $this->nameAttribute = $attribute;
        return $this;
    }

    public function showInitials(bool $show = true): static
    {
        $this->showInitials = $show;
        return $this;
    }

    public function tryFavicon(bool $try = true): static
    {
        $this->tryFavicon = $try;
        return $this;
    }



    protected function getValidFaviconUrl(Model $record): ?string
    {
        $domain = $this->getDomainFromRecord($record);
        

        
        if (!$domain) {
            return null;
        }
        
        // Try different favicon services and validate size
        $faviconServices = [
            "https://www.google.com/s2/favicons?domain={$domain}&sz=32",
            "https://favicon.im/{$domain}?larger=true",
        ];
        
        foreach ($faviconServices as $faviconUrl) {
            $isValid = $this->isValidFavicon($faviconUrl);
            

            
            if ($isValid) {
                return $faviconUrl;
            }
        }
        
        return null;
    }
    
    protected function getDomainFromRecord(Model $record): ?string
    {
        $domainField = $this->evaluate($this->domainAttribute) ?? 'domain';
        $domain = $record->{$domainField} ?? null;
        
        if (!$domain) {
            // Try to get domain from custom fields
            if (method_exists($record, 'getCustomFieldValue')) {
                try {
                    $customFields = $record->customFields()->get();
                    $domainField = $customFields->where('code', 'domain_name')->first();
                    if ($domainField) {
                        $domain = $record->getCustomFieldValue($domainField);
                    }
                } catch (\Exception $e) {
                    // Ignore if custom fields relationship doesn't exist or fails
                }
            }
        }
        
        return $domain ? $this->cleanDomain($domain) : null;
    }
    
    protected function isValidFavicon(string $url): bool
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'HEAD',
                    'timeout' => 2, // 2 second timeout
                    'user_agent' => 'Mozilla/5.0 (compatible; FaviconChecker/1.0)',
                ]
            ]);
            
            $headers = @get_headers($url, true, $context);
            
            if (!$headers || !isset($headers[0]) || strpos($headers[0], '200') === false) {
                return false;
            }
            
            // Check content type
            $contentType = $headers['Content-Type'] ?? '';
            if (is_array($contentType)) {
                $contentType = $contentType[0];
            }
            
            // Must be an image
            if (!str_contains($contentType, 'image/')) {
                return false;
            }
            
            // SVGs are always good (vector graphics scale perfectly)
            if (str_contains($contentType, 'image/svg') || str_contains($contentType, 'svg+xml')) {
                return true;
            }
            
            // For raster images (PNG, JPG, etc.), check file size
            $contentLength = $headers['Content-Length'] ?? 0;
            if (is_array($contentLength)) {
                $contentLength = $contentLength[0];
            }
            
            // Reject if too small (likely a placeholder) or too large
            $minSize = 100; // 100 bytes minimum
            $maxSize = 50000; // 50KB maximum
            
            return $contentLength >= $minSize && $contentLength <= $maxSize;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    protected function getFaviconUrl(Model $record): ?string
    {
        $domainField = $this->evaluate($this->domainAttribute) ?? 'domain';
        $domain = $record->{$domainField} ?? null;
        
        if (!$domain) {
            // Try to get domain from custom fields
            if (method_exists($record, 'getCustomFieldValue')) {
                try {
                    $customFields = $record->customFields()->get();
                    $domainField = $customFields->where('code', 'domain_name')->first();
                    if ($domainField) {
                        $domain = $record->getCustomFieldValue($domainField);
                    }
                } catch (\Exception $e) {
                    // Ignore if custom fields relationship doesn't exist or fails
                }
            }
        }
        
        if (!$domain) {
            return null;
        }
        
        // Clean up domain
        $domain = $this->cleanDomain($domain);
        
        if (!$domain) {
            return null;
        }
        
        // Try multiple favicon services
        $faviconServices = [
            "https://www.google.com/s2/favicons?domain={$domain}&sz=64",
            "https://favicon.im/{$domain}?larger=true",
            "https://{$domain}/favicon.ico",
        ];
        
        // Return the first service (Google's is most reliable)
        return $faviconServices[0];
    }

    protected function getInitialsUrl(Model $record): string
    {
        $nameField = $this->evaluate($this->nameAttribute) ?? 'name';
        $name = $record->{$nameField} ?? 'Company';
        
        $initials = $this->generateInitials($name);
        $color = $this->generateColor($name);
        
        // Generate a data URL for the initials image
        return $this->generateInitialsDataUrl($initials, $color);
    }

    protected function cleanDomain(string $domain): ?string
    {
        // Remove protocol
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        
        // Remove www
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Remove path and query
        $domain = parse_url('http://' . $domain, PHP_URL_HOST);
        
        // Validate domain
        if (!$domain || !filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            return null;
        }
        
        return $domain;
    }

    protected function generateInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach (array_slice($words, 0, 2) as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return $initials ?: 'C';
    }

    protected function generateColor(string $name): string
    {
        // Generate a consistent color based on the name
        $colors = [
            '#1f2937', '#374151', '#4b5563', '#6b7280',
            '#dc2626', '#ea580c', '#d97706', '#ca8a04',
            '#65a30d', '#16a34a', '#059669', '#0d9488',
            '#0891b2', '#0284c7', '#2563eb', '#4f46e5',
            '#7c3aed', '#a21caf', '#be185d', '#e11d48'
        ];
        
        $hash = crc32($name);
        return $colors[abs($hash) % count($colors)];
    }

    protected function generateInitialsDataUrl(string $initials, string $color): string
    {
        // Create clean, modern SVG for initials (smaller size)
        $svg = '<svg width="48" height="48" xmlns="http://www.w3.org/2000/svg">
            <rect width="48" height="48" fill="' . $color . '" rx="4"/>
            <text x="24" y="30" font-family="-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif" 
                  font-size="18" font-weight="600" text-anchor="middle" fill="white" 
                  dominant-baseline="middle">' . htmlspecialchars($initials) . '</text>
        </svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}