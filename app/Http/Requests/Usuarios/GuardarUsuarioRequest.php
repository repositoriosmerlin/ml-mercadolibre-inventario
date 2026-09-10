<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

// cambio --- 10/09/2026: validación compartida por crear y editar un usuario.
class GuardarUsuarioRequest extends FormRequest
{
    /**
     * La autorización ya la hizo el middleware 'can' de la ruta.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required', 'string', 'email', 'max:180',
                Rule::unique('users', 'email')->ignore($usuario?->id),
            ],
            // Al editar, dejar la contraseña vacía significa "no la cambies".
            // Al crear es obligatoria: no hay correo configurado que pueda
            // mandar una de bienvenida (MAIL_MAILER=log).
            'password' => [
                $usuario ? 'nullable' : 'required',
                'confirmed',
                Password::min(8),
            ],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El usuario necesita un nombre.',
            'email.required' => 'El correo es obligatorio.',
            'email.unique' => 'Ya hay una cuenta con ese correo.',
            'password.required' => 'Define una contraseña para la cuenta.',
            'password.confirmed' => 'Las dos contraseñas no coinciden.',
            'roles.*.exists' => 'Uno de los roles enviados no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo',
            'password' => 'contraseña',
        ];
    }
}
