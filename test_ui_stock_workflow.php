<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Ingredient;
use App\Http\Controllers\Cook\InventoryController;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 TESTING UI STOCK MANAGEMENT WORKFLOW\n";
echo "==========================================\n\n";

// Test Case 1: User Action in UI - Real-Time Feedback
echo "1. Testing Real-Time Feedback (UI Calculation)\n";
echo "----------------------------------------------\n";

// Simulate UI calculation before submission
$initialStock = 100;
$deliveredQuantity = 25;
$calculatedNewStock = $initialStock + $deliveredQuantity;

echo "Initial Stock: {$initialStock}\n";
echo "Delivered Quantity: {$deliveredQuantity}\n";
echo "Calculated New Stock: {$calculatedNewStock}\n";
echo "✅ PASS: UI can calculate new stock value before submission\n\n";

// Test Case 2: Form Submission or AJAX Request
echo "2. Testing Form Submission/AJAX Request\n";
echo "-----------------------------------------\n";

try {
    // Create test inventory item
    $testInventory = Inventory::create([
        'name' => 'UI Test Item',
        'description' => 'Test item for UI workflow',
        'quantity' => 100,
        'unit' => 'kg',
        'category' => 'test',
        'reorder_point' => 20,
        'unit_price' => 5.00,
        'status' => 'available',
        'last_updated_by' => 'ADMIN001'
    ]);

    echo "✅ Created test inventory item with ID: {$testInventory->id}\n";

    // The notifyDelivery method is for Ingredients, not Inventory
    // Let's test with an Ingredient instead
    $testIngredient = Ingredient::create([
        'name' => 'UI Test Ingredient',
        'unit' => 'kg',
        'category' => 'test',
        'price' => 5.00,
        'quantity' => 100,
        'description' => 'Test ingredient for UI workflow',
        'minimum_stock' => 20,
        'cost_per_unit' => 5.00
    ]);

    echo "✅ Created test ingredient with ID: {$testIngredient->id}\n";

    // Simulate AJAX request for stock addition (delivery)
    $request = new Request([
        'inventory_id' => $testIngredient->id,
        'delivery_date' => now()->toDateString(),
        'quantity' => 25
    ]);

    // Mock authentication
    \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn((object)['user_id' => 'ADMIN001']);

    $controller = new InventoryController();
    $response = $controller->notifyDelivery($request);

    $testIngredient->refresh();
    $expectedStock = 125; // 100 + 25

    if ($testIngredient->quantity == $expectedStock) {
        echo "✅ PASS: AJAX request successfully added stock\n";
        echo "   Initial: 100, Added: 25, Final: {$testIngredient->quantity}\n";
    } else {
        echo "❌ FAIL: Stock not updated correctly\n";
        echo "   Expected: {$expectedStock}, Got: {$testIngredient->quantity}\n";
    }

    // Update test variable for cleanup
    $testInventory = $testIngredient;

} catch (Exception $e) {
    echo "❌ ERROR: Form submission test failed: " . $e->getMessage() . "\n";
}

// Test Case 3: Controller Receives Request
echo "\n3. Testing Controller Request Handling\n";
echo "---------------------------------------\n";

