f# Stock Management Implementation Plan

## Phase 1: Model Methods ✅
- [x] Add `addStock()` and `useStock()` methods to `Inventory` model
- [x] Add `addStock()` and `useStock()` methods to `Ingredient` model
- [x] Add validation and logging to stock operations

## Phase 2: Delivery Logic Enhancement ✅
- [x] Update `PurchaseOrder::markAsDelivered()` to use new model methods
- [x] Update `Cook\InventoryController::notifyDelivery()` to use new model methods
- [x] Ensure consistent stock updates across both models

## Phase 3: Cooking Stock Deduction ✅
- [x] Identify where cooking/meal preparation happens
- [x] Create stock deduction logic for meal ingredients
- [x] Add methods to deduct stock when meals are prepared

## Phase 4: Controller Updates ✅
- [x] Update relevant controllers to use new stock management methods
- [x] Add proper error handling and validation

## Phase 5: Testing and Validation ✅
- [x] Test delivery stock additions
- [x] Test cooking stock deductions
- [x] Verify inventory consistency across models
