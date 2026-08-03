<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permohonan;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function excel(Request $request)
    {
        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User ID', 'Title', 'Status', 'Created At']);

            Permohonan::chunk(500, function ($permohonans) use ($handle) {
                foreach ($permohonans as $p) {
                    fputcsv($handle, [$p->id, $p->user_id, $p->title, $p->status, $p->created_at]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="permohonan.csv"');

        return $response;
    }

    public function pdf(Request $request)
    {
        // Dummy PDF summary report generation logic
        $html = "<h1>Summary Report</h1><p>Permohonan Summary Data</p>";
        
        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="summary.pdf"');
    }
}
