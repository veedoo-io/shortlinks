<?php

namespace App\Http\Requests\Audio\Gemini;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read resource file
 * @property-read string path
 */
class ConvertToMp3Request extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimetypes:application/octet-stream,audio/L24,audio/L16', 'max:30720'], // 30MB
            'path' => ['required', 'string', 'min:1', 'max:255', 'ends_with:.mp3'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
