<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonaRequest extends FormRequest
{
    /* Autorizacion
    Mas adelante esta decision dependera del token y rol enviados por la API.
    */

    public function authorize(): bool
    {
        return true;
    }

    /* Reglas de validacion del formulario */

    public function rules(): array
    {
        return [
            'dni' => ['bail', 'required', 'regex:/^[0-9]{13}$/'],
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],

            // Valores definidos por la tabla pa_people
            'sex' => ['required', 'in:M,W,F,D'],
            'ind_civil' => ['required', 'in:S,M,W'],

            // TINYINT firmado permite hasta 127
            'age' => ['required', 'integer', 'min:0', 'max:120'],

            // N = natural, J = juridica.
            'tip_person' => ['required', 'in:N,J'],
        ];
    }

    // Nombres legibles para mensajes de validacion

    public function attributes(): array
    {
        return [
            'dni' => 'DNI',
            'firstname' => 'primer nombre',
            'middlename' => 'segundo nombre',
            'lastname' => 'apellido',
            'sex' => 'sexo',
            'ind_civil' => 'estado civil',
            'age' => 'edad',
            'tip_person' => 'tipo de persona'
        ];
    }

    // Mensaje personalizado
    public function messages(): array
    {
        return [
            'dni.regex' => 'El DNI debe contener exactamente 13 dígitos numéricos.',
        ];
    }
}