<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['sometimes', 'string'],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ];
    }
}
