<?php

namespace App\Services\Comment;

use App\Models\Comment;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use InvalidArgumentException;

class CommentService
{
    /**
     * Model mapping for comment types.
     */
    private const MODEL_MAPPING = [
        'task'    => Task::class,
        'lead'    => Lead::class,
        'project' => Project::class,
    ];

    /**
     * Create a comment on a model (task, lead, or project).
     *
     * @param string $type        The type of model (task, lead, project)
     * @param string $externalId  The external ID of the model
     * @param string $description The comment description
     * @param int    $userId      The user creating the comment
     *
     * @return Comment The created comment
     *
     * @throws InvalidArgumentException If type is not supported
     */
    public function createComment(string $type, string $externalId, string $description, int $userId): Comment
    {
        // Validate type
        if ( ! array_key_exists($type, self::MODEL_MAPPING)) {
            throw new InvalidArgumentException("Unsupported comment type: {$type}. Supported types: " . implode(', ', array_keys(self::MODEL_MAPPING)));
        }

        // Get the model class
        $modelClass = self::MODEL_MAPPING[$type];

        // Find the source model
        $source = $modelClass::findByExternalId($externalId);
        if ( ! $source) {
            throw new InvalidArgumentException("Could not find {$type} with external ID: {$externalId}");
        }

        // Create the comment
        return $source->comments()->create([
            'description' => clean($description),
            'user_id'     => $userId,
        ]);
    }

    /**
     * Get supported comment types.
     *
     * @return array List of supported types
     */
    public function getSupportedTypes(): array
    {
        return array_keys(self::MODEL_MAPPING);
    }

    /**
     * Check if a type is supported.
     *
     * @param string $type The type to check
     *
     * @return bool True if supported
     */
    public function isTypeSupported(string $type): bool
    {
        return array_key_exists($type, self::MODEL_MAPPING);
    }

    /**
     * Delete a comment.
     *
     * @param Comment $comment The comment to delete
     *
     * @return bool True if deletion was successful
     */
    public function deleteComment(Comment $comment): bool
    {
        return (bool) $comment->delete();
    }

    /**
     * Update a comment.
     *
     * @param Comment $comment     The comment to update
     * @param string  $description The new description
     *
     * @return bool True if update was successful
     */
    public function updateComment(Comment $comment, string $description): bool
    {
        return $comment->update([
            'description' => clean($description),
        ]);
    }
}
