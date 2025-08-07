<?php

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StatCalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agility' => [
                'required',
                'numeric',
                'min:0',
                'max:9999',
            ],
            'strength' => [
                'required',
                'numeric', 
                'min:0',
                'max:9999',
            ],
            'base_ap' => [
                'required',
                'numeric',
                'min:0',
                'max:99999',
            ],
            'arp' => [
                'required',
                'numeric',
                'min:0',
                'max:9999',
            ],
            'resilience' => [
                'required',
                'numeric',
                'min:0',
                'max:9999',
            ],
            'stamina' => [
                'required',
                'numeric',
                'min:0',
                'max:9999',
            ],
            'hit' => [
                'required',
                'numeric',
                'min:0',
                'max:9999',
            ],
            'expertise' => [
                'required',
                'numeric',
                'min:0',
                'max:214',
            ],
            'crit_rating' => [
                'required',
                'numeric',
                'min:0',
                'max:9999',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'agility.required' => 'Agility is required for stat calculations.',
            'agility.numeric' => 'Agility must be a valid number.',
            'agility.min' => 'Agility cannot be negative.',
            'agility.max' => 'Agility seems unrealistically high (max: 9999).',
            
            'strength.required' => 'Strength is required for stat calculations.',
            'strength.numeric' => 'Strength must be a valid number.',
            'strength.min' => 'Strength cannot be negative.',
            'strength.max' => 'Strength seems unrealistically high (max: 9999).',
            
            'base_ap.required' => 'Base Attack Power is required.',
            'base_ap.numeric' => 'Base Attack Power must be a valid number.',
            'base_ap.min' => 'Base Attack Power cannot be negative.',
            'base_ap.max' => 'Base Attack Power seems unrealistically high (max: 99999).',
            
            'arp.required' => 'Armor Penetration Rating is required.',
            'arp.numeric' => 'Armor Penetration Rating must be a valid number.',
            'arp.min' => 'Armor Penetration Rating cannot be negative.',
            'arp.max' => 'Armor Penetration Rating seems unrealistically high (max: 9999).',
            
            'resilience.required' => 'Resilience Rating is required.',
            'resilience.numeric' => 'Resilience Rating must be a valid number.',
            'resilience.min' => 'Resilience Rating cannot be negative.',
            'resilience.max' => 'Resilience Rating seems unrealistically high (max: 9999).',
            
            'stamina.required' => 'Stamina is required for stat calculations.',
            'stamina.numeric' => 'Stamina must be a valid number.',
            'stamina.min' => 'Stamina cannot be negative.',
            'stamina.max' => 'Stamina seems unrealistically high (max: 9999).',
            
            'hit.required' => 'Hit Rating is required.',
            'hit.numeric' => 'Hit Rating must be a valid number.',
            'hit.min' => 'Hit Rating cannot be negative.',
            'hit.max' => 'Hit Rating seems unrealistically high (max: 9999).',
            
            'expertise.required' => 'Expertise is required.',
            'expertise.numeric' => 'Expertise must be a valid number.',
            'expertise.min' => 'Expertise cannot be negative.',
            'expertise.max' => 'Expertise cannot exceed 214 (hard cap).',
            
            'crit_rating.required' => 'Critical Strike Rating is required.',
            'crit_rating.numeric' => 'Critical Strike Rating must be a valid number.',
            'crit_rating.min' => 'Critical Strike Rating cannot be negative.',
            'crit_rating.max' => 'Critical Strike Rating seems unrealistically high (max: 9999).',
        ];
    }

    public function attributes(): array
    {
        return [
            'agility' => 'Agility',
            'strength' => 'Strength',
            'base_ap' => 'Base Attack Power',
            'arp' => 'Armor Penetration Rating',
            'resilience' => 'Resilience Rating',
            'stamina' => 'Stamina',
            'hit' => 'Hit Rating',
            'expertise' => 'Expertise',
            'crit_rating' => 'Critical Strike Rating',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        $errorMessage = 'Validation failed: ' . implode(' ', $errors);
        
        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $errorMessage)
        );
    }
}