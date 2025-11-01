# Daily Menu System Implementation

## Overview
This document describes the centralized daily menu system that provides a single source of truth for menu data across Cook, Kitchen, and Student dashboards.

## Requirements Met

### 1. Shared Menu Display ✅
- **Single Source of Truth**: All menu data is stored in the `daily_menu_updates` table
- **Consistent Display**: Cook, Kitchen, and Student users all see the same menu data
- **No Modifications**: Menu is pulled directly from the centralized database table

### 2. Dashboard Daily Menu Display ✅
- **Automatic Date-Based Display**: All dashboards automatically display the menu for the current day
- **Real-Time Updates**: When Cook edits the menu, changes are immediately reflected across all dashboards
- **Historical Data**: Past menus remain fixed and cannot be edited
- **Future Edits**: Only today and future dates can be edited by the Cook

## Database Structure

### Table: `daily_menu_updates`
```sql
- id (primary key)
- menu_date (date) - The date this menu is for
- meal_type (string) - breakfast, lunch, or dinner
- meal_name (string) - Name of the meal
- ingredients (text) - Ingredients list
- status (string) - planned, preparing, ready, served
- estimated_portions (integer) - Expected number of servings
- actual_portions (integer) - Actual servings made
- updated_by (foreign key) - User who last updated this entry
- created_at, updated_at (timestamps)
- UNIQUE constraint on (menu_date, meal_type)
```

## API Endpoints

All endpoints are protected by authentication middleware and located under `/api/daily-menu/`:

### 1. Get Today's Menu
```
GET /api/daily-menu/today?date=YYYY-MM-DD
```
- **Access**: All authenticated users
- **Returns**: Menu for specified date (defaults to today)
- **Auto-populates**: If no menu exists, automatically creates from Meal planning

### 2. Get Menu Range
```
GET /api/daily-menu/range?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
```
- **Access**: All authenticated users
- **Returns**: Menus for date range
- **Auto-populates**: Missing dates from Meal planning

### 3. Update Menu
```
POST /api/daily-menu/update
Body: {
    menu_date: "YYYY-MM-DD",
    meal_type: "breakfast|lunch|dinner",
    meal_name: "Meal Name",
    ingredients: "Ingredient list",
    estimated_portions: 50
}
```
- **Access**: Cook only (validated in controller)
- **Restriction**: Cannot edit past dates
- **Effect**: Updates centralized menu, visible to all users immediately

### 4. Delete Menu
```
DELETE /api/daily-menu/delete
Body: {
    menu_date: "YYYY-MM-DD",
    meal_type: "breakfast|lunch|dinner"
}
```
- **Access**: Cook only
- **Restriction**: Cannot delete past dates

### 5. Sync from Planning
```
POST /api/daily-menu/sync
Body: {
    start_date: "YYYY-MM-DD",
    end_date: "YYYY-MM-DD"
}
```
- **Access**: Cook or Admin only
- **Purpose**: Bulk sync menus from Meal planning to daily updates

## Controller Updates

### CookDashboardController
- **Changed**: Now reads from `DailyMenuUpdate` instead of `Meal`
- **Auto-populate**: Creates daily menu entries from Meal planning if missing
- **Edit Capability**: Cook can edit today and future menus

### KitchenDashboardController
- **Changed**: Now reads from `DailyMenuUpdate` instead of `Meal`
- **Auto-populate**: Creates daily menu entries from Meal planning if missing
- **Read-Only**: Kitchen staff can view but not edit

### StudentDashboardController
- **Changed**: Now reads from `DailyMenuUpdate` instead of `Meal`
- **Auto-populate**: Creates daily menu entries from Meal planning if missing
- **Read-Only**: Students can view but not edit

## Data Flow

```
┌─────────────────┐
│  Meal Planning  │ (Weekly template by Cook)
│   (meals table) │
└────────┬────────┘
         │ Auto-populate on first access
         ▼
┌─────────────────────────┐
│  Daily Menu Updates     │ ◄── Cook can edit (today & future)
│ (daily_menu_updates)    │
└────────┬────────────────┘
         │ Read by all users
         ├──────────┬──────────┐
         ▼          ▼          ▼
    ┌────────┐ ┌─────────┐ ┌─────────┐
    │  Cook  │ │ Kitchen │ │ Student │
    │Dashboard│ │Dashboard│ │Dashboard│
    └────────┘ └─────────┘ └─────────┘
```

## Edit Restrictions

### Date-Based Editing Rules
```php
private function canEditMenu($date)
{
    $menuDate = Carbon::parse($date)->startOfDay();
    $today = Carbon::today();
    
    return $menuDate->gte($today); // Can only edit today or future
}
```

