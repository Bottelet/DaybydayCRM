<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'required|string',
            'type' => 'required|string|in:task,lead,project',
            'external_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $type = $this->input('type');
                    $modelsMapping = [
                        'task' => \App\Models\Task::class,
                        'lead' => \App\Models\Lead::class,
                        'project' => \App\Models\Project::class,
                    ];

                    if (isset($modelsMapping[$type])) {
                        $model = $modelsMapping[$type];
                        if (! $model::where('external_id', $value)->exists()) {
                            $fail("The selected {$type} does not exist.");
                        }
                    }
                },
            ],
        ];
    }
}
