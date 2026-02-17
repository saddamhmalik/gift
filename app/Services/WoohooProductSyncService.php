<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WoohooProductSyncService
{
    public const DEFAULT_LIMIT = 500;

    public function __construct(
        protected WoohooClient $client
    ) {}

    public function sync(bool $clearToken = false): array
    {
        if ($clearToken) {
            $this->client->clearCachedToken();
        }

        $categories = Category::whereNotNull('external_id')->orderBy('id')->get();
        $totalSynced = 0;
        $errors = [];

        foreach ($categories as $category) {
            $result = $this->syncProductsForCategory($category);
            $totalSynced += $result['synced'];
            if (isset($result['error'])) {
                $errors[] = "Category {$category->name} ({$category->external_id}): {$result['error']}";
            }
        }

        return [
            'success' => empty($errors),
            'synced' => $totalSynced,
            'message' => empty($errors) ? null : implode('; ', $errors),
        ];
    }

    public function syncProductsForCategory(Category $category): array
    {
        $basePath = config('woohoo.endpoints.category_products', '/rest/v3/catalog/categories');
        $path = rtrim($basePath, '/') . '/' . $category->external_id . '/products';
        $offset = 0;
        $limit = self::DEFAULT_LIMIT;
        $synced = 0;

        do {
            $response = $this->client->get($path, ['offset' => $offset, 'limit' => $limit]);

            if (! $response->successful()) {
                if ($response->status() === 400) {
                    $data = $response->json();
                    $msg = $data['message'] ?? 'Invalid category';
                    Log::warning('Woohoo category products failed', ['category' => $category->external_id, 'status' => $response->status(), 'body' => $response->body()]);
                    return ['synced' => $synced, 'error' => $msg];
                }
                Log::warning('Woohoo category products failed', ['category' => $category->external_id, 'status' => $response->status(), 'body' => $response->body()]);
                return ['synced' => $synced, 'error' => 'HTTP ' . $response->status()];
            }

            $data = $response->json();
            if ($data === null) {
                return ['synced' => $synced, 'error' => 'Invalid JSON response'];
            }

            if (isset($data['code'], $data['message'])) {
                return ['synced' => $synced, 'error' => $data['message']];
            }

            $products = $data['products'] ?? [];
            if (! is_array($products)) {
                break;
            }

            foreach ($products as $item) {
                $payload = $this->normalizeProduct($item, $category->id);
                if (empty($payload['external_id']) || empty($payload['name'])) {
                    continue;
                }
                Product::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'external_id' => $payload['external_id'],
                    ],
                    $payload
                );
                $synced++;
            }

            $productsCount = (int) ($data['productsCount'] ?? 0);
            $offset += $limit;
            if (count($products) < $limit || $offset >= $productsCount) {
                break;
            }
        } while (true);

        return ['synced' => $synced];
    }

    protected function normalizeProduct(array $item, int $categoryId): array
    {
        $sku = $item['sku'] ?? $item['productId'] ?? null;
        $externalId = $sku !== null ? (string) $sku : null;
        $name = $item['name'] ?? $item['productName'] ?? '';
        $url = $item['url'] ?? null;
        $slug = $externalId ? 'woohoo-' . Str::slug($externalId) : (Str::slug($name) ?: 'product-' . uniqid());

        $currency = $item['currency'] ?? [];
        $currencyCode = is_array($currency) ? ($currency['code'] ?? null) : null;

        $images = $item['images'] ?? [];
        $imageUrl = is_array($images) ? ($images['base'] ?? $images['image'] ?? null) : null;
        $thumbnailUrl = is_array($images) ? ($images['thumbnail'] ?? null) : null;

        $minPrice = isset($item['minPrice']) ? (float) $item['minPrice'] : null;
        $maxPrice = isset($item['maxPrice']) ? (float) $item['maxPrice'] : null;

        $relatedOptions = $item['relatedProductOptions'] ?? null;
        $corporateDiscounts = $item['corporateDiscounts'] ?? null;

        return [
            'category_id' => $categoryId,
            'external_id' => $externalId,
            'name' => $name,
            'slug' => $slug,
            'url' => $url,
            'description' => $item['description'] ?? null,
            'offer_short_desc' => $item['offerShortDesc'] ?? null,
            'currency_code' => $currencyCode,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'image_url' => $imageUrl,
            'thumbnail_url' => $thumbnailUrl,
            'related_product_options' => is_array($relatedOptions) ? $relatedOptions : null,
            'corporate_discounts' => is_array($corporateDiscounts) ? $corporateDiscounts : null,
            'is_active' => true,
        ];
    }
}