- **Past Dates**: Read-only, cannot be modified
- **Today**: Can be edited by Cook
- **Future Dates**: Can be edited by Cook
- **Role Check**: Only users with `user_role = 'cook'` can edit

## Auto-Population Logic

When a dashboard is accessed and no menu exists for the requested date:

1. **Check `daily_menu_updates` table** for the date
2. **If empty**, query `meals` table using:
   - Week cycle (calculated from date)
   - Day of week
3. **Create entries** in `daily_menu_updates` for each meal found
4. **Return** the newly created menu

This ensures:
- Menus are always available
- Cook's planning is automatically applied
- One-time conversion from template to daily menu

## Real-Time Sync

### How It Works
1. Cook updates menu via API endpoint
2. Data is saved to `daily_menu_updates` table
3. All dashboards read from the same table
4. Next page load or refresh shows updated menu

### Future Enhancement (Optional)
For true real-time updates without page refresh:
- Implement WebSocket or Server-Sent Events (SSE)
- Use Laravel Broadcasting with Pusher or Redis
- Update frontend to listen for menu change events

## Usage Examples

### For Cook: Edit Today's Breakfast Menu
```javascript
fetch('/api/daily-menu/update', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        menu_date: '2025-10-05',
        meal_type: 'breakfast',
        meal_name: 'Pancakes with Maple Syrup',
        ingredients: 'Flour, Eggs, Milk, Maple Syrup, Butter',
        estimated_portions: 50
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Menu updated successfully
        // Refresh menu display
    }
});
```

### For All Users: Get Today's Menu
```javascript
fetch('/api/daily-menu/today')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display menu
            data.menu.forEach(item => {
                console.log(`${item.meal_type}: ${item.meal_name}`);
            });
        }
    });
```

## Testing Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Test Cook can edit today's menu
- [ ] Test Cook cannot edit past menu
- [ ] Test Kitchen sees same menu as Cook
- [ ] Test Student sees same menu as Cook
- [ ] Test menu auto-populates from Meal planning
- [ ] Test menu updates reflect immediately on refresh
- [ ] Test unique constraint (no duplicate meal_type for same date)

## Files Modified

### New Files
1. `/database/migrations/2025_10_05_000000_create_daily_menu_updates_table.php`
2. `/app/Http/Controllers/DailyMenuController.php`
3. `/app/Services/WeekCycleService.php` - Added `getWeekInfoForDate()` method

### Modified Files
1. `/routes/web.php` - Added API routes for daily menu
2. `/app/Http/Controllers/Cook/CookDashboardController.php` - Uses DailyMenuUpdate
3. `/app/Http/Controllers/Kitchen/KitchenDashboardController.php` - Uses DailyMenuUpdate
4. `/app/Http/Controllers/Student/StudentDashboardController.php` - Uses DailyMenuUpdate

### Existing Files (No Changes Required)
- `/app/Models/DailyMenuUpdate.php` - Already exists with correct structure
- `/app/Models/Meal.php` - Continues to serve as weekly template
- Dashboard blade views - Continue to work with updated data structure

## Benefits

1. **Single Source of Truth**: No data mismatches between dashboards
2. **Date-Based Control**: Past menus are immutable, future menus are editable
3. **Auto-Population**: Seamless integration with existing Meal planning
4. **Role-Based Access**: Cook can edit, others can only view
5. **Audit Trail**: `updated_by` tracks who made changes
6. **Performance**: Direct queries to daily table instead of complex joins
7. **Flexibility**: Cook can override planned menu for specific dates

## Maintenance Notes

### Regular Tasks
- Monitor `daily_menu_updates` table size (grows daily)
- Consider archiving old entries (older than 30 days) if needed
- Ensure Meal planning is kept up-to-date for auto-population

### Troubleshooting
- **Menu not showing**: Check if Meal planning exists for the week cycle
- **Cannot edit**: Verify user role is 'cook' and date is not in past
- **Duplicate entries**: Check unique constraint on (menu_date, meal_type)

## Future Enhancements

1. **Real-Time Updates**: Implement WebSocket for instant updates without refresh
2. **Menu History**: Track all changes with timestamps and user info
3. **Bulk Edit**: Allow Cook to edit multiple days at once
4. **Menu Templates**: Save frequently used menus as templates
5. **Nutritional Info**: Add nutritional information to menu items
6. **Allergen Warnings**: Flag common allergens in ingredients
7. **Image Upload**: Allow Cook to upload meal images
8. **Student Preferences**: Track which meals are most popular
