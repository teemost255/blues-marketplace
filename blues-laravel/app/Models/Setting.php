<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): ?string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Null means the catalog has not been customized yet (show all products).
     * An empty array is an intentional choice to show no supplier products.
     */
    public static function apiCatalogProductIds(): ?array
    {
        $stored = static::get('api_catalog_product_ids');
        if ($stored === null) {
            return null;
        }

        $ids = json_decode($stored, true);
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $ids),
            fn (int $id) => $id > 0
        )));
    }
}
