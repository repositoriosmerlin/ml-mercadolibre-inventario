<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// cambio --- 10/09/2026: validación del alta manual de permisos.
class GuardarPermisoRequest extends FormRequest
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
        return [
            // El formato 'modulo.accion' no es capricho: de ahí sale el módulo
            // con el que se agrupa el catálogo en el formulario de rol, y es
            // el mismo formato que espera el middleware 'can' de las rutas.
            'clave' => [
                'required', 'string', 'max:60',
                'regex:/^[a-z][a-z0-9_]*\.[a-z][a-z0-9_]*$/',
                Rule::unique('permisos', 'clave'),
            ],
            'descripcion' => ['nullable', 'string', 'max:160'],
        ];
    }

    /**
     * Se normaliza antes de validar: un espacio o una mayúscula de más no
     * deberían costarle al usuario un mensaje de error.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'clave' => Str::lower(trim((string) $this->input('clave'))),
        ]);
    }

    public function messages(): array
    {
        return [
            'clave.required' => 'La clave es obligatoria.',
            'clave.unique' => 'Ese permiso ya existe en el catálogo.',
            'clave.regex' => 'Usa el formato modulo.accion, en minúsculas. Por ejemplo: reportes.ver',
        ];
    }

    public function attributes(): array
    {
        return [
            'clave' => 'clave',
            'descripcion' => 'descripción',
        ];
    }
}
