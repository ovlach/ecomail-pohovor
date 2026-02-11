<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Contact;
use App\Data\Intent\Contact\DeleteContactIntent;
use App\Data\Intent\Contact\SearchContactIntent;
use App\Data\InvariantException;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Services\Command\CreateContactCommand;
use App\Services\Command\DeleteContactCommand;
use App\Services\Command\UpdateContactCommand;
use App\Services\Query\SearchContactQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function __construct(
        private readonly UpdateContactCommand $updateContactCommand,
        private readonly DeleteContactCommand $deleteContactCommand,
        private readonly CreateContactCommand $createContactCommand,
        private readonly SearchContactQuery $searchContactQuery,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $contacts = $this->searchContactQuery->execute(SearchContactIntent::queryAll());

        return view('contacts.index', [
            'contacts' => $contacts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('contacts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        $query = $request->query('q');
        $searchQueryParams = is_string($query) && $query !== '' ? ['q' => $query] : [];
        try {
            $contact = $this->createContactCommand->execute($request->toIntent());
        } catch (InvariantException $exception) {
            Log::error(
                'Cannot store contact: {message}',
                ['message' => $exception->getMessage()]
            );

            return redirect()
                ->route('contacts.index', $searchQueryParams)
                ->with('status', 'Contact create failed.');
        }

        if ($contact === null) {
            Log::error(
                'Cannot store contact: {message}',
                ['intent' => $request->toIntent()]
            );

            return redirect()
                ->route('contacts.index', $searchQueryParams)
                ->with('status', 'Contact create failed.');
        }

        return redirect()
            ->route('contacts.show', ['contact' => $contact->uuid->toString(), ...$searchQueryParams])
            ->with('status', 'Contact created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): View
    {
        return view('contacts.show', [
            'contact' => $contact,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        return view('contacts.edit', [
            'contact' => $contact,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request): RedirectResponse
    {
        $intent = $request->toIntent();
        $query = $request->query('q');
        $searchQueryParams = is_string($query) && $query !== '' ? ['q' => $query] : [];

        try {
            $contact = $this->updateContactCommand->execute($intent);
        } catch (InvariantException $exception) {
            Log::error(
                'Cannot update contact: {message}',
                ['message' => $exception->getMessage()]
            );

            return redirect()
                ->route('contacts.index', $searchQueryParams)
                ->with('status', 'Contact update failed.');
        }

        if ($contact === null) {
            Log::error("Failed to update contact '{uuid}'", [
                'uuid' => $intent->uuid,
                'intent' => $intent,
            ]);

            return redirect()
                ->route('contacts.show', ['contact' => $intent->uuid, ...$searchQueryParams])
                ->with('status', 'Contact update failed.');
        }

        return redirect()
            ->route('contacts.show', ['contact' => $contact->uuid->toString(), ...$searchQueryParams])
            ->with('status', 'Contact updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $route = $request->route('contact');
        if ($route === null) {
            abort(404);
        }
        /** @var Contact $route */
        $uuid = $route->uuid;
        $query = $request->query('q');
        $searchQueryParams = is_string($query) && $query !== '' ? ['q' => $query] : [];

        $result = $this->deleteContactCommand->execute(
            new DeleteContactIntent($uuid)
        );

        if ($result === false) {
            Log::error('Failed to delete contact {uuid}.', [
                'uuid' => $uuid,
            ]);

            return redirect()
                ->route('contacts.show', ['contact' => $uuid, ...$searchQueryParams])
                ->with('status', 'Contact can\'t be deleted.');
        }

        return redirect()
            ->route('contacts.index', $searchQueryParams)
            ->with('status', 'Contact deleted.');
    }

    public function search(Request $request): View
    {
        $q = $request->query('q');

        if ($q === null) {
            Log::warning('No search query provided.');
            $searchIntent = SearchContactIntent::queryAll();
        } else {
            if (is_string($q)) {
                $searchIntent = new SearchContactIntent($q);
            } else {
                abort(400);
            }
        }

        $contacts = $this->searchContactQuery->execute($searchIntent);
        if ($request->filled('q')) {
            $contacts->appends(['q' => $request->query('q')]);
        }

        return view('contacts.index', [
            'contacts' => $contacts,
        ]);
    }
}
