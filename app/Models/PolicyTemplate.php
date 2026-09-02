<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyTemplate extends Model
{
    protected $fillable = [
        'asset_type_id',
        'group_key',
        'group_label',
        'key',
        'label',
        'input_type',
        'radio_group',
        'radio_value',
        'parent_key',
        'sort_order',
    ];

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    /**
     * Ambil template untuk tipe aset tertentu, dikelompokkan per group.
     * Hanya mengambil template dengan asset_type_id = $typeId (tidak global).
     */
    public static function getGroupedForType(int $typeId): array
    {
        $items = static::where('asset_type_id', $typeId)
            ->orderBy('group_key')
            ->orderBy('sort_order')
            ->get();

        $groups = [];
        foreach ($items as $item) {
            $gk = $item->group_key;
            if (!isset($groups[$gk])) {
                $groups[$gk] = [
                    'group_key'   => $gk,
                    'group_label' => $item->group_label,
                    'always_open' => $gk === 'persyaratan',
                    'items'       => [],
                ];
            }
            $groups[$gk]['items'][] = [
                'key'         => $item->key,
                'label'       => $item->label,
                'input_type'  => $item->input_type,
                'radio_group' => $item->radio_group,
                'radio_value' => $item->radio_value,
                'parent_key'  => $item->parent_key,
            ];
        }

        return array_values($groups);
    }
}
