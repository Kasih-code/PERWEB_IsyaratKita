<?php

namespace App\Http\Controllers;

use App\Data\DataDummy;
use App\Models\Donasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class DonasiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('donasi.index', [
            'pilihanNominal' => DataDummy::pilihanNominalDonasi(),
            'nominalTerpilih' => 100000,
            'statusLogin' => (bool) $user,
            'penggunaLogin' => $user,
            'tampilkanModalLogin' => ! $user,
        ]);
    }

    private function konfigurasiMidtrans(): void
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function simpan(Request $request): JsonResponse
    {
        $request->merge([
            'whatsapp' => preg_replace(
                '/[\s-]/',
                '',
                (string) $request->input('whatsapp')
            ),
        ]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp' => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'nominal' => [
                'required',
                'integer',
                Rule::in(DataDummy::pilihanNominalDonasi()),
            ],
        ]);

        $donasi = Donasi::create([
            'kode' => Donasi::buatKode(),
            'user_id' => $request->user()->id,
            'nama' => trim($data['nama']),
            'email' => $data['email'],
            'whatsapp' => $data['whatsapp'],
            'nominal' => (int) $data['nominal'],
            'metode' => 'qris',
            'status' => 'menunggu',
            'simulasi' => false,
        ]);

        try {
            $this->konfigurasiMidtrans();

            $params = [
                'enabled_payments' => ['other_qris'],
                'transaction_details' => [
                    'order_id' => $donasi->kode,
                    'gross_amount' => (int) $donasi->nominal,
                ],
                'customer_details' => [
                    'first_name' => $donasi->nama,
                    'email' => $donasi->email,
                    'phone' => $donasi->whatsapp,
                ],
            ];

            $snap = Snap::createTransaction($params);

            return response()->json([
                'kode' => $donasi->kode,
                'nominal' => $donasi->nominal,
                'snap_token' => $snap->token,
                'client_key' => config('services.midtrans.client_key'),
            ], 201);

        } catch (\Throwable $e) {
            $donasi->update([
                'status' => 'gagal',
            ]);

            Log::error('Gagal membuat transaksi Midtrans', [
                'donasi' => $donasi->kode,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Pembayaran belum dapat dibuat. Silakan coba lagi.',
            ], 500);
        }
    }

    public function notifikasi(Request $request): JsonResponse
    {
        try {
            $payload = $request->json()->all();

            $orderId = $payload['order_id'] ?? null;
            $statusCode = $payload['status_code'] ?? null;
            $grossAmount = $payload['gross_amount'] ?? null;
            $signatureKey = $payload['signature_key'] ?? null;

            if (
                ! $orderId ||
                ! $statusCode ||
                ! $grossAmount ||
                ! $signatureKey
            ) {
                return response()->json([
                    'message' => 'Data notifikasi tidak lengkap.',
                ], 400);
            }

            $serverKey = config('services.midtrans.server_key');

            $signature = hash(
                'sha512',
                $orderId . $statusCode . $grossAmount . $serverKey
            );

            if (! hash_equals($signature, $signatureKey)) {
                return response()->json([
                    'message' => 'Signature tidak valid.',
                ], 403);
            }

            $this->konfigurasiMidtrans();

            // Memeriksa transaksi langsung menggunakan order ID.
            $transaksi = (object) Transaction::status($orderId);

            if ((string) $transaksi->order_id !== (string) $orderId) {
                return response()->json([
                    'message' => 'Data transaksi tidak sesuai.',
                ], 422);
            }

            $donasi = Donasi::where('kode', $orderId)->first();

            if (! $donasi) {
                return response()->json([
                    'message' => 'Donasi tidak ditemukan.',
                ], 404);
            }

            // Memastikan nominal transaksi sesuai.
            $nominalDonasi = number_format(
                (int) $donasi->nominal,
                2,
                '.',
                ''
            );

            $nominalTransaksi = number_format(
                (float) $transaksi->gross_amount,
                2,
                '.',
                ''
            );

            if ($nominalDonasi !== $nominalTransaksi) {
                return response()->json([
                    'message' => 'Nominal transaksi tidak sesuai.',
                ], 422);
            }

            // Donasi yang sudah berhasil tidak diproses ulang.
            if ($donasi->sudahBerhasil()) {
                return response()->json([
                    'message' => 'Donasi sudah berhasil diproses.',
                ]);
            }

            $status = $transaksi->transaction_status;
            $fraud = $transaksi->fraud_status ?? null;

            // Pembayaran berhasil.
            if (
                $status === 'settlement' ||
                ($status === 'capture' && $fraud === 'accept')
            ) {
                $donasi->update([
                    'status' => 'berhasil',
                    'referensi' => $transaksi->transaction_id ?? null,
                    'metode' => $transaksi->payment_type ?? 'qris',
                    'dibayar_pada' => now(),
                ]);
            }

            // Pembayaran gagal, dibatalkan, atau kedaluwarsa.
            elseif (in_array(
                $status,
                ['expire', 'cancel', 'deny', 'failure'],
                true
            )) {
                $donasi->update([
                    'status' => 'gagal',
                    'referensi' => $transaksi->transaction_id ?? null,
                ]);
            }

            return response()->json([
                'message' => 'Notifikasi berhasil diproses.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Notifikasi Midtrans gagal diproses', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Notifikasi gagal diproses.',
            ], 500);
        }
    }

    public function status(Request $request, string $kode): JsonResponse
    {
        $donasi = Donasi::where('kode', $kode)->firstOrFail();

        abort_unless(
            (int) $donasi->user_id === (int) $request->user()->id,
            403
        );

        return response()->json([
            'kode' => $donasi->kode,
            'status' => $donasi->status,
        ]);
    }
}