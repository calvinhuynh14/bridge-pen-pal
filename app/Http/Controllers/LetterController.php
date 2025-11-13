<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LetterController extends Controller
{
    /**
     * Validation rules for letter creation
     */
    private function getValidationRules()
    {
        return [
            'content' => 'required|string|max:1000',
            'receiver_id' => 'nullable|exists:users,id',
            'is_open_letter' => 'boolean',
            'parent_letter_id' => 'nullable|exists:letters,id',
        ];
    }

    /**
     * Get open letters for the Discover page
     * Volunteers can only see open letters from residents
     * Residents can see open letters from other residents
     */
    public function getOpenLetters(Request $request)
    {
        $user = Auth::user();
        $userType = $user->user_type;

        // Base query for open letters
        $query = "
            SELECT 
                l.id,
                l.content,
                l.sent_at,
                l.created_at,
                l.claimed_by,
                l.status,
                sender.id as sender_id,
                sender.name as sender_name,
                ut.name as sender_type,
                receiver.id as receiver_id,
                receiver.name as receiver_name
            FROM letters l
            JOIN users sender ON l.sender_id = sender.id
            JOIN user_types ut ON sender.user_type_id = ut.id
            LEFT JOIN users receiver ON l.receiver_id = receiver.id
            WHERE l.is_open_letter = 1
            AND l.status IN ('sent', 'delivered')
            AND l.deleted_at IS NULL
        ";

        $params = [];

        // Filter based on user type
        if ($userType === 'volunteer') {
            // Volunteers can only see open letters from residents
            $query .= " AND ut.name = 'resident'";
        } elseif ($userType === 'resident') {
            // Residents can see open letters from other residents
            $query .= " AND ut.name = 'resident'";
        }

        // Exclude letters already claimed by current user
        $query .= " AND (l.claimed_by IS NULL OR l.claimed_by != ?)";
        $params[] = $user->id;

        // Exclude letters sent by current user (users cannot see their own open letters)
        $query .= " AND l.sender_id != ?";
        $params[] = $user->id;

        // Order by most recent first
        $query .= " ORDER BY l.sent_at DESC, l.created_at DESC";

        $openLetters = DB::select($query, $params);

        return response()->json([
            'letters' => $openLetters,
            'count' => count($openLetters)
        ]);
    }

    /**
     * Claim an open letter
     */
    public function claim(Request $request, $id)
    {
        $user = Auth::user();

        // Check if letter exists and is an open letter
        $letter = DB::selectOne("
            SELECT 
                id,
                is_open_letter,
                claimed_by,
                sender_id,
                status,
                deleted_at
            FROM letters
            WHERE id = ?
            AND deleted_at IS NULL
        ", [$id]);

        if (!$letter) {
            return response()->json(['error' => 'Letter not found'], 404);
        }

        if (!$letter->is_open_letter) {
            return response()->json(['error' => 'This is not an open letter'], 400);
        }

        if ($letter->claimed_by) {
            return response()->json(['error' => 'This letter has already been claimed'], 400);
        }

        if ($letter->sender_id == $user->id) {
            return response()->json(['error' => 'You cannot claim your own letter'], 400);
        }

        // Check user type restrictions
        $sender = DB::selectOne("
            SELECT user_type
            FROM users
            WHERE id = ?
        ", [$letter->sender_id]);

        if ($user->user_type === 'volunteer' && $sender->user_type === 'volunteer') {
            return response()->json(['error' => 'Volunteers cannot claim letters from other volunteers'], 400);
        }

        // Claim the letter
        DB::update("
            UPDATE letters
            SET claimed_by = ?,
                receiver_id = ?,
                updated_at = ?
            WHERE id = ?
        ", [$user->id, $user->id, now(), $id]);

        return response()->json([
            'message' => 'Letter claimed successfully',
            'letter_id' => $id
        ]);
    }

    /**
     * Report a letter
     */
    public function report(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if letter exists
        $letter = DB::selectOne("
            SELECT 
                id,
                sender_id,
                deleted_at
            FROM letters
            WHERE id = ?
            AND deleted_at IS NULL
        ", [$id]);

        if (!$letter) {
            return response()->json(['error' => 'Letter not found'], 404);
        }

        // Prevent users from reporting their own letters
        if ($letter->sender_id == $user->id) {
            return response()->json(['error' => 'You cannot report your own letter'], 400);
        }

        // Check if user has already reported this letter
        $existingReport = DB::selectOne("
            SELECT id
            FROM reports
            WHERE reporter_id = ?
            AND reported_letter_id = ?
            AND status = 'pending'
        ", [$user->id, $id]);

        if ($existingReport) {
            return response()->json(['error' => 'You have already reported this letter'], 400);
        }

        // Create the report
        DB::table('reports')->insert([
            'reporter_id' => $user->id,
            'reported_user_id' => $letter->sender_id,
            'reported_letter_id' => $id,
            'reason' => $request->reason,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Letter reported successfully. Our team will review it shortly.'
        ]);
    }

    /**
     * Store a new letter
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $data = $validator->validated();

        // Determine if this is an open letter
        $isOpenLetter = $data['is_open_letter'] ?? false;
        $receiverId = $isOpenLetter ? null : ($data['receiver_id'] ?? null);

        // If it's not an open letter, receiver_id is required
        if (!$isOpenLetter && !$receiverId) {
            return response()->json([
                'errors' => ['receiver_id' => ['Receiver is required for non-open letters']]
            ], 422);
        }

        // Insert the letter
        $letterId = DB::table('letters')->insertGetId([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'content' => $data['content'],
            'is_open_letter' => $isOpenLetter,
            'parent_letter_id' => $data['parent_letter_id'] ?? null,
            'status' => 'sent',
            'sent_at' => now(),
            'delivered_at' => now()->addHours(8), // 8-hour delay
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Letter sent successfully',
            'letter_id' => $letterId
        ], 201);
    }

    /**
     * Get received letters for a user
     */
    public function getReceived(Request $request)
    {
        $user = Auth::user();

        $letters = DB::select("
            SELECT 
                l.id,
                l.content,
                l.status,
                l.sent_at,
                l.delivered_at,
                l.read_at,
                l.created_at,
                sender.id as sender_id,
                sender.name as sender_name
            FROM letters l
            JOIN users sender ON l.sender_id = sender.id
            WHERE l.receiver_id = ?
            AND l.deleted_at IS NULL
            ORDER BY l.delivered_at DESC, l.created_at DESC
        ", [$user->id]);

        return response()->json([
            'letters' => $letters,
            'count' => count($letters)
        ]);
    }

    /**
     * Get sent letters for a user
     */
    public function getSent(Request $request)
    {
        $user = Auth::user();

        $letters = DB::select("
            SELECT 
                l.id,
                l.content,
                l.status,
                l.is_open_letter,
                l.sent_at,
                l.delivered_at,
                l.created_at,
                receiver.id as receiver_id,
                receiver.name as receiver_name,
                claimed_by_user.id as claimed_by_id,
                claimed_by_user.name as claimed_by_name
            FROM letters l
            LEFT JOIN users receiver ON l.receiver_id = receiver.id
            LEFT JOIN users claimed_by_user ON l.claimed_by = claimed_by_user.id
            WHERE l.sender_id = ?
            AND l.deleted_at IS NULL
            ORDER BY l.sent_at DESC, l.created_at DESC
        ", [$user->id]);

        return response()->json([
            'letters' => $letters,
            'count' => count($letters)
        ]);
    }

    /**
     * Get a single letter by ID
     */
    public function show($id)
    {
        $user = Auth::user();

        $letter = DB::selectOne("
            SELECT 
                l.*,
                sender.id as sender_id,
                sender.name as sender_name,
                receiver.id as receiver_id,
                receiver.name as receiver_name,
                claimed_by_user.id as claimed_by_id,
                claimed_by_user.name as claimed_by_name
            FROM letters l
            JOIN users sender ON l.sender_id = sender.id
            LEFT JOIN users receiver ON l.receiver_id = receiver.id
            LEFT JOIN users claimed_by_user ON l.claimed_by = claimed_by_user.id
            WHERE l.id = ?
            AND l.deleted_at IS NULL
        ", [$id]);

        if (!$letter) {
            return response()->json(['error' => 'Letter not found'], 404);
        }

        // Check if user has access to this letter
        if ($letter->sender_id != $user->id && $letter->receiver_id != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark as read if user is the receiver and hasn't read it yet
        if ($letter->receiver_id == $user->id && !$letter->read_at) {
            DB::update("
                UPDATE letters
                SET read_at = ?,
                    status = CASE 
                        WHEN status = 'delivered' THEN 'read'
                        ELSE status
                    END,
                    updated_at = ?
                WHERE id = ?
            ", [now(), now(), $id]);

            // Fetch the updated letter to get the correct status
            // Use the same query structure as the initial fetch to avoid column issues
            $letter = DB::selectOne("
                SELECT 
                    l.*,
                    sender.id as sender_id,
                    sender.name as sender_name,
                    receiver.id as receiver_id,
                    receiver.name as receiver_name,
                    claimed_by_user.id as claimed_by_id,
                    claimed_by_user.name as claimed_by_name
                FROM letters l
                JOIN users sender ON l.sender_id = sender.id
                LEFT JOIN users receiver ON l.receiver_id = receiver.id
                LEFT JOIN users claimed_by_user ON l.claimed_by = claimed_by_user.id
                WHERE l.id = ?
                AND l.deleted_at IS NULL
            ", [$id]);
        }

        return response()->json(['letter' => $letter]);
    }

    /**
     * Get correspondence between current user and a specific pen pal
     * Supports pagination, search, filter, and sort
     */
    public function getCorrespondence(Request $request, $penPalId)
    {
        $user = Auth::user();
        
        // Validate pen pal ID
        $penPal = DB::selectOne("SELECT id, name FROM users WHERE id = ?", [$penPalId]);
        if (!$penPal) {
            return response()->json(['error' => 'Pen pal not found'], 404);
        }

        // Get pagination parameters
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');
        $filter = $request->input('filter', 'all'); // 'all', 'me', 'them'
        $sort = $request->input('sort', 'newest'); // 'newest', 'oldest'

        // Build WHERE clause
        $whereClause = "
            WHERE l.deleted_at IS NULL
            AND (
                (l.sender_id = ? AND l.receiver_id = ?)
                OR (l.sender_id = ? AND l.receiver_id = ?)
            )
        ";

        $params = [$user->id, $penPalId, $penPalId, $user->id];

        // Apply sender filter
        if ($filter === 'me') {
            $whereClause .= " AND l.sender_id = ?";
            $params[] = $user->id;
        } elseif ($filter === 'them') {
            $whereClause .= " AND l.sender_id = ?";
            $params[] = $penPalId;
        }

        // Apply search filter
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $whereClause .= " AND (
                l.content LIKE ?
                OR sender.name LIKE ?
                OR receiver.name LIKE ?
                OR DATE_FORMAT(l.sent_at, '%Y-%m-%d') LIKE ?
                OR DATE_FORMAT(l.sent_at, '%M %d, %Y') LIKE ?
            )";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Build count query for pagination
        $countQuery = "
            SELECT COUNT(*) as total
            FROM letters l
            JOIN users sender ON l.sender_id = sender.id
            JOIN users receiver ON l.receiver_id = receiver.id
            " . $whereClause;
        
        $totalResult = DB::selectOne($countQuery, $params);
        $total = $totalResult->total;

        // Build main query with ORDER BY
        $query = "
            SELECT 
                l.id,
                l.content,
                l.sent_at,
                l.delivered_at,
                l.read_at,
                l.created_at,
                l.sender_id,
                sender.name as sender_name,
                l.receiver_id,
                receiver.name as receiver_name,
                l.is_open_letter,
                l.status
            FROM letters l
            JOIN users sender ON l.sender_id = sender.id
            JOIN users receiver ON l.receiver_id = receiver.id
            " . $whereClause;

        // Apply sort order
        if ($sort === 'oldest') {
            $query .= " ORDER BY l.sent_at ASC, l.created_at ASC";
        } else {
            $query .= " ORDER BY l.sent_at DESC, l.created_at DESC";
        }

        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        // Execute query
        $messages = DB::select($query, $params);

        // Calculate pagination metadata
        $lastPage = ceil($total / $perPage);

        return response()->json([
            'messages' => $messages,
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => $lastPage,
                'per_page' => (int)$perPage,
                'total' => $total,
                'has_more' => $page < $lastPage,
            ],
        ]);
    }

    /**
     * Get list of pen pals for the current user
     * Returns users who have sent or received letters with the current user
     * Supports pagination and search
     */
    public function getPenPals(Request $request)
    {
        $user = Auth::user();

        // Get pagination parameters
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        // Build WHERE clause
        $whereClause = "
            WHERE u.id IN (
                SELECT DISTINCT sender_id 
                FROM letters 
                WHERE receiver_id = ? 
                AND is_open_letter = 0
                AND deleted_at IS NULL
                
                UNION
                
                SELECT DISTINCT receiver_id 
                FROM letters 
                WHERE sender_id = ? 
                AND is_open_letter = 0
                AND deleted_at IS NULL
            )
            AND u.id != ?
        ";

        $params = [$user->id, $user->id, $user->id];

        // Apply search filter
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $whereClause .= " AND u.name LIKE ?";
            $params[] = $searchTerm;
        }

        // Build count query
        $countQuery = "
            SELECT COUNT(DISTINCT u.id) as total
            FROM users u
            " . $whereClause;
        
        $totalResult = DB::selectOne($countQuery, $params);
        $total = $totalResult->total;

        // Build main query with message status and unread count
        $query = "
            SELECT DISTINCT 
                u.id,
                u.name,
                u.email,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM letters l
                        WHERE ((l.sender_id = u.id AND l.receiver_id = ?) 
                               OR (l.sender_id = ? AND l.receiver_id = u.id))
                        AND l.is_open_letter = 0
                        AND l.deleted_at IS NULL
                    ) THEN 1
                    ELSE 0
                END as has_messages,
                COALESCE((
                    SELECT COUNT(*)
                    FROM letters l
                    WHERE l.sender_id = u.id
                    AND l.receiver_id = ?
                    AND l.is_open_letter = 0
                    AND l.read_at IS NULL
                    AND l.deleted_at IS NULL
                ), 0) as unread_count
            FROM users u
            " . $whereClause . "
            ORDER BY u.name ASC
        ";

        // Add user ID for has_messages and unread_count checks
        $paramsWithChecks = array_merge($params, [$user->id, $user->id, $user->id]);

        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ? OFFSET ?";
        $paramsWithChecks[] = $perPage;
        $paramsWithChecks[] = $offset;

        // Execute query
        $penPals = DB::select($query, $paramsWithChecks);

        // Calculate pagination metadata
        $lastPage = ceil($total / $perPage);

        return response()->json([
            'pen_pals' => $penPals,
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => $lastPage,
                'per_page' => (int)$perPage,
                'total' => $total,
                'has_more' => $page < $lastPage,
            ],
        ]);
    }
}

