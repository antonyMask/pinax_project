<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMayorizacionRequest extends FormRequest
{
    /**
     * La autorización general se realiza mediante la sesión de Pinax.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Limpia los identificadores enviados por los selectores HTML.
     */
    protected function prepareForValidation(): void
    {
        // Los selectores envían texto; quitamos únicamente espacios externos.
        $codCuenta = $this->input('cod_cuenta');
        $codPeriodo = $this->input('cod_periodo');

        $this->merge([
            'cod_cuenta' => is_string($codCuenta)
                ? trim($codCuenta)
                : $codCuenta,
            'cod_periodo' => is_string($codPeriodo)
                ? trim($codPeriodo)
                : $codPeriodo,
        ]);
    }

    /**
     * La interfaz solo puede indicar la cuenta y el período.
     * Los importes se calculan en MySQL desde los asientos aprobados.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'cod_cuenta' => ['bail', 'required', 'integer', 'min:1'],
            'cod_periodo' => ['bail', 'required', 'integer', 'min:1'],
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
            'cod_cuenta' => 'cuenta contable',
            'cod_periodo' => 'período contable',
        ];
    }

    /**
     * Mensajes orientados a corregir el formulario de generación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cod_cuenta.required' =>
                'Debes seleccionar la cuenta que deseas mayorizar.',
            'cod_cuenta.integer' =>
                'La cuenta seleccionada no contiene un código válido.',
            'cod_cuenta.min' =>
                'El código de la cuenta debe ser mayor que cero.',
            'cod_periodo.required' =>
                'Debes seleccionar el período contable.',
            'cod_periodo.integer' =>
                'El período seleccionado no contiene un código válido.',
            'cod_periodo.min' =>
                'El código del período debe ser mayor que cero.',
        ];
    }
}
