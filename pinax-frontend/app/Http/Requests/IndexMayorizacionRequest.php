<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as HttpFormRequest;
use Illuminate\Validation\Rule;

class IndexMayorizacionRequest extends HttpFormRequest
{
    /*
    - Determina si el usuario puede realizar esta solicitud.
    - La autenticacion se manejara mediante el middleware.
    - Correspondiente, por eso aqui permitimos la solicitud.
    */
    public function authorize(): bool
    {
        return true;
    }

    // Normaliza los texto (Ej. ABIERTO en abierto)
    protected function prepareForValidation(): void
    {
        $estado = $this->input('ind_estado');

        // Normalizamos el estado solamente si contiene texto.
        $this->merge([
            'ind_estado' => is_string($estado) && trim($estado) !== ''
            ? strtolower(trim($estado))
            : null,
        ]);
    }

    public function rules(): array
    {
        return [
            // Permite filtrar el resumen por el identificador interno de la cuenta.
            'cod_cuenta' => [
                'nullable',
                'integer',
                'min:1'
            ],
            
            // Codigo especifico de un saldo mayorizado.
            'cod_saldo' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // Periodo contable por el cual se filtraran los saldos.
            'cod_periodo' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // El estado unicamente puede contener uno de los valores
            // reconocidos por el contrato de la API de mayorizacion.
            'ind_estado' => [
                'nullable',
                Rule::in([
                    'abierto',
                    'recalculado',
                    'cerrado',
                    'inactivo',
                ]),
            ],
        ];
    }

    // Define mensajes de validacion comprensibles para el usuario.
    public function messages(): array
    {
        return [
            'cod_saldo.integer' => 'El codigo de saldo debe ser un número entero.',
            'cod_saldo.min' => 'El código de saldo debe ser mayor que cero.',

            'cod_periodo.integer' => 'El período debe ser un número entero.',
            'cod_periodo.min' => 'El período seleccionado no es válido.',

            'cod_cuenta.integer' => 'La cuenta debe ser un número entero.',
            'cod_cuenta.min' => 'La cuenta seleccionada no es válida.',

            'ind_estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}