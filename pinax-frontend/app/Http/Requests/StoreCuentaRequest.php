<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCuentaRequest extends FormRequest
{
    /*
    Autorizacion
    Mas adelante esta decision dependera del token y rol enviados por la API.
    */
    public function authorize(): bool
    {
        return true;
    }

    /*
    Normalizamos algunos valores antes de validar.
    cod_tipo_cuenta llega siempre desde el formulario (0 significa
    "crear un nuevo tipo de cuenta"), tal como lo espera la API.
    */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cod_num_cuenta' => trim((string) $this->cod_num_cuenta),
            'nom_cuenta' => trim((string) $this->nom_cuenta),
            'ind_naturaleza_cuenta' => strtolower(trim((string) $this->ind_naturaleza_cuenta)),
            'ind_acepta_movimiento' => strtolower(trim((string) $this->ind_acepta_movimiento)),
        ]);
    }

    /*
    Reglas de validacion del formulario.
    Deben coincidir con las validaciones del controlador catalogo.controller.js
    */
    public function rules(): array
    {
        return [
            'cod_num_cuenta' => ['required', 'string', 'max:50'],
            'nom_cuenta' => ['required', 'string', 'max:255'],

            // 0 indica que se debe crear un nuevo tipo de cuenta.
            'cod_tipo_cuenta' => ['required', 'integer', 'min:0'],

            // Solo obligatorios cuando cod_tipo_cuenta llega en 0.
            'nom_tipo_cuenta' => ['nullable', 'required_if:cod_tipo_cuenta,0', 'string', 'max:255'],
            'ind_naturaleza_tipo' => ['nullable', 'required_if:cod_tipo_cuenta,0', Rule::in(['deudora', 'acreedora'])],
            'des_tipo_cuenta' => ['nullable', 'string', 'max:255'],

            // 0 o vacio se interpreta como cuenta de primer nivel (sin padre).
            'cod_cuenta_padre' => ['nullable', 'integer', 'min:0'],

            'num_nivel_jerarquia' => ['required', 'integer', 'min:1'],
            'ind_naturaleza_cuenta' => ['required', Rule::in(['deudora', 'acreedora'])],
            'ind_acepta_movimiento' => ['required', Rule::in(['si', 'no'])],
            'des_cuenta' => ['nullable', 'string', 'max:255'],
            'ind_estado' => ['nullable', Rule::in(['activo', 'inactivo'])],
        ];
    }

    // Nombres legibles para los mensajes de validacion.
    public function attributes(): array
    {
        return [
            'cod_num_cuenta' => 'número de cuenta',
            'nom_cuenta' => 'nombre de la cuenta',
            'cod_tipo_cuenta' => 'tipo de cuenta',
            'nom_tipo_cuenta' => 'nombre del nuevo tipo de cuenta',
            'ind_naturaleza_tipo' => 'naturaleza del nuevo tipo de cuenta',
            'cod_cuenta_padre' => 'cuenta padre',
            'num_nivel_jerarquia' => 'nivel de jerarquía',
            'ind_naturaleza_cuenta' => 'naturaleza de la cuenta',
            'ind_acepta_movimiento' => 'acepta movimiento',
            'des_cuenta' => 'descripción',
        ];
    }

    // Mensajes personalizados.
    public function messages(): array
    {
        return [
            'nom_tipo_cuenta.required_if' => 'Debe indicar el nombre del nuevo tipo de cuenta.',
            'ind_naturaleza_tipo.required_if' => 'Debe indicar la naturaleza del nuevo tipo de cuenta.',
        ];
    }
}
