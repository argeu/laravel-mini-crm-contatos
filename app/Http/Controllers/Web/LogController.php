<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;



class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $logPath = storage_path('logs/contact.log');
        
        if (!File::exists($logPath)) {
            $logs = collect([]);
        } else {
            $content = File::get($logPath);
            $lines = explode("\n", $content);
            
            $logs = collect();
            
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                
                // Parse log line to extract data
                if (preg_match('/\[(.*?)\] local\.INFO: Contact score processed (.*)/', $line, $matches)) {
                    $jsonData = json_decode($matches[2], true);
                    if ($jsonData && isset($jsonData['id'], $jsonData['email'], $jsonData['score'], $jsonData['timestamp'])) {
                        // Check if name exists (new logs) or try to get from database (old logs)
                        $name = isset($jsonData['name']) ? $jsonData['name'] : null;
                        
                        // If name is not in log, try to get from database
                        if (!$name) {
                            $contact = Contact::find($jsonData['id']);
                            $name = $contact ? $contact->name : 'N/A';
                        }
                        
                        $logs->push([
                            'id' => (int) $jsonData['id'],
                            'name' => $name,
                            'email' => $jsonData['email'],
                            'score' => (int) $jsonData['score'],
                            'timestamp' => $jsonData['timestamp'],
                        ]);
                    }
                }
            }
            
            // Reverse to show newest first
            $logs = $logs->reverse();
        }
        
        // Implement pagination manually
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $total = $logs->count();
        
        $paginatedLogs = $logs->forPage($currentPage, $perPage);
        
        // Create a custom paginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLogs,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
        
        // Calculate stats from all logs (not just current page)
        $allLogs = $logs;
        $todayCount = $allLogs->filter(function($log) { 
            return \Carbon\Carbon::parse($log['timestamp'])->isToday(); 
        })->count();
        $averageScore = $allLogs->count() > 0 ? round($allLogs->avg('score')) : 0;
        
        return view('logs.index', compact('paginator', 'allLogs', 'todayCount', 'averageScore'));
    }

    /**
     * Download logs as CSV.
     */
    public function download()
    {
        $logPath = storage_path('logs/contact.log');
        
        if (!File::exists($logPath)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }

        $content = File::get($logPath);
        $lines = explode("\n", $content);
        
        $csv = "ID,Nome,Email,Score,Timestamp\n";
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // Parse log line to extract data
            if (preg_match('/\[(.*?)\] local\.INFO: Contact score processed (.*)/', $line, $matches)) {
                $jsonData = json_decode($matches[2], true);
                if ($jsonData && isset($jsonData['id'], $jsonData['email'], $jsonData['score'], $jsonData['timestamp'])) {
                                    // Check if name exists (new logs) or try to get from database (old logs)
                $name = isset($jsonData['name']) ? $jsonData['name'] : null;
                
                // If name is not in log, try to get from database
                if (!$name) {
                    $contact = Contact::find($jsonData['id']);
                    $name = $contact ? $contact->name : 'N/A';
                }
                
                $csv .= "{$jsonData['id']},\"{$name}\",{$jsonData['email']},{$jsonData['score']},{$jsonData['timestamp']}\n";
                }
            }
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contact-logs.csv"',
        ]);
    }


} 