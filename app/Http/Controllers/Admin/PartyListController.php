<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\PartyList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * The official registry of party lists that candidates may file under.
 */
class PartyListController extends Controller
{
    public function store(Request $request)
    {
        $party = PartyList::create($this->validated($request) + ['created_by' => Auth::id(), 'is_active' => true]);

        SscHelper::logActivity(Auth::id(), 'PARTY_LIST_ADD', "Registered party list: {$party->name}");
        return redirect()->route('admin.candidacies')->with('success', "{$party->name} is now a registered party list.");
    }

    public function update(Request $request, PartyList $partyList)
    {
        $partyList->update($this->validated($request, $partyList));

        SscHelper::logActivity(Auth::id(), 'PARTY_LIST_UPDATE', "Updated party list: {$partyList->name}");
        return redirect()->route('admin.candidacies')->with('success', "{$partyList->name} was updated.");
    }

    /** Opens or closes a party list to new candidates; past filings keep it. */
    public function toggle(PartyList $partyList)
    {
        $partyList->update(['is_active' => !$partyList->is_active]);
        $state = $partyList->is_active ? 'open to new candidates' : 'closed to new candidates';

        SscHelper::logActivity(Auth::id(), 'PARTY_LIST_TOGGLE', "Party list {$partyList->name} is now {$state}");
        return redirect()->route('admin.candidacies')->with('success', "{$partyList->name} is now {$state}.");
    }

    public function destroy(PartyList $partyList)
    {
        // Deleting a party with filings would quietly turn its candidates into
        // independents on ballots and past results; close it instead.
        if ($partyList->candidacies()->exists()) {
            return redirect()->route('admin.candidacies')->with('danger', "{$partyList->name} has candidates on file, so it cannot be deleted. Close it to new candidates instead.");
        }

        $partyList->delete();

        SscHelper::logActivity(Auth::id(), 'PARTY_LIST_DELETE', "Deleted party list: {$partyList->name}");
        return redirect()->route('admin.candidacies')->with('success', "{$partyList->name} was deleted.");
    }

    private function validated(Request $request, ?PartyList $partyList = null): array
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'acronym' => ($acronym = strtoupper(trim((string) $request->input('acronym')))) !== '' ? $acronym : null,
            'color' => strtoupper(trim((string) $request->input('color', '#4F46E5'))),
            'description' => ($description = trim((string) $request->input('description'))) !== '' ? $description : null,
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100', Rule::unique('party_lists', 'name')->ignore($partyList?->id)],
            'acronym' => ['nullable', 'string', 'max:15', 'regex:/^[A-Z0-9][A-Z0-9 &.\-]*$/', Rule::unique('party_lists', 'acronym')->ignore($partyList?->id)],
            'color' => ['required', 'regex:/^#[0-9A-F]{6}$/'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.unique' => 'A party list with this name is already registered.',
            'acronym.unique' => 'Another party list already uses this acronym.',
            'acronym.regex' => 'The acronym may contain letters, numbers, spaces, &, . and - only.',
            'color.regex' => 'Choose a valid colour.',
        ]);
    }
}
