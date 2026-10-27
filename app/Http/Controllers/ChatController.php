<?php

namespace App\Http\Controllers;

use App\Services\Chat\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ChatController extends Controller
{
    public function __construct(private MessagingService $messaging)
    {
    }

    public function getGlobalUnreadCount(): JsonResponse
    {
        return $this->messaging->getGlobalUnreadCount();
    }

    public function getContacts(): JsonResponse
    {
        return $this->messaging->getContacts();
    }

    public function getDirectory(Request $request): JsonResponse
    {
        return $this->messaging->getDirectory($request);
    }

    public function findOrCreateConversation(Request $request): JsonResponse
    {
        return $this->messaging->findOrCreateConversation($request);
    }

    public function getMessages(Request $request, int $conversationId): JsonResponse
    {
        return $this->messaging->getMessages($request, $conversationId);
    }

    public function getOlderMessages(Request $request, int $conversationId): JsonResponse
    {
        return $this->messaging->getOlderMessages($request, $conversationId);
    }

    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        return $this->messaging->sendMessage($request, $conversationId);
    }

    public function uploadAttachment(Request $request, int $conversationId): JsonResponse
    {
        return $this->messaging->uploadAttachment($request, $conversationId);
    }

    public function downloadAttachment(int $attachmentId): BinaryFileResponse|JsonResponse
    {
        return $this->messaging->downloadAttachment($attachmentId);
    }

    public function setTyping(Request $request, int $conversationId): JsonResponse
    {
        return $this->messaging->setTyping($request, $conversationId);
    }

    public function getTyping(int $conversationId): JsonResponse
    {
        return $this->messaging->getTyping($conversationId);
    }

    public function muteConversation(int $conversationId): JsonResponse
    {
        return $this->messaging->muteConversation($conversationId);
    }

    public function unmuteConversation(int $conversationId): JsonResponse
    {
        return $this->messaging->unmuteConversation($conversationId);
    }

    public function archiveConversation(int $conversationId): JsonResponse
    {
        return $this->messaging->archiveConversation($conversationId);
    }

    public function clearChat(int $conversationId): JsonResponse
    {
        return $this->messaging->clearChat($conversationId);
    }

    public function clearAllChats(Request $request): JsonResponse
    {
        return $this->messaging->clearAllChats($request);
    }

    public function deleteConversation(int $conversationId): JsonResponse
    {
        return $this->messaging->deleteConversation($conversationId);
    }

    public function getMonitoredChildren(): JsonResponse
    {
        return $this->messaging->getMonitoredChildren();
    }

    public function getChildConversations(int $studentId): JsonResponse
    {
        return $this->messaging->getChildConversations($studentId);
    }

    public function getChildConversation(Request $request, int $studentId, int $conversationId): JsonResponse
    {
        return $this->messaging->getChildConversation($request, $studentId, $conversationId);
    }

    public function adminGetContacts(Request $request): JsonResponse
    {
        return $this->messaging->adminGetContacts($request);
    }

    public function adminUpdateStatus(Request $request, int $conversationId): JsonResponse
    {
        return $this->messaging->adminUpdateStatus($request, $conversationId);
    }

    public function adminGetUserContext(int $userId): JsonResponse
    {
        return $this->messaging->adminGetUserContext($userId);
    }
}
