<?php

namespace App\Http\Controllers\Api;

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
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 10,
                'total' => 0,
                'from' => null,
                'to' => null,
                'prev_page_url' => null,
                'next_page_url' => null,
            ]);
        }

        $content = File::get($logPath);
        $lines = explode("\n", $content);
        
        $logs = [];
        
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
                    
                    $logs[] = [
                        'id' => (int) $jsonData['id'],
                        'name' => $name,
                        'email' => $jsonData['email'],
                        'score' => (int) $jsonData['score'],
                        'timestamp' => $jsonData['timestamp'],
                    ];
                }
            }
        }
        
        // Reverse to show newest first
        $logs = array_reverse($logs);
        
        // Pagination
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        
        $total = count($logs);
        $offset = ($page - 1) * $perPage;
        $paginatedLogs = array_slice($logs, $offset, $perPage);
        
        $lastPage = ceil($total / $perPage);
        
        return response()->json([
            'data' => $paginatedLogs,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'prev_page_url' => $page > 1 ? url("/api/logs?page=" . ($page - 1)) : null,
            'next_page_url' => $page < $lastPage ? url("/api/logs?page=" . ($page + 1)) : null,
        ]);
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