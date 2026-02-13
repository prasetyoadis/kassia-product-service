<?php

namespace App\Services;

use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductImageService
{
    public function store(string $productId, string $productName, UploadedFile $image, ?string $alt): ProductImage
    {
        DB::beginTransaction();

        try {
            $hasImage = ProductImage::where('product_id', $productId)->exists();

            $alt = $alt ?? $productName;

            $path = $image->store(
                'products/'.$productId,
                'cloudflare'
            );

            /** @var $url env('FILESYSTEM_DISK') Storage::disk('cloudflare') */
            $url = Storage::url($path);

            $productImage = ProductImage::create([
                'product_id' => $productId,
                'url' => $url,
                'alt' => $alt,
                'is_default' => !$hasImage, // gambar pertama auto default
            ]);

            DB::commit();

            Log::info('kassia-product-service.ProductImageController@store.ProductImageService@store success', [
                'service' => 'ProductImageService',
                'action'  => 'store',
                'payload' => [
                    'product_id' => $productId,
                    'url' => $url,
                    'alt' => $alt,
                    'is_default' => !$hasImage,
                ],
            ]);

            return $productImage;

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductImageController@store.ProductImageService.store failed', [
                'service' => 'ProductImageService',
                'action'  => 'store',
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function update(string $productId, ProductImage $productImage, UploadedFile $image, ?string $alt): ProductImage 
    {
        DB::beginTransaction();

        try {
            if (!$productImage) {
                throw new \RuntimeException('PRODUCT_IMAGE_NOT_FOUND');
            }

            # hapus file lama
            $this->deleteFromCloudflare($productImage->url);
            
            $oldUrl = $productImage->url;
            $alt = $alt ?? $productImage->alt;
            
            # upload file baru
            $path = $image->store(
                'products/'.$productId,
                'cloudflare'
            );

            /** @var newUrl env('FILESYSTEM_DISK') disk('cloudflare') */
            $newUrl = Storage::url($path);

            # update DB (is_default TETAP)
            $productImage->update([
                'url' => $newUrl,
                'alt' => $alt,
            ]);

            DB::commit();

            Log::info('kassia-product-service.ProductImageController@update.ProductImageService@update success', [
                'service' => 'ProductImageService',
                'action'  => 'update',
                'payload' => [
                    'product_id' => $productId,
                    'product_image_id' => $productImage->id,
                    'old_url' => $oldUrl,
                    'new_url' => $newUrl,
                ],
            ]);

            return $productImage->refresh();

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductImageController@update.ProductImageService@update failed', [
                'service' => 'ProductImageService',
                'action' => 'update',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function setDefault(string $productId, ProductImage $image): ProductImage
    {
        DB::beginTransaction();

        try {
            if (!$image) {
                throw new \RuntimeException('PRODUCT_IMAGE_NOT_FOUND');
            }

            # unset default lama
            ProductImage::where('product_id', $productId)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            # set default baru
            $image->update(['is_default' => true]);

            DB::commit();

            Log::info('kassia-product-service.ProductImageController@setDefault.ProductImageService@setDefault success', [
                'service' => 'ProductImageService',
                'action'  => 'setDefault',
                'payload' => [
                    'product_id' => $productId,
                    'product_image_id' => $image->id,
                ],
            ]);

            return $image;

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductImageController@setDefault.ProductImageService@setDefault failed', [
                'service' => 'ProductImageService',
                'action' => 'setDefault',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function destroy(string $productId, ProductImage $image): void
    {
        DB::beginTransaction();
        try {
            if (!$image) {
                throw new \RuntimeException('PRODUCT_IMAGE_NOT_FOUND');
            }

            $wasDefault = $image->is_default;
            $url = $image->url;

            // hapus file di cloudflare
            $this->deleteFromCloudflare($url);

            // hapus data
            $image->delete();

            # kalau default pindah kan default ke gambar lain
            if ($wasDefault) {
                $newDefault = ProductImage::where('product_id', $productId)
                    ->orderBy('created_at')
                    ->first();

                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            DB::commit();

            Log::info('kassia-product-service.ProductImageController@destroy.ProductImageService@destroy success', [
                'service' => 'ProductImageService',
                'action' => 'destroy',
                'payload' => [
                    'product_id' => $productId,
                    'product_image_id' => $image->id,
                    'was_default' => $wasDefault,
                ],
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('kassia-product-service.ProductImageController@destroy.ProductImageService@destroy failed', [
                'service' => 'ProductImageService',
                'action' => 'destroy',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        };
    }

    private function deleteFromCloudflare(string $url): void
    {
        $disk = Storage::disk('cloudflare');

        # ambil path relatif dari URL CDN
        $baseUrl = rtrim(config('filesystems.disks.cloudflare.url'), '/');
        $path = ltrim(str_replace($baseUrl, '', $url), '/');

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }
}
