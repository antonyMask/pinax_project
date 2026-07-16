<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonaRequest extends FormRequest
{
    /**
     * Permitimos que la solicitud sea procesada.
     * La autorización por usuario se añadirá cuando implementemos autenticación.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalizamos determinados valores antes de validarlos.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'dni' => trim((string) $this->dni),
            'firstname' => trim((string) $this->firstname),
            'middlename' => trim((string) $this->middlename),
            'lastname' => trim((string) $this->lastname),
            'sex' => strtoupper(trim((string) $this->sex)),
            'ind_civil' => strtoupper(trim((string) $this->ind_civil)),
            'tip_person' => strtoupper(trim((string) $this->tip_person)),
            'ind_people' => strtolower(trim((string) $this->ind_people)),
        ]);
    }

    /**
     * Reglas que deben coincidir con las validaciones de la API.
     */
    public function rules(): array
    {
        return [
            'dni' => [
                'bail',
                'required',
                'regex:/^[0-9]{13}$/',
            ],
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'sex' => ['required', Rule::in(['M', 'W', 'F', 'D'])],
            'ind_civil' => ['required', Rule::in(['S', 'M', 'W'])],
            'age' => ['required', 'integer', 'min:0', 'max:120'],
            'tip_person' => ['required', Rule::in(['N', 'J'])],
            'ind_people' => [
                'required',
                Rule::in(['activo', 'inactivo']),
            ],
        ];
    }

    /**
     * Mensajes amigables mostrados en la interfaz.
     */
    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.regex' => 'El DNI debe contener exactamente 13 dígitos numéricos.',
            'firstname.required' => 'El primer nombre es obligatorio.',
            'middlename.required' => 'El segundo nombre es obligatorio.',
            'lastname.required' => 'El apellido es obligatorio.',
            'sex.required' => 'Debe seleccionar el sexo.',
            'sex.in' => 'El valor seleccionado para sexo no es válido.',
            'ind_civil.required' => 'Debe seleccionar el estado civil.',
            'ind_civil.in' => 'El estado civil seleccionado no es válido.',
            'age.required' => 'La edad es obligatoria.',
            'age.integer' => 'La edad debe ser un número entero.',
            'age.min' => 'La edad no puede ser negativa.',
            'age.max' => 'La edad no puede ser mayor que 120.',
            'tip_person.required' => 'Debe seleccionar el tipo de persona.',
            'tip_person.in' => 'El tipo de persona seleccionado no es válido.',
            'ind_people.required' => 'Debe seleccionar el estado de la persona.',
            'ind_people.in' => 'El estado de la persona no es válido.',
        ];
    }
}