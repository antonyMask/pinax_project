<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMayorizacionRequest extends FormRequest
{
    /**
     * La autorización general se realiza mediante la sesión de Pinax.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Incorpora el identificador de la URL y normaliza la acción del formulario.
     */
    protected function prepareForValidation(): void
    {
        // El código se recibe en /mayorizacion/{cod_saldo}.
        $accion = $this->input('accion');

        $this->merge([
            'cod_saldo' => $this->route('cod_saldo'),
            'accion' => is_string($accion) && trim($accion) !== ''
                ? strtolower(trim($accion))
                : null,
        ]);
    }

    /**
     * Valida una intención controlada, nunca un estado escrito manualmente.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'cod_saldo' => ['bail', 'required', 'integer', 'min:1'],
            'accion' => [
                'bail',
                'required',
                'string',
                Rule::in(['recalcular', 'cerrar', 'inactivar']),
            ],
        ];
    }

    /**
     * Nombres legibles utilizados por los mensajes generales de Laravel.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cod_saldo' => 'código del saldo',
            'accion' => 'acción de mayorización',
        ];
    }

    /**
     * Mensajes orientados a las acciones disponibles en la tabla.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cod_saldo.required' =>
                'No se recibió el código del saldo que deseas actualizar.',
            'cod_saldo.integer' =>
                'El código del saldo debe ser un número entero.',
            'cod_saldo.min' =>
                'El código del saldo debe ser mayor que cero.',
            'accion.required' =>
                'Debes seleccionar una acción para la mayorización.',
            'accion.string' =>
                'La acción de mayorización debe enviarse como texto.',
            'accion.in' =>
                'La acción solo puede ser recalcular, cerrar o inactivar.',
        ];
    }
}
