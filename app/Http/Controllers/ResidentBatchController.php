<?php

namespace App\Http\Controllers;

use App\Services\ResidentBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ResidentBatchController extends Controller
{
    protected $batchService;
    
    public function __construct(ResidentBatchService $batchService)
    {
        $this->batchService = $batchService;
    }
    
    /**
     * Show the batch upload form
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get admin's organization
        $adminRecord = DB::select('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        $organizationId = $adminRecord[0]->organization_id ?? null;
        
        return Inertia::render('Admin/ResidentBatchUpload', [
            'organizationId' => $organizationId
        ]);
    }
    
    /**
     * Process CSV upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120', // 5MB max
        ]);
        
        $user = auth()->user();
        
        // Get admin's organization
        $adminRecord = DB::select('SELECT organization_id FROM admin WHERE user_id = ?', [$user->id]);
        $organizationId = $adminRecord[0]->organization_id ?? null;
        
        if (!$organizationId) {
            return back()->withErrors(['error' => 'Admin organization not found']);
        }
        
        try {
            \Log::info('ResidentBatchController: Starting CSV upload', [
                'organization_id' => $organizationId,
                'file_name' => $request->file('csv_file')->getClientOriginalName(),
                'file_size' => $request->file('csv_file')->getSize()
            ]);
            
            // Parse CSV file
            $csvData = $this->parseCsvFile($request->file('csv_file'));
            
            \Log::info('ResidentBatchController: CSV parsed successfully', [
                'csv_data_count' => count($csvData),
                'csv_data' => $csvData
            ]);
            
            if (empty($csvData)) {
                \Log::error('ResidentBatchController: CSV file is empty');
                return back()->withErrors(['error' => 'CSV file is empty or invalid']);
            }
            
            // Process the batch
            $results = $this->batchService->processCsvFile($csvData, $organizationId);
            
            \Log::info('ResidentBatchController: Batch processing completed', [
                'successful_count' => count($results['successful']),
                'failed_count' => count($results['failed']),
                'errors_count' => count($results['errors'])
            ]);
            
            return back()->with([
                'success' => 'Batch processing completed',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            \Log::error('ResidentBatchController: Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $template = $this->batchService->getCsvTemplate();
        
        $filename = 'resident_template.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($template) {
            $file = fopen('php://output', 'w');
            foreach ($template as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Parse CSV file
     */
    private function parseCsvFile($file): array
    {
        $data = [];
        $handle = fopen($file->getPathname(), 'r');
        
        if ($handle !== false) {
            $header = fgetcsv($handle);
            if ($header === false) {
                fclose($handle);
                return [];
            }
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === count($header)) {
                    $data[] = array_combine($header, $row);
                }
            }
            
            fclose($handle);
        }
        
        return $data;
    }
}
