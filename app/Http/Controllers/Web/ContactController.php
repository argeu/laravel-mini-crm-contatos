<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Jobs\ProcessContactScore;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contact::query();
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'pending') {
                $query->whereNull('processed_at');
            } elseif ($status === 'processed') {
                $query->whereNotNull('processed_at');
            }
        }
        
        // Score filter
        if ($request->filled('score')) {
            $score = $request->get('score');
            switch ($score) {
                case 'high':
                    $query->where('score', '>=', 80);
                    break;
                case 'medium':
                    $query->whereBetween('score', [40, 79]);
                    break;
                case 'low':
                    $query->whereBetween('score', [0, 39]);
                    break;
            }
        }
        
        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        // Validate sort column
        $allowedSorts = ['name', 'email', 'score', 'created_at', 'processed_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        $contacts = $query->paginate(10);
        
        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'required|string|max:20',
        ]);

        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contato criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        return view('contacts.show', compact('contact'));
    }

    /**
     * Display the specified resource as JSON.
     */
    public function showJson(Contact $contact)
    {
        return response()->json([
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'score' => $contact->score,
                'processed_at' => $contact->processed_at,
                'created_at' => $contact->created_at,
                'updated_at' => $contact->updated_at
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        return view('contacts.edit', compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $contact->id,
            'phone' => 'required|string|max:20',
        ]);

        $contact->update($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contato atualizado com sucesso!');
    }

        /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        
        return redirect()->route('contacts.index')
            ->with('success', 'Contato excluído com sucesso!');
    }

    /**
     * Process a single contact score.
     */
    public function processScore(Contact $contact)
    {
        // Check if contact is already processed
        if ($contact->processed_at) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Este contato já foi processado.'], 400);
            }
            return redirect()->back()->with('warning', 'Este contato já foi processado.');
        }
        
        // Dispatch the job
        ProcessContactScore::dispatch($contact->id)->onQueue('contacts');
        
        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Processamento iniciado. O score será atualizado em breve.',
                'contact_id' => $contact->id
            ], 200);
        }
        
        return redirect()->back()->with('success', 'Processamento iniciado. O score será atualizado em breve.');
    }
} 