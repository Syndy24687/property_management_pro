<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest\StoreMaintenanceRequestRequest;
use App\Http\Requests\MaintenanceRequest\UpdateMaintenanceRequestRequest;
use App\Http\Resources\MaintenanceCommentResource;
use App\Http\Resources\MaintenanceRequestResource;
use App\Models\Document;
use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use App\Notifications\MaintenanceRequestAssigned;
use App\Notifications\MaintenanceStatusChanged;
use App\Services\ImageUploadService;
use App\Services\MaintenanceRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaintenanceRequestController extends Controller
{
    public function __construct(
        protected MaintenanceRequestService $maintenanceService,
        protected ImageUploadService        $imageService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'priority', 'unit_id', 'tenant_id', 'category_id', 'assigned_to']);
        return MaintenanceRequestResource::collection($this->maintenanceService->getAllRequests($filters, $request->integer('per_page', 15)));
    }

    public function store(StoreMaintenanceRequestRequest $request): JsonResponse
    {
        $maintenanceRequest = $this->maintenanceService->createRequest($request->validated());

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                Document::create([
                    'uploaded_by'       => auth('api')->id(),
                    'documentable_type' => MaintenanceRequest::class,
                    'documentable_id'   => $maintenanceRequest->id,
                    'title'             => $file->getClientOriginalName(),
                    'file_path'         => $file->store('documents/maintenance/' . $maintenanceRequest->id, 'public'),
                    'file_name'         => $file->getClientOriginalName(),
                    'mime_type'         => $file->getMimeType(),
                    'file_size'         => $file->getSize(),
                    'category'          => 'photo',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request created successfully.',
            'data'    => new MaintenanceRequestResource($maintenanceRequest->load(['unit.property', 'tenant', 'category', 'documents'])),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $maintenanceRequest = $this->maintenanceService->getRequest($id);
        if (!$maintenanceRequest) {
            return response()->json(['success' => false, 'message' => 'Maintenance request not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new MaintenanceRequestResource($maintenanceRequest->load(['unit.property', 'tenant', 'category', 'assignee', 'comments.user', 'documents'])),
        ]);
    }

    public function update(UpdateMaintenanceRequestRequest $request, int $id): JsonResponse
    {
        $oldRequest = $this->maintenanceService->getRequest($id);
        $oldStatus = $oldRequest->status;

        $maintenanceRequest = $this->maintenanceService->updateRequest($id, $request->validated());

        // Notify tenant on status change
        if ($oldStatus !== $maintenanceRequest->status) {
            try {
                $maintenanceRequest->tenant->notify(new MaintenanceStatusChanged($maintenanceRequest));
            } catch (\Throwable $e) {
                // Don't fail if notification fails
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request updated.',
            'data'    => new MaintenanceRequestResource($maintenanceRequest),
        ]);
    }

    /**
     * POST /api/v1/maintenance-requests/{id}/assign
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $maintenanceRequest = $this->maintenanceService->updateRequest($id, [
            'assigned_to' => $validated['assigned_to'],
            'status'      => 'in_progress',
        ]);

        // Notify assigned user
        try {
            $maintenanceRequest->assignee->notify(new MaintenanceRequestAssigned($maintenanceRequest));
        } catch (\Throwable $e) {
            // Don't fail if notification fails
        }

        return response()->json([
            'success' => true,
            'message' => 'Request assigned successfully.',
            'data'    => new MaintenanceRequestResource($maintenanceRequest->load('assignee')),
        ]);
    }

    /**
     * POST /api/v1/maintenance-requests/{id}/comments
     */
    public function storeComment(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'comment'     => ['required', 'string'],
            'is_internal' => ['sometimes', 'boolean'],
        ]);

        $comment = MaintenanceComment::create([
            'maintenance_request_id' => $id,
            'user_id'                => auth('api')->id(),
            'comment'                => $validated['comment'],
            'is_internal'            => $validated['is_internal'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added.',
            'data'    => new MaintenanceCommentResource($comment->load('user')),
        ], 201);
    }

    /**
     * GET /api/v1/maintenance-requests/{id}/comments
     */
    public function indexComments(int $id): JsonResponse
    {
        $user = auth('api')->user();
        $query = MaintenanceComment::where('maintenance_request_id', $id)->with('user');

        // Tenants/occupants can't see internal comments
        if ($user->hasRole(['tenant', 'occupant']) && !$user->hasAnyRole(['super-admin', 'admin', 'owner', 'manager'])) {
            $query->where('is_internal', false);
        }

        return response()->json([
            'success' => true,
            'data'    => MaintenanceCommentResource::collection($query->orderBy('created_at')->get()),
        ]);
    }

    /**
     * POST /api/v1/maintenance-requests/{id}/attachments
     */
    public function storeAttachment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'files'   => ['required', 'array', 'max:5'],
            'files.*' => ['required', 'file', 'max:10240'], // 10MB max
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($id);
        $attachments = [];

        foreach ($request->file('files') as $file) {
            $attachments[] = Document::create([
                'uploaded_by'       => auth('api')->id(),
                'documentable_type' => MaintenanceRequest::class,
                'documentable_id'   => $maintenanceRequest->id,
                'title'             => $file->getClientOriginalName(),
                'file_path'         => $file->store('documents/maintenance/' . $id, 'public'),
                'file_name'         => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'file_size'         => $file->getSize(),
                'category'          => 'photo',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($attachments) . ' attachment(s) uploaded.',
            'data'    => $attachments,
        ], 201);
    }

    /**
     * GET /api/v1/maintenance-requests/{id}/attachments
     */
    public function indexAttachments(int $id): JsonResponse
    {
        $docs = Document::where('documentable_type', MaintenanceRequest::class)
            ->where('documentable_id', $id)
            ->get()
            ->map(fn($d) => [
                'id'        => $d->id,
                'title'     => $d->title,
                'file_name' => $d->file_name,
                'url'       => \Storage::url($d->file_path),
                'mime_type' => $d->mime_type,
                'file_size' => $d->formatted_size,
                'uploaded_at' => $d->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $docs,
        ]);
    }
}
