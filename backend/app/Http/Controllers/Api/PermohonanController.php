<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\PermohonanDocument;
use App\Support\QiHelp;
use App\Services\QiLog;
use Illuminate\Support\Facades\Storage;

class PermohonanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Permohonan::with(['documents', 'logs', 'pemohon']);

        if ($user->hasRole('pemohon')) {
            $query->where('pemohon_id', $user->id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_permohonan', 'like', '%' . $search . '%')
                  ->orWhere('judul_project', 'like', '%' . $search . '%');
            });
        }

        $permohonan = $query->orderBy('id', 'desc')->paginate(15);

        return QiHelp::apiResponse($permohonan, 'Permohonan list retrieved', 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_project' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        $permohonan = Permohonan::create([
            'pemohon_id' => $request->user()->id,
            'nomor_permohonan' => QiHelp::generateRegistrationNo(),
            'judul_project' => $request->judul_project,
            'deskripsi' => $request->deskripsi,
            'status' => 'draft',
        ]);

        QiLog::audit('CREATED', $permohonan->id, $request->user()->id, 'Draft permohonan created');

        return QiHelp::apiResponse($permohonan, 'Permohonan draft created', 201);
    }

    public function show(int $id, Request $request)
    {
        $user = $request->user();
        $query = Permohonan::with(['documents', 'logs', 'pemohon']);

        if ($user->hasRole('pemohon')) {
            $query->where('pemohon_id', $user->id);
        }

        $permohonan = $query->findOrFail($id);

        return QiHelp::apiResponse($permohonan, 'Permohonan details retrieved', 200);
    }

    public function update(Request $request, int $id)
    {
        $user = $request->user();
        $permohonan = Permohonan::where('pemohon_id', $user->id)->findOrFail($id);

        if (!in_array($permohonan->status, ['draft', 'revision'])) {
            return response()->json([
                'status' => false,
                'message' => 'Only draft or revision permohonan can be updated',
                'data' => null
            ], 422);
        }

        $request->validate([
            'judul_project' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
        ]);

        $permohonan->update($request->only(['judul_project', 'deskripsi']));

        QiLog::audit('UPDATED', $permohonan->id, $user->id, 'Permohonan updated');

        return QiHelp::apiResponse($permohonan, 'Permohonan updated', 200);
    }

    public function submit(Request $request, int $id)
    {
        $user = $request->user();
        $permohonan = Permohonan::where('pemohon_id', $user->id)->findOrFail($id);

        if (!in_array($permohonan->status, ['draft', 'revision'])) {
            return response()->json([
                'status' => false,
                'message' => 'Only draft or revision permohonan can be submitted',
                'data' => null
            ], 422);
        }

        $permohonan->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        QiLog::audit('SUBMITTED', $permohonan->id, $user->id, 'Permohonan submitted for review');

        return QiHelp::apiResponse($permohonan, 'Permohonan submitted', 200);
    }

    public function uploadDocument(Request $request, int $id)
    {
        $user = $request->user();
        $permohonan = Permohonan::findOrFail($id);

        if ($permohonan->pemohon_id !== $user->id && !$user->hasRole('penilai')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized permohonan access',
                'data' => null
            ], 403);
        }

        if (!in_array($permohonan->status, ['draft', 'revision'])) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot upload documents unless status is draft or revision',
                'data' => null
            ], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,doc|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('permohonan_documents', 'public');

        $document = PermohonanDocument::create([
            'permohonan_id' => $permohonan->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
        ]);

        QiLog::audit('DOCUMENT_UPLOADED', $permohonan->id, $user->id, "Document {$file->getClientOriginalName()} uploaded");

        return QiHelp::apiResponse($document, 'Document uploaded', 201);
    }

    public function deleteDocument(Request $request, int $id, int $docId)
    {
        $user = $request->user();
        $permohonan = Permohonan::where('pemohon_id', $user->id)->findOrFail($id);

        $document = PermohonanDocument::where('permohonan_id', $permohonan->id)->findOrFail($docId);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        QiLog::audit('DOCUMENT_DELETED', $permohonan->id, $user->id, "Document {$document->original_name} deleted");

        return QiHelp::apiResponse(null, 'Document deleted successfully', 200);
    }
}
