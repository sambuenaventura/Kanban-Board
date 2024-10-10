<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\BoardUser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BoardService
{
    protected $boardModel;
    protected $taskModel;
    protected $boardUserModel;
    protected $userModel;
    protected $boardInvitationModel;
    protected $idempotencyService;

    // Inject the models through the constructor
    public function __construct(Board $boardModel, Task $taskModel, BoardUser $boardUserModel, User $userModel, BoardInvitation $boardInvitationModel, IdempotencyService $idempotencyService)
    {
        $this->boardModel = $boardModel;
        $this->taskModel = $taskModel;
        $this->boardUserModel = $boardUserModel;
        $this->userModel = $userModel;
        $this->boardInvitationModel = $boardInvitationModel;
        $this->idempotencyService = $idempotencyService;
    }
    
    public function getBoardWithTasksAndCollaborators($id)
    {
        return $this->boardModel->with('tasks', 'collaborators')->findOrFail($id);
    }

    public function getBoards($userId, $isOwner = true)
    {
        $query = $this->boardModel->with(['user', 'boardUsers.user'])
            ->withCount(['tasks', 'boardUsers'])
            ->withCount([
                'tasks as overdue_tasks_count' => function ($query) {
                    $query->where('due', '<', now())->where('progress', '!=', 'done');
                },
                'tasks as due_today_tasks_count' => function ($query) {
                    $query->where('due', '=', now()->startOfDay())->where('progress', '!=', 'done');
                },
                'tasks as due_soon_tasks_count' => function ($query) {
                    $query->where('due', '>', now()->endOfDay())
                        ->where('due', '<=', now()->addWeek())
                        ->where('progress', '!=', 'done');
                },
            ]);

        if ($isOwner) {
            // Filter by owner
            $query->where('user_id', $userId);
        } else {
            // Filter by collaborators from boardUsers
            $query->whereHas('boardUsers', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('role', 'collaborator');
            });
        }
    
        return $query->paginate(6, ['*'], $isOwner ? 'owned_page' : 'collaborated_page');
    }

    public function getBoardById(string $id)
    {
        return $this->boardModel->find($id);
    }

    public function getOwnedBoards($userId)
    {
        return $this->getBoards($userId, true);
    }

    public function getCollaboratedBoards($userId)
    {
        return $this->getBoards($userId, false);
    }

    public function addTaskCountsToBoards($boards)
    {
        foreach ($boards as $board) {
            $board->taskCounts = [
                'overdue' => $board->overdue_tasks_count,
                'dueToday' => $board->due_today_tasks_count,
                'dueSoon' => $board->due_soon_tasks_count,
            ];
        }

        return $boards;
    }

    public function sortCollaborators($boardUsers, $userId, $limit = 3)
    {
        $owner = null;
        $authUser = null;
        $otherCollaborators = [];
    
        foreach ($boardUsers as $user) {
            if ($user->role === 'owner') {
                $owner = $user;
            } elseif ($user->user_id === $userId) {
                $authUser = $user;
            } else {
                $otherCollaborators[] = $user;
            }
        }
    
        $sortedCollaborators = [];
        if ($owner) {
            $sortedCollaborators[] = $owner;
        }
        if ($authUser && $authUser !== $owner) {
            $sortedCollaborators[] = $authUser;
        }
    
        $remainingSlots = $limit - count($sortedCollaborators);
        $sortedCollaborators = array_merge($sortedCollaborators, array_slice($otherCollaborators, 0, $remainingSlots));
    
        $remainingCount = count($boardUsers) - count($sortedCollaborators);
    
        if ($remainingCount > 0) {
            $sortedCollaborators[] = ['remaining_count' => $remainingCount];
        }
    
        return $sortedCollaborators;
    }
    

    public function createBoard(array $data)
    {
        $board = $this->boardModel->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'user_id' => Auth::id(),
        ]);
        // Add the creator to the board_users table as the owner
        $this->boardUserModel->create([
            'board_id' => $board->id,
            'user_id' => Auth::id(),
            'role' => 'owner',
        ]);
        
        return $board;
    }

    public function getCollaborators($board)
    {
        return $board->collaborators ?? collect();
    }
    
    public function getNonCollaboratorsExcludingInvited($board, $pendingInvitations)
    {
        $collaborators = $this->getCollaborators($board)->keyBy('id'); // Eager loading and keying by ID
        $invitedUserIds = $pendingInvitations->pluck('user_id')->toArray(); // Collect IDs as an array
        
        return $this->userModel->whereDoesntHave('boards', function ($query) use ($board) {
                $query->where('boards.id', $board->id);
            })
            ->where('id', '!=', auth()->id())
            ->whereNotIn('id', $collaborators->keys()) // Use keys directly from the keyed collection
            ->whereNotIn('id', $invitedUserIds)
            ->get();
    }
    
    public function getPendingInvitations($board)
    {
        return $this->boardInvitationModel->with('invitedUser')
                                          ->where('board_id', $board->id)
                                          ->where('status', 'pending')
                                          ->get();
    }

    public function deleteBoard(string $id, string $idempotencyKey)
    {
        return $this->idempotencyService->process("delete_board_{$id}", $idempotencyKey, function () use ($id) 
            {
                $board = $this->boardModel->findOrFail($id);
                $board->delete();
                return [
                    'status' => 'success',
                    'message' => 'Board deleted successfully.',
                ];
            }
        );
    }
}