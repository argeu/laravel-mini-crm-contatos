<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Http\Resources\ContactCollection;
use App\Models\Contact;
use App\Jobs\ProcessContactScore;
use Illuminate\Http\JsonResponse;
use Throwable;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $contacts = Contact::paginate(10);
            $collection = new ContactCollection($contacts);
            $resolved = $collection->resolve();
            
            return response()->json([
                'message' => 'Contacts retrieved successfully',
                'data' => $resolved['data'],
                'meta' => $resolved['meta']
            ], 200);
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'List Contacts');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        try {
            $contact = Contact::create($request->validated());
            return $this->successResponse(
                new ContactResource($contact),
                'Contact created successfully',
                201
            );
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Create Contact');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): JsonResponse
    {
        try {
            return $this->successResponse(
                new ContactResource($contact),
                'Contact retrieved successfully'
            );
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Show Contact');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        try {
            $contact->update($request->validated());
            return $this->successResponse(
                new ContactResource($contact),
                'Contact updated successfully'
            );
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Update Contact');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): JsonResponse
    {
        try {
            $contact->delete();
            return $this->successResponse(null, 'Contact deleted successfully');
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Delete Contact');
        }
    }

    /**
     * Process contact score asynchronously.
     */
    public function processScore(Contact $contact): JsonResponse
    {
        try {
            ProcessContactScore::dispatch($contact)->onQueue('contacts');
            
            return response()->json([
                'message' => 'Score processing started',
                'contact_id' => $contact->id
            ], 200);
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Process Contact Score');
        }
    }
}
