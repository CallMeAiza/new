<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Send notification to specific users
     */
    public function sendToUsers(array $userIds, string $title, string $message, string $type = 'info', array $data = [])
    {
        foreach ($userIds as $userId) {
            $this->createNotification($userId, $title, $message, $type, $data);
        }
    }

    /**
     * Send notification to users by role
     */
    public function sendToRole(string $role, string $title, string $message, string $type = 'info', array $data = [])
    {
        $users = User::where('user_role', $role)->get();
        foreach ($users as $user) {
            $this->createNotification($user->user_id, $title, $message, $type, $data);
        }
    }

    /**
     * Send notification to all users except sender
     */
    public function sendToAll(string $title, string $message, string $type = 'info', array $data = [])
    {
        $users = User::where('user_id', '!=', Auth::id())->get();
        foreach ($users as $user) {
            $this->createNotification($user->user_id, $title, $message, $type, $data);
        }
    }

    /**
     * Create individual notification
     */
    public function createNotification(string $userId, string $title, string $message, string $type, array $data)
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'read_at' => null
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to create notification: ' . $e->getMessage());
        }
    }

    /**
     * Menu Planning Notifications
     */
    public function menuCreated($menuData)
    {
        $this->sendToRole('kitchen',
            'New Menu Available',
            'Cook has created a new menu for ' . $menuData['day'] . '. You can now view and update meal status.',
            'menu_update',
            ['action_url' => '/kitchen/daily-menu', 'menu_data' => $menuData, 'feature' => 'kitchen.daily-menu']
        );

        $this->sendToRole('student',
            'New Menu Available',
            'Today\'s menu has been updated! Check out what\'s available for ' . $menuData['day'] . '.',
            'menu_update',
            ['action_url' => '/student/menu', 'menu_data' => $menuData, 'feature' => 'student.menu']
        );
    }

    public function menuUpdated($menuData)
    {
        $this->sendToRole('kitchen',
            'Menu Updated',
            'Cook has updated the menu for ' . $menuData['day'] . '. Please review the changes.',
            'menu_update',
            ['action_url' => '/kitchen/daily-menu', 'menu_data' => $menuData, 'feature' => 'kitchen.daily-menu']
        );

        $this->sendToRole('student',
            'Menu Updated',
            'The menu for ' . $menuData['day'] . ' has been updated. Check out the changes!',
            'menu_update',
            ['action_url' => '/student/menu', 'menu_data' => $menuData, 'feature' => 'student.menu']
        );
    }

    /**
     * Pre-Order Notifications
     */
    public function pollCreated($pollData)
    {
        $this->sendToRole('student',
            'New Meal Poll Available',
            'Kitchen has created a new meal poll. Please submit your meal preferences.',
            'poll_created',
            ['action_url' => '/student/pre-order', 'poll_data' => $pollData, 'feature' => 'student.pre-order']
        );

        // Note: Cook/admin no longer has access to pre-orders interface
        // Poll creation notifications are only sent to students
    }

    public function pollResponseSubmitted($responseData)
    {
        $this->sendToRole('kitchen',
            'New Poll Response',
            'A student has submitted their meal preferences for the poll.',
            'poll_response',
            ['action_url' => '/kitchen/pre-orders', 'response_data' => $responseData, 'feature' => 'kitchen.pre-orders']
        );

        // Note: Cook/admin no longer has access to pre-orders interface
        // Poll response notifications are only handled by kitchen team
    }

    /**
     * Inventory Notifications
     */
    public function inventoryReportSubmitted($reportData)
    {
        $this->sendToRole('cook',
            'New Inventory Report',
            'Kitchen team has submitted an inventory check report. Please review and approve.',
            'inventory_report',
            ['action_url' => '/cook/inventory/report/' . $reportData['id'], 'report_data' => $reportData, 'feature' => 'cook.inventory']
        );
    }

    public function inventoryReportApproved($reportData)
    {
        $message = 'Your inventory report has been approved by the cook team.';
        if (!empty($reportData['approval_notes'])) {
            $message .= ' Cook\'s message: "' . $reportData['approval_notes'] . '"';
        }

        $this->sendToUsers([$reportData['submitted_by']],
            'Inventory Report Approved',
            $message,
            'inventory_approved',
            ['action_url' => '/kitchen/inventory', 'report_data' => $reportData, 'feature' => 'kitchen.inventory']
        );
    }

    public function inventoryReportReply($reportData)
    {
        $message = 'Cook team has replied to your inventory report: "' . $reportData['approval_notes'] . '"';

        $this->sendToUsers([$reportData['submitted_by']],
            'Cook Reply to Inventory Report',
            $message,
            'inventory_reply',
            ['action_url' => '/kitchen/inventory', 'report_data' => $reportData, 'feature' => 'kitchen.inventory']
        );
    }

    /**
     * Notify cook about delivery receipt submission
     */
    public function deliveryReceiptSubmitted(array $data)
    {
        $this->sendToRole('cook',
            'New Delivery Receipt',
            "Kitchen has submitted delivery receipt {$data['receipt_number']} from {$data['supplier']} with {$data['total_items']} items (₱{$data['total_value']}).",
            'delivery_receipt',
            ['action_url' => '/cook/stock-management/receipts/' . $data['receipt_id'], 'receipt_data' => $data, 'feature' => 'cook.stock-management']
        );
    }

    /**
     * Notify kitchen about delivery receipt approval
     */
    public function deliveryReceiptApproved(array $data)
    {
        $this->sendToUsers([$data['received_by']],
            'Delivery Receipt Approved',
            "Your delivery receipt {$data['receipt_number']} has been approved by the cook team.",
            'delivery_approved',
            ['action_url' => '/kitchen/inventory', 'receipt_data' => $data, 'feature' => 'kitchen.inventory']
        );
    }

    public function inventoryReportDeleted($reportData)
    {
        $this->sendToUsers([$reportData['submitted_by']],
            'Inventory Report Deleted',
            'Your inventory report has been deleted by the cook/admin team.',
            'inventory_deleted',
            ['action_url' => '/kitchen/inventory', 'report_data' => $reportData, 'feature' => 'kitchen.inventory']
        );
    }

    public function inventoryReportsCleared($reportData)
    {
        $this->sendToUsers([$reportData['submitted_by']],
            'All Inventory Reports Cleared',
            "All inventory reports have been cleared by the cook/admin team. {$reportData['total_reports']} of your reports were removed.",
            'inventory_cleared',
            ['action_url' => '/kitchen/inventory', 'report_data' => $reportData, 'feature' => 'kitchen.inventory']
        );
    }

    public function lowStockAlert($itemData)
    {
        $this->sendToRole('cook',
            'Low Stock Alert',
            $itemData['name'] . ' is running low. Current stock: ' . $itemData['quantity'] . ' ' . $itemData['unit'],
            'low_stock',
            ['action_url' => '/cook/inventory', 'item_data' => $itemData, 'feature' => 'cook.inventory']
        );

        $this->sendToRole('kitchen',
            'Low Stock Alert',
            $itemData['name'] . ' is running low. Please check inventory and report if restock is needed.',
            'low_stock',
            ['action_url' => '/kitchen/inventory', 'item_data' => $itemData, 'feature' => 'kitchen.inventory']
        );
    }

    /**
     * Feedback Notifications
     */
    public function feedbackSubmitted($feedbackData)
    {
        $this->sendToRole('cook',
            'New Student Feedback',
            'A student has submitted feedback about a meal. Rating: ' . $feedbackData['rating'] . '/5',
            'feedback_submitted',
            ['action_url' => '/cook/feedback', 'feedback_data' => $feedbackData, 'feature' => 'cook.feedback']
        );

        $this->sendToRole('kitchen',
            'New Student Feedback',
            'A student has provided feedback about a meal. Check the feedback to improve meal quality.',
            'feedback_submitted',
            ['action_url' => '/kitchen/feedback', 'feedback_data' => $feedbackData, 'feature' => 'kitchen.feedback']
        );
    }

    /**
     * Post-Meal Report Notifications
     */
    public function postMealReportSubmitted($reportData)
    {
        $this->sendToRole('cook',
            'New Post-Meal Report',
            'Kitchen team has submitted a post-meal report with waste assessment data.',
            'post_meal_report',
            ['action_url' => '/cook/post-assessment', 'report_data' => $reportData, 'feature' => 'cook.post-assessment']
        );
    }

    /**
     * General System Notifications
     */
    public function systemUpdate($title, $message)
    {
        $this->sendToAll($title, $message, 'system_update');
    }

    public function deadlineReminder($userRole, $title, $message, $actionUrl = null)
    {
        $data = $actionUrl ? ['action_url' => $actionUrl] : [];
        $this->sendToRole($userRole, $title, $message, 'deadline_reminder', $data);
    }

    /**
     * Send notification when purchase order is created
     */
    public function purchaseOrderCreated($purchaseOrder)
    {
        try {
            // Notify kitchen staff about new purchase order
            $kitchenUsers = User::where('user_role', 'kitchen')->get();

            foreach ($kitchenUsers as $user) {
                Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'purchase_order_created',
                    'title' => 'New Purchase Order Created',
                    'message' => "Purchase order {$purchaseOrder->order_number} has been created by {$purchaseOrder->creator->user_fname} {$purchaseOrder->creator->user_lname}",
                    'data' => json_encode([
                        'purchase_order_id' => $purchaseOrder->id,
                        'order_number' => $purchaseOrder->order_number,
                        'total_amount' => $purchaseOrder->total_amount,
                        'items_count' => $purchaseOrder->items->count()
                    ])
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send purchase order created notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification when purchase order is approved
     */
    public function purchaseOrderApproved($purchaseOrder)
    {
        try {
            // Notify kitchen staff about approved purchase order
            $kitchenUsers = User::where('user_role', 'kitchen')->get();

            foreach ($kitchenUsers as $user) {
                Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'purchase_order_approved',
                    'title' => 'Purchase Order Approved',
                    'message' => "Purchase order {$purchaseOrder->order_number} has been approved and is ready for delivery confirmation",
                    'data' => json_encode([
                        'purchase_order_id' => $purchaseOrder->id,
                        'order_number' => $purchaseOrder->order_number,
                        'approved_by' => $purchaseOrder->approver->user_fname . ' ' . $purchaseOrder->approver->user_lname
                    ])
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send purchase order approved notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification when purchase order is delivered
     */
    public function purchaseOrderDelivered($purchaseOrder)
    {
        try {
            // Notify cook who created the order
            Notification::create([
                'user_id' => $purchaseOrder->created_by,
                'type' => 'purchase_order_delivered',
                'title' => 'Purchase Order Delivered',
                'message' => "Purchase order {$purchaseOrder->order_number} has been delivered and inventory has been updated",
                'data' => json_encode([
                    'purchase_order_id' => $purchaseOrder->id,
                    'order_number' => $purchaseOrder->order_number,
                    'delivered_by' => $purchaseOrder->deliveryConfirmer->user_fname . ' ' . $purchaseOrder->deliveryConfirmer->user_lname,
                    'delivery_date' => $purchaseOrder->actual_delivery_date
                ])
            ]);

            // Also notify other cooks
            $cookUsers = User::where('user_role', 'cook')
                           ->where('user_id', '!=', $purchaseOrder->created_by)
                           ->get();

            foreach ($cookUsers as $user) {
                Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'purchase_order_delivered',
                    'title' => 'Purchase Order Delivered',
                    'message' => "Purchase order {$purchaseOrder->order_number} has been delivered - inventory updated",
                    'data' => json_encode([
                        'purchase_order_id' => $purchaseOrder->id,
                        'order_number' => $purchaseOrder->order_number
                    ])
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send purchase order delivered notification: ' . $e->getMessage());
        }
    }

    /**
     * Send notification when inventory report is created (including auto-generated ones)
     */
    public function inventoryReportCreated($reportData)
    {
        try {
            $message = $reportData['auto_generated'] ?? false
                ? "Auto-generated inventory report created for {$reportData['reason']}. {$reportData['items_count']} items checked."
                : "New inventory report submitted by {$reportData['submitted_by']} with {$reportData['items_count']} items.";

            $this->sendToRole('cook',
                'New Inventory Report',
                $message,
                'inventory_report',
                ['action_url' => '/cook/inventory/report/' . $reportData['id'], 'report_data' => $reportData, 'feature' => 'cook.inventory']
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send inventory report created notification: ' . $e->getMessage());
        }
    }
}
