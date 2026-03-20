<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\UserService;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends Controller
{
    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $users = $this->service->list(auth()->user());

        return Inertia::render('Users/Index', compact('users'));
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(StoreUserRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('users.index')
            ->with('message', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        // Segurança mantida no controller (fluxo de acesso)
        if (auth()->user()->access_level !== 1 && auth()->id() !== $user->id) {
            abort(403, 'Você só pode editar seu próprio perfil.');
        }

        return Inertia::render('Users/Edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->service->update($user, $request->validated());

        return redirect()->route('users.index')
            ->with('message', 'Usuário atualizado!');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        try {
            $this->service->toggleStatus($user, auth()->user());

            return back()->with('message', 'Status atualizado!');
        } catch (\Exception $e) {
            return back()->withErrors($e->errors());
        }
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->service->resetPassword($user);

        return back()->with('message', 'Senha resetada para: Mudar@123');
    }

    public function destroy(User $user): RedirectResponse
    {
        try {
            $this->service->delete($user, auth()->user());

            return redirect()->route('users.index')
                ->with('message', 'Usuário excluído!');
        } catch (\Exception $e) {
            return back()->withErrors($e->errors());
        }
    }
}