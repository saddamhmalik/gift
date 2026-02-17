<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class WoohooProductDetailService
{
    public function __construct(
        protected WoohooClient $client
    ) {}

    public function getProductBySku(string $sku): ?array
    {
        $basePath = config('woohoo.endpoints.product', '/rest/v3/catalog/products');
        $path = rtrim($basePath, '/') . '/' . rawurlencode($sku);

        $response = $this->client->get($path);

        if (! $response->successful()) {
            if ($response->status() === 400) {
                $data = $response->json();
                Log::warning('Woohoo product detail failed', ['sku' => $sku, 'message' => $data['message'] ?? 'Invalid product SKU']);
            } else {
                Log::warning('Woohoo product detail failed', ['sku' => $sku, 'status' => $response->status(), 'body' => $response->body()]);
            }
            return null;
        }

        $data = $response->json();
        return is_array($data) ? $data : null;
    }

    public function syncProductDetails(Product $product): bool
    {
        $sku = $product->external_id;
        if (empty($sku)) {
            return false;
        }

        $data = $this->getProductBySku($sku);
        if ($data === null) {
            return false;
        }

        $product->update($this->mapDetailToProduct($product, $data));
        return true;
    }

    public function syncAll(bool $clearToken = false): array
    {
        if ($clearToken) {
            $this->client->clearCachedToken();
        }

        $products = Product::orderBy('id')->get();
        $synced = 0;
        $failed = 0;

        foreach ($products as $product) {
            if ($this->syncProductDetails($product)) {
                $synced++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $failed === 0,
            'synced' => $synced,
            'failed' => $failed,
        ];
    }

    protected function mapDetailToProduct(Product $product, array $data): array
    {
        $price = $data['price'] ?? [];
        $priceArr = is_array($price) ? $price : [];
        $currency = $priceArr['currency'] ?? [];
        $currencyArr = is_array($currency) ? $currency : [];
        $min = $priceArr['min'] ?? null;
        $max = $priceArr['max'] ?? null;
        $denominations = $priceArr['denominations'] ?? null;

        $images = $data['images'] ?? [];
        $imagesArr = is_array($images) ? $images : [];
        $tnc = $data['tnc'] ?? [];
        $tncArr = is_array($tnc) ? $tnc : [];

        $payload = [
            'name' => $data['name'] ?? $product->name,
            'description' => $data['description'] ?? $product->description,
            'offer_short_desc' => $data['offerShortDesc'] ?? $product->offer_short_desc,
            'currency_code' => $currencyArr['code'] ?? $product->currency_code,
            'min_price' => $min !== null && $min !== '' ? (float) $min : $product->min_price,
            'max_price' => $max !== null && $max !== '' ? (float) $max : $product->max_price,
            'denominations' => is_array($denominations) ? $denominations : null,
            'price_type' => $priceArr['type'] ?? $priceArr['price'] ?? null,
            'image_url' => $imagesArr['base'] ?? $product->image_url,
            'thumbnail_url' => $imagesArr['thumbnail'] ?? $product->thumbnail_url,
            'related_product_options' => $data['relatedProductOptions'] ?? $product->related_product_options,
            'corporate_discounts' => $data['corporateDiscounts'] ?? $product->corporate_discounts,
            'product_type' => $data['type'] ?? null,
            'purchaser_limit' => $data['purchaserLimit'] ?? null,
            'purchaser_description' => $data['purchaserDescription'] ?? null,
            'tnc_link' => $tncArr['link'] ?? null,
            'tnc_content' => $tncArr['content'] ?? null,
            'woohoo_attributes' => $data,
        ];

        return $payload;
    }
}
