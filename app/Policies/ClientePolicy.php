<?php

namespace App\Policies;

use App\Models\ClienteModelo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver clientes');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClienteModelo $clienteModelo): bool
    {
        return $user->can('ver clientes');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear clientes');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClienteModelo $clienteModelo): bool
    {
        return $user->can('editar clientes');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClienteModelo $clienteModelo): bool
    {
        return $user->can('eliminar clientes');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClienteModelo $clienteModelo): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClienteModelo $clienteModelo): bool
    {
        return false;
    }
}
