<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Notification;
use App\Models\Feedback;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Device;
use App\Models\Maintenance;
use App\Models\Event;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SearchController extends Controller
{
    /**
     * Global search across all entities
     */
    public function globalSearch(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all'); // all, users, apartments, notifications, etc.
        $limit = $request->input('limit', 10);

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $results = [];

        if ($type === 'all' || $type === 'users') {
            $results['users'] = $this->searchUsers($query, $limit);
        }

        if ($type === 'all' || $type === 'apartments') {
            $results['apartments'] = $this->searchApartments($query, $limit);
        }

        if ($type === 'all' || $type === 'notifications') {
            $results['notifications'] = $this->searchNotifications($query, $limit);
        }

        /*
        if ($type === 'all' || $type === 'feedbacks') {
            $results['feedbacks'] = $this->searchFeedbacks($query, $limit);
        }
        */

        if ($type === 'all' || $type === 'invoices') {
            $results['invoices'] = $this->searchInvoices($query, $limit);
        }

        if ($type === 'all' || $type === 'devices') {
            $results['devices'] = $this->searchDevices($query, $limit);
        }

        if ($type === 'all' || $type === 'maintenances') {
            $results['maintenances'] = $this->searchMaintenances($query, $limit);
        }

        if ($type === 'all' || $type === 'events') {
            $results['events'] = $this->searchEvents($query, $limit);
        }

        if ($type === 'all' || $type === 'votes') {
            $results['votes'] = $this->searchVotes($query, $limit);
        }

        if ($type === 'all' || $type === 'payments') {
            $results['payments'] = $this->searchPayments($query, $limit);
        }

        $totalResults = 0;
        foreach ($results as $key => $items) {
            if (is_array($items)) {
                $totalResults += count($items);
            } elseif (is_object($items) && method_exists($items, 'count')) {
                $totalResults += $items->count();
            }
        }

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $results,
            'total_results' => $totalResults
        ]);
    }

    /**
     * Advanced search with filters
     */
    public function advancedSearch(Request $request)
    {
        $request->validate([
            'entity' => 'required|string|in:users,apartments,notifications,feedbacks,invoices,payments,devices,maintenances,events,votes',
            'filters' => 'required|array',
            'sort_by' => 'sometimes|string',
            'sort_order' => 'sometimes|string|in:asc,desc',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $entity = $request->input('entity');
        $filters = $request->input('filters');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        switch ($entity) {
            case 'users':
                $results = $this->advancedSearchUsers($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'apartments':
                $results = $this->advancedSearchApartments($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'notifications':
                $results = $this->advancedSearchNotifications($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'feedbacks':
                $results = $this->advancedSearchFeedbacks($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'invoices':
                $results = $this->advancedSearchInvoices($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'payments':
                $results = $this->advancedSearchPayments($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'devices':
                $results = $this->advancedSearchDevices($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'maintenances':
                $results = $this->advancedSearchMaintenances($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'events':
                $results = $this->advancedSearchEvents($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            case 'votes':
                $results = $this->advancedSearchVotes($filters, $sortBy, $sortOrder, $page, $perPage);
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid entity type'
                ], 400);
        }

        return response()->json([
            'success' => true,
            'entity' => $entity,
            'filters' => $filters,
            'results' => $results
        ]);
    }

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $limit = $request->input('limit', 5);

        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'suggestions' => []
            ]);
        }

        $suggestions = [];

        if ($type === 'all' || $type === 'users') {
            $userSuggestions = User::where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get(['id', 'name', 'email'])
                ->map(function($user) {
                    return [
                        'type' => 'user',
                        'id' => $user->id,
                        'text' => $user->name,
                        'subtitle' => $user->email,
                        'url' => "/users/{$user->id}"
                    ];
                });
            $suggestions = array_merge($suggestions, $userSuggestions->toArray());
        }

        if ($type === 'all' || $type === 'apartments') {
            $apartmentSuggestions = Apartment::where('apartment_number', 'LIKE', "%{$query}%")
                ->orWhere('block', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get(['id', 'apartment_number', 'block', 'floor'])
                ->map(function($apartment) {
                    return [
                        'type' => 'apartment',
                        'id' => $apartment->id,
                        'text' => $apartment->apartment_number,
                        'subtitle' => "Block {$apartment->block}, Floor {$apartment->floor}",
                        'url' => "/apartments/{$apartment->id}"
                    ];
                });
            $suggestions = array_merge($suggestions, $apartmentSuggestions->toArray());
        }

        return response()->json([
            'success' => true,
            'suggestions' => array_slice($suggestions, 0, $limit)
        ]);
    }

    // Private helper methods for specific searches

    private function searchUsers($query, $limit)
    {
        return User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'name', 'email', 'role', 'status']);
    }

    private function searchApartments($query, $limit)
    {
        return Apartment::where('apartment_number', 'LIKE', "%{$query}%")
            ->orWhere('block', 'LIKE', "%{$query}%")
            ->orWhere('floor', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    private function searchNotifications($query, $limit)
    {
        return Notification::where('title', 'LIKE', "%{$query}%")
            ->orWhere('message', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    private function searchFeedbacks($query, $limit)
    {
        return Feedback::where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    private function searchInvoices($query, $limit)
    {
        return Invoice::where('invoice_number', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    private function searchDevices($query, $limit)
    {
        return Device::where('name', 'LIKE', "%{$query}%")
            ->orWhere('type', 'LIKE', "%{$query}%")
            ->orWhere('location', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    private function searchEvents($query, $limit)
    {
        return Event::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    private function searchMaintenances($query, $limit)
    {
        return Maintenance::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('type', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    private function searchVotes($query, $limit)
    {
        return Vote::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    private function searchPayments($query, $limit)
    {
        return Payment::where('method', 'LIKE', "%{$query}%")
            ->orWhere('status', 'LIKE', "%{$query}%")
            ->orWhere('reference_number', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    // Advanced search methods

    private function advancedSearchUsers($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = User::query();

        if (isset($filters['name'])) {
            $query->where('name', 'LIKE', "%{$filters['name']}%");
        }

        if (isset($filters['email'])) {
            $query->where('email', 'LIKE', "%{$filters['email']}%");
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['created_from']));
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['created_to']));
        }

        if (isset($filters['apartment_block'])) {
            $query->whereHas('apartment', function($q) use ($filters) {
                $q->where('block', $filters['apartment_block']);
            });
        }

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchApartments($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Apartment::query();

        if (isset($filters['apartment_number'])) {
            $query->where('apartment_number', 'LIKE', "%{$filters['apartment_number']}%");
        }

        if (isset($filters['block'])) {
            $query->where('block', $filters['block']);
        }

        if (isset($filters['floor'])) {
            $query->where('floor', $filters['floor']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['area_from'])) {
            $query->where('area', '>=', $filters['area_from']);
        }

        if (isset($filters['area_to'])) {
            $query->where('area', '<=', $filters['area_to']);
        }

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchNotifications($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Notification::query();

        if (isset($filters['title'])) {
            $query->where('title', 'LIKE', "%{$filters['title']}%");
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['scope'])) {
            $query->where('scope', $filters['scope']);
        }

        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['created_from']));
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['created_to']));
        }

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchFeedbacks($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Feedback::query();

        if (isset($filters['title'])) {
            $query->where('title', 'LIKE', "%{$filters['title']}%");
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['created_from']));
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['created_to']));
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->with(['user', 'assignedTo'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchInvoices($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Invoice::query();

        if (isset($filters['invoice_number'])) {
            $query->where('invoice_number', 'LIKE', "%{$filters['invoice_number']}%");
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['amount_from'])) {
            $query->where('amount', '>=', $filters['amount_from']);
        }

        if (isset($filters['amount_to'])) {
            $query->where('amount', '<=', $filters['amount_to']);
        }

        if (isset($filters['due_from'])) {
            $query->where('due_date', '>=', Carbon::parse($filters['due_from']));
        }

        if (isset($filters['due_to'])) {
            $query->where('due_date', '<=', Carbon::parse($filters['due_to']));
        }

        if (isset($filters['apartment_id'])) {
            $query->where('apartment_id', $filters['apartment_id']);
        }

        return $query->with(['apartment'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchPayments($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Payment::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (isset($filters['amount_from'])) {
            $query->where('amount', '>=', $filters['amount_from']);
        }

        if (isset($filters['amount_to'])) {
            $query->where('amount', '<=', $filters['amount_to']);
        }

        if (isset($filters['created_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['created_from']));
        }

        if (isset($filters['created_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['created_to']));
        }

        return $query->with(['invoice'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchDevices($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Device::query();

        if (isset($filters['name'])) {
            $query->where('name', 'LIKE', "%{$filters['name']}%");
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['location'])) {
            $query->where('location', 'LIKE', "%{$filters['location']}%");
        }

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchMaintenances($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Maintenance::query();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['scheduled_from'])) {
            $query->where('scheduled_date', '>=', Carbon::parse($filters['scheduled_from']));
        }

        if (isset($filters['scheduled_to'])) {
            $query->where('scheduled_date', '<=', Carbon::parse($filters['scheduled_to']));
        }

        if (isset($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        return $query->with(['device', 'assignedTo'])
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchEvents($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Event::query();

        if (isset($filters['title'])) {
            $query->where('title', 'LIKE', "%{$filters['title']}%");
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['scope'])) {
            $query->where('scope', $filters['scope']);
        }

        if (isset($filters['start_from'])) {
            $query->where('start_date', '>=', Carbon::parse($filters['start_from']));
        }

        if (isset($filters['start_to'])) {
            $query->where('start_date', '<=', Carbon::parse($filters['start_to']));
        }

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);
    }

    private function advancedSearchVotes($filters, $sortBy, $sortOrder, $page, $perPage)
    {
        $query = Vote::query();

        if (isset($filters['title'])) {
            $query->where('title', 'LIKE', "%{$filters['title']}%");
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_from'])) {
            $query->where('start_date', '>=', Carbon::parse($filters['start_from']));
        }

        if (isset($filters['end_to'])) {
            $query->where('end_date', '<=', Carbon::parse($filters['end_to']));
        }

        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);
    }
}