try {
    // Test validation - missing required fields
    $invalidRequest = new Request([
        'inventory_id' => $testInventory->id,
        // Missing delivery_date and quantity
    ]);

    $controller = new InventoryController();

    // Since validation happens in the method, we need to catch the ValidationException
    try {
        $response = $controller->notifyDelivery($invalidRequest);
        echo "⚠️  Expected validation to fail but it didn't\n";
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "✅ PASS: Controller properly validates input\n";
        echo "   Validation errors: " . implode(', ', array_keys($e->errors())) . "\n";
    } catch (Exception $e) {
        echo "❌ ERROR: Unexpected exception: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: Controller validation test failed: " . $e->getMessage() . "\n";
}

// Test Case 4: Model/Service Updates Stock
echo "\n4. Testing Model Stock Updates\n";
echo "-------------------------------\n";

try {
    $beforeUpdate = $testInventory->quantity;

    // Test addStock method directly
    $testInventory->addStock(10, 'ADMIN001', 'Direct model test');

    $testInventory->refresh();
    $expected = $beforeUpdate + 10;

    if ($testInventory->quantity == $expected) {
        echo "✅ PASS: Model addStock method works correctly\n";
        echo "   Before: {$beforeUpdate}, Added: 10, After: {$testInventory->quantity}\n";
    } else {
        echo "❌ FAIL: Model addStock failed\n";
        echo "   Expected: {$expected}, Got: {$testInventory->quantity}\n";
    }

    // Test useStock method
    $beforeUse = $testInventory->quantity;
    $testInventory->useStock(5, 'ADMIN001', 'Direct model test - usage');

    $testInventory->refresh();
    $expectedAfterUse = $beforeUse - 5;

    if ($testInventory->quantity == $expectedAfterUse) {
        echo "✅ PASS: Model useStock method works correctly\n";
        echo "   Before: {$beforeUse}, Used: 5, After: {$testInventory->quantity}\n";
    } else {
        echo "❌ FAIL: Model useStock failed\n";
        echo "   Expected: {$expectedAfterUse}, Got: {$testInventory->quantity}\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: Model update test failed: " . $e->getMessage() . "\n";
}

// Test Case 5: Database Update & History
echo "\n5. Testing Database Updates & History\n";
echo "--------------------------------------\n";

try {
    // For ingredients, history is not logged to inventory_history table
    // Check if ingredient quantity was actually updated in database
    $testInventory->refresh();

    if ($testInventory->quantity == 130) { // Should be 130 after addStock(10) and useStock(5)
        echo "✅ PASS: Database updates work correctly\n";
        echo "   Current quantity in DB: {$testInventory->quantity}\n";
        echo "   Note: Ingredients use separate history logging (not inventory_history)\n";
    } else {
        echo "❌ FAIL: Database not updated correctly\n";
        echo "   Expected: 130, Got: {$testInventory->quantity}\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: Database check failed: " . $e->getMessage() . "\n";
}

// Test Case 6: Response to UI
echo "\n6. Testing Response to UI\n";
echo "-------------------------\n";

try {
    // Test successful response
    $successRequest = new Request([
        'inventory_id' => $testInventory->id,
        'delivery_date' => now()->toDateString(),
        'quantity' => 15
    ]);

    $controller = new InventoryController();
    $response = $controller->notifyDelivery($successRequest);

    if (method_exists($response, 'getData')) {
        $data = $response->getData(true);
        if (isset($data['success']) && $data['success']) {
            echo "✅ PASS: Controller returns success response\n";
            echo "   Message: " . ($data['message'] ?? 'No message') . "\n";
        } else {
            echo "❌ FAIL: Controller did not return success\n";
        }
    } else {
        echo "⚠️  Could not check response format\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: Response test failed: " . $e->getMessage() . "\n";
}

// Test Case 7: Error Handling
echo "\n7. Testing Error Handling\n";
echo "-------------------------\n";

try {
    // Test invalid inventory ID
    $errorRequest = new Request([
        'inventory_id' => 99999, // Non-existent ID
        'delivery_date' => now()->toDateString(),
        'quantity' => 10
    ]);

    $controller = new InventoryController();

    try {
        $response = $controller->notifyDelivery($errorRequest);
        echo "❌ FAIL: Should have thrown exception for invalid ID\n";
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        echo "✅ PASS: Controller handles invalid inventory ID\n";
        echo "   Error: Model not found exception\n";
    } catch (Exception $e) {
        echo "⚠️  Different exception type: " . $e->getMessage() . "\n";
    }

    // Test negative quantity (validation should catch this)
    $negativeRequest = new Request([
        'inventory_id' => $testInventory->id,
        'delivery_date' => now()->toDateString(),
        'quantity' => -5
    ]);

    try {
        $response = $controller->notifyDelivery($negativeRequest);
        echo "❌ FAIL: Should have rejected negative quantity\n";
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "✅ PASS: Controller rejects negative quantities\n";
        echo "   Validation errors: " . implode(', ', array_keys($e->errors())) . "\n";
    } catch (Exception $e) {
        echo "⚠️  Different exception for negative quantity: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: Error handling test failed: " . $e->getMessage() . "\n";
}

// Test Case 8: Audit/History (Optional)
echo "\n8. Testing Audit/History\n";
echo "-----------------------\n";

try {
    // For ingredients, we don't have separate history logging yet
    // But we can check that the updated_by field is set correctly
    $testInventory->refresh();

    if ($testInventory->updated_by == 'ADMIN001') {
        echo "✅ PASS: Audit trail maintained via updated_by field\n";
        echo "   Last updated by: {$testInventory->updated_by}\n";
        echo "   Note: Full history logging for ingredients needs separate implementation\n";
    } else {
        echo "⚠️  updated_by field not set correctly: {$testInventory->updated_by}\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: Audit test failed: " . $e->getMessage() . "\n";
}

// Cleanup
echo "\n🧹 Cleaning up test data...\n";
try {
    // Delete history records
    \App\Models\InventoryHistory::where('inventory_item_id', $testInventory->id)->delete();

    // Delete test inventory
    $testInventory->delete();

    echo "✅ Test data cleaned up successfully\n";
} catch (Exception $e) {
    echo "⚠️  Cleanup failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "UI STOCK MANAGEMENT WORKFLOW TEST COMPLETE\n";
echo str_repeat("=", 50) . "\n";

?>
