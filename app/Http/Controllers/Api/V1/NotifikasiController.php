<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'hanya_belum_dibaca' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $member = $request->user()->member()->first();

        $paginator = $member->notifikasi()
            ->when($request->boolean('hanya_belum_dibaca'), fn ($q) => $q->where('is_read', false))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return ApiResponse::paginated($paginator, fn (Notifikasi $n) => [
            'notifikasi_id' => $n->notifikasi_id,
            'judul' => $n->judul,
            'content' => $n->content,
            'is_read' => filter_var($n->is_read, FILTER_VALIDATE_BOOLEAN),
            'created_at' => $n->created_at,
        ]);
    }

    public function belumDibaca(Request $request)
    {
        $member = $request->user()->member()->first();

        return ApiResponse::ok([
            'total' => $member->notifikasi()->where('is_read', false)->count(),
        ]);
    }

    public function baca(Request $request, Notifikasi $notifikasi)
    {
        $member = $request->user()->member()->first();

        if ($notifikasi->member_id !== $member->member_id) {
            return ApiResponse::fail('Notifikasi ini bukan milik Anda.', 403);
        }

        $notifikasi->update(['is_read' => true]);

        return ApiResponse::ok([], 'Notifikasi ditandai sudah dibaca.');
    }

    public function bacaSemua(Request $request)
    {
        $member = $request->user()->member()->first();

        $jumlah = $member->notifikasi()->where('is_read', false)->update(['is_read' => true]);

        return ApiResponse::ok(['ditandai' => $jumlah], 'Semua notifikasi ditandai sudah dibaca.');
    }
}
