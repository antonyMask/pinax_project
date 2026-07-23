<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cod_num_cuenta' => trim((string) $this->cod_num_cuenta),
            'nom_cuenta' => trim((string) $this->nom_cuenta),
            'ind_naturaleza_cuenta' => strtolower(trim((string) $this->ind_naturaleza_cuenta)),
            'ind_acepta_movimiento' => strtolower(trim((string) $this->ind_acepta_movimiento)),
            'ind_estado' => strtolower(trim((string) $this->ind_estado)),

            // El checkbox del formulario solo envía el campo cuando está marcado.
            'actualizar_tipo' => $this->boolean('actualizar_tipo'),
        ]);
    }

    /*
    Reglas de validacion.
    Deben coincidir con el controlador actualizarCuenta de la API.
    */
    public function rules(): array
    {
        return [
            'cod_num_cuenta' => ['required', 'string', 'max:50'],
            'nom_cuenta' => ['required', 'string', 'max:255'],

            // Al actualizar, el tipo de cuenta debe existir (no se acepta 0 aquí).
            'cod_tipo_cuenta' => ['required', 'integer', 'min:1'],

            'cod_cuenta_padre' => ['nullable', 'integer', 'min:0'],
            'num_nivel_jerarquia' => ['required', 'integer', 'min:1'],
            'ind_naturaleza_cuenta' => ['required', Rule::in(['deudora', 'acreedora'])],
            'ind_acepta_movimiento' => ['required', Rule::in(['si', 'no'])],
            'des_cuenta' => ['nullable', 'string', 'max:255'],
            'ind_estado' => ['required', Rule::in(['activo', 'inactivo'])],

            // Datos del tipo de cuenta, solo obligatorios si se marca actualizar_tipo.
            'actualizar_tipo' => ['boolean'],
            'nom_tipo_cuenta' => ['nullable', 'required_if:actualizar_tipo,1', 'string', 'max:255'],
            'ind_naturaleza_tipo' => ['nullable', 'required_if:actualizar_tipo,1', Rule::in(['deudora', 'acreedora'])],
            'des_tipo_cuenta' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'cod_num_cuenta' => 'número de cuenta',
            'nom_cuenta' => 'nombre de la cuenta',
            'cod_tipo_cuenta' => 'tipo de cuenta',
            'cod_cuenta_padre' => 'cuenta padre',
            'num_nivel_jerarquia' => 'nivel de jerarquía',
            'ind_naturaleza_cuenta' => 'naturaleza de la cuenta',
            'ind_acepta_movimiento' => 'acepta movimiento',
            'des_cuenta' => 'descripción',
            'ind_estado' => 'estado',
            'nom_tipo_cuenta' => 'nombre del tipo de cuenta',
            'ind_naturaleza_tipo' => 'naturaleza del tipo de cuenta',
        ];
    }

    public function messages(): array
    {
        return [
            'nom_tipo_cuenta.required_if' => 'Debe indicar el nombre del tipo de cuenta a actualizar.',
            'ind_naturaleza_tipo.required_if' => 'Debe indicar la naturaleza del tipo de cuenta a actualizar.',
        ];
    }
}
