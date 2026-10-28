<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Models\Link;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $like = '%'.addcslashes($search, '%_\\').'%';

        $links = $request->user()->links()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('url', 'like', $like)
                ->orWhere('title', 'like', $like)
                ->orWhere('code', 'like', $like)))
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('links.index', [
            'links' => $links,
            'search' => $search,
            'created' => $request->user()->links()->find(session('created_link')),
        ]);
    }

    public function store(StoreLinkRequest $request): RedirectResponse
    {
        try {
            $link = $request->user()->links()->create([
                'code' => $request->validated('alias') ?? Link::generateCode(),
                'url' => $request->validated('url'),
                'title' => $request->validated('title'),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Someone took the same alias between validation and insert.
            throw ValidationException::withMessages(['alias' => 'That alias is already taken. Try another one.']);
        }

        return redirect()->route('dashboard')->with('created_link', $link->id);
    }

    public function edit(Link $link): View
    {
        Gate::authorize('update', $link);

        return view('links.edit', ['link' => $link]);
    }

    public function update(UpdateLinkRequest $request, Link $link): RedirectResponse
    {
        $link->update($request->validated());

        return redirect()->route('links.edit', $link)->with('status', 'Link saved.');
    }

    public function destroy(Link $link): RedirectResponse
    {
        Gate::authorize('delete', $link);

        $link->delete();

        return redirect()->route('dashboard')->with('status', 'Link deleted.');
    }
}
