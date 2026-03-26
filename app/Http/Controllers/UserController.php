<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = $this->service->list(auth()->user());

        return Inertia::render('Users/Index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create');
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $this->service->create($request->validated());

        return redirect()->route('users.index')
            ->with('message', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return Inertia::render('Users/Edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $this->service->update($user, $request->validated());

        return redirect()->route('users.index')
            ->with('message', 'Usuário atualizado!');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);

        $this->service->toggleStatus($user, auth()->user());

        return back()->with('message', 'Status atualizado!');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $this->service->resetPassword($user);

        return back()->with('message', 'Senha resetada para: Mudar@123');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->service->delete($user, auth()->user());

        return redirect()->route('users.index')
            ->with('message', 'Usuário excluído!');
    }
}