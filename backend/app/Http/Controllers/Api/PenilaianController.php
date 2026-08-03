<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Support\QiHelp;
use App\Services\QiLog;

class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $query = Permohonan::with(['documents', 'logs', 'pemohon'])->where('status', 'submitted');
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_permohonan', 'like', '%' . $search . '%')
                  ->orWhere('judul_project', 'like', '%' . $search . '%');
            });
        }

        $permohonan = $query->orderBy('id', 'desc')->paginate(15);
        return QiHelp::apiResponse($permohonan, 'Submitted permohonan list retrieved', 200);
    }

    public function review(Request $request, int $id)
    {
        $permohonan = Permohonan::findOrFail($id);

        if ($permohonan->status !== 'submitted') {
            return response()->json([
                'status' => false,
                'message' => 'Only submitted permohonan can be reviewed',
                'data' => null
            ], 422);
        }

        $request->validate([
            'action' => 'required|in:approve,revision,reject',
            'notes' => 'required_if:action,revision,reject|string|nullable',
        ]);

        $action = $request->action;
        $notes = $request->notes;
        $statusFrom = $permohonan->status;

        if ($action === 'approve') {
            $permohonan->update(['status' => 'approved']);
            QiLog::audit('APPROVED', $permohonan->id, $request->user()->id, $notes ?? 'Permohonan approved', $statusFrom, 'approved');
        } elseif ($action === 'revision') {
            $permohonan->update(['status' => 'revision']);
            QiLog::audit('REVISION_REQUESTED', $permohonan->id, $request->user()->id, $notes, $statusFrom, 'revision');
        } elseif ($action === 'reject') {
            $permohonan->update(['status' => 'rejected']);
            QiLog::audit('REJECTED', $permohonan->id, $request->user()->id, $notes, $statusFrom, 'rejected');
        }

        return QiHelp::apiResponse($permohonan, "Permohonan {$action} successfully", 200);
    }
}
