<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesOrderOnline;
use App\Models\DetailSalesOrder;
use App\Models\Product;
use App\Models\ProductOnlineMapping;
use App\Models\OnlineShopProvider;
use App\Models\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarketplaceOrderSyncController extends Controller
{
    public function sync(Request $request)
    {
        // 1. Validasi input payload
        $validated = $request->validate([
            'provider_name' => 'required|string', // 'Shopee', 'Lazada', 'TikTok Shop'
            'store_id' => 'required|integer',
            'order_id' => 'required|string',
            'receipt_no' => 'required|string',
            'courier_name' => 'required|string',
            'total_payment' => 'required|numeric',
            'admin_fee' => 'nullable|numeric',
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'document.base64_data' => 'required|string',
            'document.format' => 'required|string|in:pdf,png,jpg,jpeg',
        ]);

        // Cek duplikasi nomor resi
        $exists = SalesOrderOnline::where('receipt_no', $validated['receipt_no'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Order dengan nomor resi {$validated['receipt_no']} sudah pernah disinkronkan."
            ], 422);
        }

        // 2. Cari / Buat Online Shop Provider secara dinamis
        $providerName = trim($validated['provider_name']);
        $provider = OnlineShopProvider::where('name', 'like', '%' . $providerName . '%')->first();
        if (!$provider) {
            $provider = OnlineShopProvider::create([
                'name' => $providerName
            ]);
        }

        // 3. Cari / Buat Delivery Service (Kurir)
        $deliveryService = DeliveryService::where('name', 'like', '%' . $validated['courier_name'] . '%')->first();
        if (!$deliveryService) {
            $deliveryService = DeliveryService::create([
                'name' => $validated['courier_name']
            ]);
        }

        // 4. Pencocokan SKU marketplace ke Product ID Lokal
        $itemsWithProductId = [];
        foreach ($validated['items'] as $item) {
            // Cari dari tabel pemetaan kustom berdasarkan provider & SKU
            $mapping = ProductOnlineMapping::where('online_shop_provider_id', $provider->id)
                ->where('online_sku', $item['sku'])
                ->first();

            if ($mapping) {
                $productId = $mapping->product_id;
            } else {
                // Fallback: Cari langsung di tabel products berdasarkan SKU
                $product = Product::where('sku', $item['sku'])->first();
                if ($product) {
                    $productId = $product->id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "[{$providerName}] SKU '{$item['sku']}' belum dipetakan ke database lokal. Harap hubungkan SKU terlebih dahulu."
                    ], 422);
                }
            }

            $itemsWithProductId[] = [
                'product_id' => $productId,
                'sku' => $item['sku'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }

        // 5. Proses penyimpanan dokumen resi (dipisahkan berdasarkan folder provider)
        $doc = $validated['document'];
        $decodedData = base64_decode($doc['base64_data']);
        
        $sanitizedProvider = Str::slug($providerName);
        $filename = 'resi_' . $validated['order_id'] . '_' . time() . '.' . $doc['format'];
        $relativeFolder = 'images/Online/Payment/' . $sanitizedProvider;
        $fullPath = $relativeFolder . '/' . $filename;

        Storage::disk('public')->put($fullPath, $decodedData);

        // 6. DB Transaction untuk konsistensi data
        return DB::transaction(function () use ($validated, $provider, $deliveryService, $fullPath, $itemsWithProductId, $providerName) {
            
            // Buat SalesOrder (Online)
            $order = new SalesOrderOnline();
            $order->for = '3'; // Online
            $order->delivery_date = now()->toDateString();
            $order->online_shop_provider_id = $provider->id;
            $order->delivery_service_id = $deliveryService->id;
            $order->store_id = $validated['store_id'];
            $order->receipt_no = $validated['receipt_no'];
            $order->image_payment = $fullPath; 
            $order->payment_status = 2; // Paid
            $order->delivery_status = 4; // Siap Dikirim
            $order->total_price = $validated['total_payment'];
            $order->admin_fee = $validated['admin_fee'] ?? 0;
            $order->notes = "{$providerName} Order ID: " . $validated['order_id'];
            $order->ordered_by_id = auth()->id() ?? 1;
            $order->save();

            // Masukkan Detail Barang
            foreach ($itemsWithProductId as $item) {
                $detail = new DetailSalesOrder();
                $detail->sales_order_id = $order->id;
                $detail->product_id = $item['product_id'];
                $detail->quantity = $item['quantity'];
                $detail->unit_price = $item['price'];
                $detail->subtotal_price = $item['quantity'] * $item['price'];
                $detail->save();
            }

            return response()->json([
                'success' => true,
                'message' => "Order {$providerName} berhasil disinkronkan.",
                'data' => [
                    'sales_order_id' => $order->id,
                    'receipt_no' => $order->receipt_no,
                    'resi_path' => $order->image_payment
                ]
            ], 201);
        });
    }
}
