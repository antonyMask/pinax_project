<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class LoginRequest extends FormRequest
{
    // El login es una operacion publica
    public function authorize(): bool
    {
        return true;
    }

    /* Limpiamos unicamente el nombre.
    - La contraseña nunca debe alterarse ni recortarse.
    */
    #[Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->name),
        ]);
    }

    // Reglas del formulario web
    public function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:255'
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'max:72',
            ],
        ];
    }

    // Mensajes mostrados al usuario.
    public function messages(): array
        {
            return [
                'name.required' => 'El nombre de usuario es obligatorio.',
                'name.max' => 'El nombre de usuario no es válido.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.max' => 'La contraseña supera la longitud permitida.',
            ];
        }
}