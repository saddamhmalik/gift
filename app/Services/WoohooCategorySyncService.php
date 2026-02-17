<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Log;

class WoohooCategorySyncService
{
    public function __construct(
        protected WoohooClient $client
    ) {}

    public function sync(bool $clearToken = false): array
    {
        if ($clearToken) {
            $this->client->clearCachedToken();
        }

        $path = config('woohoo.endpoints.categories', '/rest/v3/catalog/categories');
        $response = $this->client->get($path);

        if (! $response->successful()) {
            Log::warning('Woohoo categories fetch failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'message' => 'Failed to fetch categories', 'status' => $response->status(), 'synced' => 0];
        }

        $data = $response->json();
        if ($data === null) {
            return ['success' => false, 'message' => 'Invalid JSON response', 'synced' => 0];
        }

        if (isset($data['code'], $data['message']) && ! isset($data['id'], $data['subcategories'])) {
            return ['success' => false, 'message' => $data['message'] ?? 'API error', 'synced' => 0];
        }

        $synced = $this->syncRecursive($data, null);

        return ['success' => true, 'synced' => $synced];
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

        $ourCategory = Category::updateOrCreate(
            ['external_id' => $externalId],
            array_merge($payload, ['slug' => $slug, 'parent_id' => $parentId, 'last_synced_at' => now()])
        );
        $count = 1;

        $subcategories = $node['subcategories'] ?? [];
        if (! is_array($subcategories)) {
            return $count;
        }

        $ourId = $ourCategory->id;

        foreach ($subcategories as $sub) {
            if (is_array($sub)) {
                $count += $this->syncRecursive($sub, $ourId);
            }
        }

        return $count;
    }

    protected function normalize(array $item, ?int $parentId): array
    {
        $id = $item['id'] ?? $item['categoryId'] ?? null;
        $id = $id !== null ? (string) $id : null;
        $name = $item['name'] ?? $item['categoryName'] ?? $item['title'] ?? '';
        $url = isset($item['url']) ? (string) $item['url'] : null;
        $slug = $id !== null ? 'woohoo-cat-' . $id : (trim((string) ($url ?? ''), '/') ?: 'cat-' . uniqid());
        $images = $item['images'] ?? [];
        $imageUrl = is_array($images) ? ($images['image'] ?? null) : null;
        $thumbnailUrl = is_array($images) ? ($images['thumbnail'] ?? null) : null;

        $payload = [
            'external_id' => $id,
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'url' => $url,
            'description' => $item['description'] ?? null,
            'short_description' => $item['shortDescription'] ?? null,
            'canonical_url' => $item['canonicalUrl'] ?? null,
            'image_url' => $imageUrl,
            'thumbnail_url' => $thumbnailUrl,
            'color_code' => $item['colorCode'] ?? null,
            'bg_color_code' => $item['bgColorCode'] ?? null,
            'offer_description' => $item['offerDescription'] ?? null,
            'meta_index' => isset($item['metaIndex']) ? (bool) $item['metaIndex'] : null,
            'meta_keyword' => $item['metaKeyword'] ?? null,
            'page_title' => $item['pageTitle'] ?? null,
            'meta_description' => $item['metaDescription'] ?? null,
            'sub_category_filter' => (bool) ($item['subCategoryFilter'] ?? false),
            'subcategories_count' => (int) ($item['subcategoriesCount'] ?? 0),
            'sort_order' => (int) ($item['subcategoriesCount'] ?? $item['sort_order'] ?? $item['sortOrder'] ?? 0),
            'is_active' => true,
        ];

        return $payload;
    }
}
