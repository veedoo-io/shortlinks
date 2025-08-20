<?php

namespace App\Http\Requests\Audio\Gemini;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read resource file
 */
class ConvertToMp3Request extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['file','mimetypes:application/octet-stream,audio/L24,audio/L16', 'max:30720'] // 30MB
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
