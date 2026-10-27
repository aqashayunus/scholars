<?php

namespace App\Services\Chat;

use App\Models\ChatAuditLog;

class AuditLogService
{
    /**
     * Records a parent-monitoring action against a child's chat activity.
     * Used only from the parental-monitoring module (MessagingService's
     * getChildConversations/getChildConversation).
     */
    public function log(int $viewerId, int $childId, ?int $conversationId, string $action): void
    {
        ChatAuditLog::create([
            'viewer_id'       => $viewerId,
            'child_id'        => $childId,
            'conversation_id' => $conversationId,
            'action'          => $action,
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
        ]);
    }
}
