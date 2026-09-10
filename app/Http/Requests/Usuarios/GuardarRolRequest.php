<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// cambio --- 10/09/2026: validación compartida por crear y editar un rol.
class GuardarRolRequest extends FormRequest
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
        // En edición el propio rol no debe chocar consigo mismo.
        $rol = $this->route('rol');

        return [
            'nombre' => [
                'required', 'string', 'max:60',
                Rule::unique('roles', 'nombre')->ignore($rol?->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:160'],
            'permisos' => ['array'],
            'permisos.*' => ['integer', Rule::exists('permisos', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El rol necesita un nombre.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
            'permisos.*.exists' => 'Uno de los permisos enviados no existe en el catálogo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
        ];
    }
}
