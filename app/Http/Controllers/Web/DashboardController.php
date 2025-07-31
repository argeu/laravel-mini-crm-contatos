<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Jobs\ProcessContactScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Get contacts statistics
        $totalContacts = Contact::count();
        $processedContacts = Contact::whereNotNull('processed_at')->count();
        $pendingContacts = Contact::whereNull('processed_at')->count();
        $averageScore = Contact::whereNotNull('processed_at')->avg('score') ?? 0;
        
        // Get recent contacts
        $recentContacts = Contact::orderBy('created_at', 'desc')->take(5)->get();
        
        // Get logs statistics
        $logPath = storage_path('logs/contact.log');
        $totalLogs = 0;
        $processedToday = 0;
        
        if (File::exists($logPath)) {
            $content = File::get($logPath);
            $lines = explode("\n", $content);
            
            $totalLogs = count(array_filter($lines, function($line) {
                return !empty(trim($line)) && strpos($line, 'Contact score processed') !== false;
            }));
            
            $today = now()->format('Y-m-d');
            $processedToday = count(array_filter($lines, function($line) use ($today) {
                return !empty(trim($line)) && 
                       strpos($line, 'Contact score processed') !== false &&
                       strpos($line, $today) !== false;
            }));
        }
        
        return view('dashboard', compact(
            'totalContacts',
            'processedContacts', 
            'pendingContacts',
            'averageScore',
            'recentContacts',
            'totalLogs',
            'processedToday'
        ));
    }

    /**
     * Process all pending contacts.
     */
    public function processAllPending()
    {
        // Get all pending contacts
        $pendingContacts = Contact::whereNull('processed_at')->get();
        
        if ($pendingContacts->isEmpty()) {
            return redirect()->route('dashboard')->with('warning', 'Não há contatos pendentes para processar.');
        }
        
        // Dispatch jobs for all pending contacts
        foreach ($pendingContacts as $contact) {
            ProcessContactScore::dispatch($contact)->onQueue('contacts');
        }
        
        $count = $pendingContacts->count();
        
        // Log for debugging
        Log::info("Processamento iniciado para {$count} contato(s) pendente(s)");
        
        return redirect()->route('dashboard')->with('success', "Processamento iniciado para {$count} contato(s) pendente(s). Os scores serão atualizados em breve.");
    }
} 