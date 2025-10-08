# Menu Synchronization Fix

## Problem Identified
The dashboard's "Today's Menu" was showing different data than the Menu Planning page because:

1. **Menu Planning** updates the `meals` table (weekly template)
2. **Dashboard** was reading from `daily_menu_updates` table (daily instance)
3. **No synchronization** between the two tables when menu planning is updated

## Solution Implemented

### Automatic Synchronization
When a Cook updates the menu planning (in the `meals` table), the system now automatically syncs the changes to `daily_menu_updates` table **if the meal applies to today**.

### How It Works

```
Menu Planning Update Flow:
1. Cook updates meal in Menu Planning
2. Meal saved to `meals` table
3. System checks: Is this meal for today?
4. If YES → Update `daily_menu_updates` table
5. If NO → Skip (will auto-populate when that day arrives)
6. Dashboard shows updated menu immediately
```

### Code Changes

#### File: `/app/Http/Controllers/Cook/MenuController.php`

**Added sync method:**
```php
private function syncMealToDailyUpdate($meal)
{
    // Get current week info
    $weekInfo = \App\Services\WeekCycleService::getWeekInfo();
    $currentDay = $weekInfo['current_day'];
    $currentWeekCycle = $weekInfo['week_cycle'];
    $today = now()->format('Y-m-d');

    // Only sync if this meal is for today
    if ($meal->day_of_week === $currentDay && $meal->week_cycle == $currentWeekCycle) {
        \App\Models\DailyMenuUpdate::updateOrCreate(
            [
                'menu_date' => $today,
                'meal_type' => $meal->meal_type
            ],
            [
                'meal_name' => $meal->name,
                'ingredients' => is_array($meal->ingredients) ? implode(', ', $meal->ingredients) : $meal->ingredients,
                'estimated_portions' => $meal->serving_size ?? 0,
                'updated_by' => auth()->user()->user_id ?? null
            ]
        );
    }
}
```

**Called in two places:**
1. After `update()` method (line 185)
2. After `store()` method (line 305)

## Data Flow Diagram

```
┌─────────────────────────┐
│   Menu Planning Page    │
│   (Cook updates meal)   │
└───────────┬─────────────┘
            │
            ▼
┌─────────────────────────┐
│    meals table          │
│  (Weekly Template)      │
│  - monday/breakfast     │
│  - tuesday/lunch        │
│  - etc...               │
└───────────┬─────────────┘
            │
            │ Auto-sync if today
            ▼
┌─────────────────────────┐
│ daily_menu_updates      │
│ (Daily Instance)        │
│ - 2025-10-05/breakfast  │
│ - 2025-10-05/lunch      │
│ - etc...                │
└───────────┬─────────────┘
            │
            ▼
┌─────────────────────────┐
│   All Dashboards        │
│   - Cook Dashboard      │
│   - Kitchen Dashboard   │
│   - Student Dashboard   │
└─────────────────────────┘
```

## Testing Steps

### 1. Update Menu Planning for Today
```bash
# Go to Menu Planning page
# Select current week cycle (Week 1 or Week 2)
# Update a meal for today (e.g., Breakfast)
# Save changes
```

### 2. Check Dashboard
```bash
# Go to Cook Dashboard
# Verify "Today's Menu" shows the updated meal
# Check Kitchen Dashboard - should match
# Check Student Dashboard - should match
```

### 3. Update Menu Planning for Future Day
```bash
# Go to Menu Planning page
# Update a meal for tomorrow or next week
# Save changes
# Dashboard should NOT change (only shows today)
```

### 4. Verify Auto-Population
```bash
# Clear daily_menu_updates for today:
DELETE FROM daily_menu_updates WHERE menu_date = CURDATE();

# Refresh any dashboard
# System should auto-populate from meals table
# Dashboard shows correct menu
```

## Key Benefits

1. **✅ Single Source of Truth**: `daily_menu_updates` is always the source for dashboards
2. **✅ Automatic Sync**: Menu planning changes reflect immediately on dashboards
3. **✅ Smart Sync**: Only syncs when meal applies to today (efficient)
4. **✅ Backward Compatible**: Auto-populates from `meals` if `daily_menu_updates` is empty
5. **✅ Consistent Display**: All users see the same menu data

## Edge Cases Handled

### Case 1: Menu Planning Updated for Today
- ✅ Syncs immediately to `daily_menu_updates`
- ✅ Dashboard shows updated menu on next load

### Case 2: Menu Planning Updated for Future Day
- ✅ No sync (not today)
- ✅ Will auto-populate when that day arrives

### Case 3: No Daily Menu Exists
- ✅ Auto-populates from `meals` table
- ✅ Creates entry in `daily_menu_updates`

### Case 4: Cook Edits Daily Menu Directly (via API)
- ✅ Updates `daily_menu_updates` only
- ✅ Does NOT affect `meals` template
- ✅ Change is temporary (only for that specific date)

## Database Tables

### meals (Weekly Template)
```
id | name | ingredients | meal_type | day_of_week | week_cycle
1  | Pancakes | Flour, Eggs | breakfast | monday | 1
```

### daily_menu_updates (Daily Instance)
```
id | menu_date | meal_type | meal_name | ingredients | updated_by
1  | 2025-10-05 | breakfast | Pancakes | Flour, Eggs | cook1
```

## Troubleshooting

### Dashboard shows old menu
**Solution**: Clear browser cache or hard refresh (Ctrl+F5)

### Menu planning update doesn't sync
**Check**:
1. Is the meal for today?
2. Is the week cycle correct?
3. Check logs: `storage/logs/laravel.log`

### Dashboard shows "No menu planned"
**Check**:
1. Does meal exist in `meals` table for current day/week?
2. Run: `SELECT * FROM meals WHERE day_of_week = 'monday' AND week_cycle = 1`
3. Check week cycle calculation: `/debug/week-cycle`

## Future Enhancements

1. **Real-Time Sync**: Use WebSocket/Pusher for instant updates without refresh
2. **Sync All Days**: Add button to sync entire week from planning to daily
3. **Conflict Resolution**: Handle cases where both tables have different data
4. **Audit Trail**: Track all sync operations for debugging

## Related Files

- `/app/Http/Controllers/Cook/MenuController.php` - Menu planning controller (sync added)
- `/app/Http/Controllers/Cook/CookDashboardController.php` - Dashboard controller
- `/app/Http/Controllers/DailyMenuController.php` - Daily menu API
- `/app/Models/Meal.php` - Weekly template model
- `/app/Models/DailyMenuUpdate.php` - Daily instance model
- `/database/migrations/2025_10_05_000000_create_daily_menu_updates_table.php` - Migration

## Conclusion

The menu synchronization issue has been resolved. The system now maintains consistency between Menu Planning and Dashboard displays by automatically syncing changes when they apply to the current day. This ensures all users (Cook, Kitchen, Student) see the same, up-to-date menu information.
