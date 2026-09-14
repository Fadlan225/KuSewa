<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\service_fee as ServiceFee;
use App\Models\asset_type as AssetType;
use Illuminate\Support\Facades\DB;

class PaymentSystemController extends Controller
{
    /**
     * Display the payment system page (service fees management per asset type).
     */
    public function index()
    {
        $assetTypes = AssetType::with('category:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'name'        => $t->name,
                'category'    => $t->category?->name ?? 'Lainnya',
                'allow_units' => (bool) $t->allow_units,
                'default_rental_unit' => $t->default_rental_unit,
                'unit_label'  => in_array($t->name, ['Hotel', 'Kos', 'Guest House', 'Apartemen', 'Homestay']) ? 'Kamar' : 'Unit',
            ]);

        $serviceFees = ServiceFee::with('assetType:id,name')
            ->whereNotNull('asset_type_id')
            ->orderBy('sort_order', 'asc')
            ->get();

        return Inertia::render('admin/PengaturanBiaya/Tarif', [
            'assetTypes'  => $assetTypes,
            'serviceFees' => $serviceFees,
        ]);
    }

    /**
     * Store a newly created service fee in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'scope'         => 'required|in:asset,unit',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'fee_type'      => 'required|in:fixed,percentage',
            'fee_value'     => 'required|numeric|min:0',
        ]);

        $maxSort = ServiceFee::where('asset_type_id', $validated['asset_type_id'])
            ->where('scope', $validated['scope'])
            ->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSort + 1;

        ServiceFee::create($validated);

        return back()->with('success', 'Profil biaya layanan berhasil ditambahkan.');
    }

    /**
     * Update the specified service fee in storage.
     */
    public function update(Request $request, $id)
    {
        $serviceFee = ServiceFee::findOrFail($id);

        $validated = $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'scope'         => 'required|in:asset,unit',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'fee_type'      => 'required|in:fixed,percentage',
            'fee_value'     => 'required|numeric|min:0',
        ]);

        $serviceFee->update($validated);

        return back()->with('success', 'Profil biaya layanan berhasil diperbarui.');
    }

    /**
     * Remove the specified service fee from storage.
     */
    public function destroy($id)
    {
        $serviceFee = ServiceFee::findOrFail($id);

        $remaining = ServiceFee::where('asset_type_id', $serviceFee->asset_type_id)
            ->where('scope', $serviceFee->scope)
            ->count();

        if ($remaining <= 1) {
            return back()->with('error', 'Setiap tipe aset minimal harus memiliki 1 profil biaya aktif.');
        }

        $serviceFee->delete();

        return back()->with('success', 'Profil biaya layanan berhasil dihapus.');
    }

    /**
     * Reorder service fees.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ordered_ids'   => 'required|array',
            'ordered_ids.*' => 'integer|exists:service_fees,id',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->ordered_ids as $index => $id) {
                ServiceFee::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });

        return back()->with('success', 'Prioritas profil biaya diperbarui. Urutan teratas otomatis menjadi tarif yang aktif.');
    }

    /**
     * Reset service fees to default (Rp 5.000) for a given asset type and scope.
     */
    public function resetToDefault(Request $request, $assetTypeId)
    {
        $assetType = AssetType::findOrFail($assetTypeId);
        $scope = $request->input('scope', 'asset');
        if (!in_array($scope, ['asset', 'unit'])) {
            $scope = 'asset';
        }

        DB::transaction(function () use ($assetType, $scope) {
            ServiceFee::where('asset_type_id', $assetType->id)
                ->where('scope', $scope)
                ->delete();

            $isUnit = ($scope === 'unit');
            ServiceFee::create([
                'asset_type_id' => $assetType->id,
                'scope'         => $scope,
                'name'          => $isUnit ? 'Biaya Platform Unit Standar' : 'Biaya Platform Standar',
                'description'   => $isUnit
                    ? 'Biaya layanan dasar per unit/kamar untuk ' . $assetType->name . '.'
                    : 'Biaya layanan dasar untuk transaksi sewa ' . $assetType->name . '.',
                'fee_type'      => 'fixed',
                'fee_value'     => 5000,
                'sort_order'    => 1,
            ]);
        });

        return back()->with('success', 'Biaya layanan untuk ' . $assetType->name . ' (' . ($scope === 'unit' ? 'Unit' : 'Aset') . ') berhasil dikembalikan ke default Rp 5.000.');
    }
}
