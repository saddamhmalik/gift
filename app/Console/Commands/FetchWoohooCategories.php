<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\WoohooClient;
use Illuminate\Console\Command;

class FetchWoohooCategories extends Command
{
    protected $signature = 'giftbox:fetch-categories
                            {--clear-token : Clear cached Bearer token before fetch}';

    protected $description = 'Fetch categories from Woohoo API and store in database';

    public function handle(): int
    {
        if ($this->option('clear-token')) {
            WoohooClient::fromConfig()->clearCachedToken();
        }

        $client = WoohooClient::fromConfig();
        $path = config('woohoo.endpoints.categories', '/rest/v3/catalog/categories');
        $response = $client->get($path);

        if (! $response->successful()) {
            $this->error('Failed to fetch categories. HTTP ' . $response->status());
            $this->line($response->body());
            return self::FAILURE;
        }

        $data = $response->json();
        if ($data === null) {
            $this->error('Invalid JSON response');
            return self::FAILURE;
        }

        if (isset($data['code'], $data['message']) && ! isset($data['id'], $data['subcategories'])) {
            $this->error($data['message'] ?? 'API error');
            return self::FAILURE;
        }
        
        $synced = $this->syncRecursive($data, null);
        $this->info("Fetched and stored {$synced} categories.");

        return self::SUCCESS;
    }

    protected function syncRecursive(array $node, ?int $parentId): int
    {
        $payload = $this->normalize($node, $parentId);
        if (empty($payload['name'])) {
            return 0;
        }

        $externalId = $payload['external_id'];
        unset($payload['external_id']);
        $slug = $payload['slug'];
        $parentId = $payload['parent_id'];
        unset($payload['parent_id']);

        Category::updateOrCreate(
            ['external_id' => $externalId],
            array_merge($payload, ['slug' => $slug, 'parent_id' => $parentId])
        );
        $count = 1;

        $subcategories = $node['subcategories'] ?? [];
        if (! is_array($subcategories)) {
            return $count;
        }

        $ourCategory = Category::where('external_id', (string) ($node['id'] ?? ''))->first();
        $ourId = $ourCategory?->id;

        foreach ($subcategories as $sub) {
            if (is_array($sub)) {
                $count += $this->syncRecursive($sub, $ourId ?? $parentId);
            }
        }

        return $count;
    }

    protected function normalize(array $item, ?int $parentId): array
    {
        $id = $item['id'] ?? $item['categoryId'] ?? null;
        $id = $id !== null ? (string) $id : null;
        $name = $item['name'] ?? $item['categoryName'] ?? $item['title'] ?? '';
        $url = $item['url'] ?? $item['slug'] ?? '';
        $slug = $id !== null ? 'woohoo-cat-' . $id : (trim((string) $url, '/') ?: 'cat-' . uniqid());
        $images = $item['images'] ?? [];
        $imageUrl = is_array($images) ? ($images['image'] ?? $images['thumbnail'] ?? null) : null;

        $payload = [
            'external_id' => $id,
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'description' => $item['description'] ?? null,
            'image_url' => $imageUrl,
            'sort_order' => (int) ($item['subcategoriesCount'] ?? $item['sort_order'] ?? $item['sortOrder'] ?? 0),
            'is_active' => true,
        ];

        if (isset($item['colorCode'])) {
            $payload['color_code'] = $item['colorCode'];
        }
        if (isset($item['offerDescription'])) {
            $payload['offer_description'] = $item['offerDescription'];
        }

        return $payload;
    }
}
