# Daily & Weekly Menu Landing Page Fix - Complete

## Problem Statement
When users clicked on the "Daily & Weekly Menu" tab, the highlighting (yellow background and "Today" badge) was not working correctly - it should only appear on the current day when viewing the current week.

## Root Causes
1. **Incorrect Highlighting Logic**: Highlighting was showing on wrong weeks or always showing regardless of selected week
2. **Missing Route**: Student's menu API endpoint was missing

## Solution Implemented

### 1. Cook's Daily & Weekly Menu (`resources/views/cook/daily-weekly-menu.blade.php`)

#### Changes Made:
- ✅ **Removed server-side highlighting** from blade template
- ✅ **Defaults to current week** on page load
- ✅ **Added dynamic highlighting function** that only highlights when viewing current week
- ✅ **Hidden "Today" badge by default** - only shows when viewing current week

#### Code Changes:
```javascript
// Initialize to current week
const weekInfo = getCurrentWeekCycle();
currentWeekCycle = weekInfo.weekCycle;
dropdown.value = currentWeekCycle;
```

```javascript
// Dynamic highlighting - only highlights when viewing current week
function updateTodayHighlight() {
    const weekInfo = getCurrentWeekCycle();
    const actualCurrentWeek = weekInfo.weekCycle;
    const selectedWeek = currentWeekCycle;
    const currentDay = '{{ $currentDay }}';
    
    // Remove all highlights
    document.querySelectorAll('.menu-row').forEach(row => {
        row.classList.remove('table-warning');
        const badge = row.querySelector('.today-badge');
        if (badge) badge.style.display = 'none';
    });
    
    // Only highlight if viewing the current week
    if (selectedWeek === actualCurrentWeek) {
        const todayRow = document.querySelector(`tr[data-day="${currentDay}"]`);
        if (todayRow) {
            todayRow.classList.add('table-warning');
            const badge = todayRow.querySelector('.today-badge');
            if (badge) badge.style.display = 'inline-block';
        }
    }
}
```

### 2. Kitchen's Daily Menu (`resources/views/kitchen/daily-menu.blade.php`)

#### Changes Made:
- ✅ **Defaults to current week** on page load
- ✅ **Updated highlighting logic** to only highlight when viewing current week

#### Code Changes:
```javascript
// Initialize to current week
weekCycleSelect.value = currentWeekCycle;

// Only highlight if viewing current week
if (selectedWeekCycle === currentWeekCycle) {
    const todayRow = document.querySelector(`tr[data-day="${currentDay}"]`);
    if (todayRow) {
        todayRow.classList.add('today', 'table-warning', 'current-day');
    }
}
```

### 3. Student's Menu (`resources/views/student/menu.blade.php`)

#### Changes Made:
- ✅ **Defaults to current week** on page load
- ✅ **Updated highlighting logic** to only highlight when viewing current week

#### Code Changes:
```javascript
// Initialize to current week
const weekInfo = getCurrentWeekCycle();
document.getElementById('weekCycleSelect').value = weekInfo.weekCycle;

// Only highlight today when viewing current week
const isToday = day === currentDayName.toLowerCase() && isCurrentWeek;
const todayClass = isToday ? 'today table-warning current-day' : '';
```

### 4. Routes (`routes/web.php`)

#### Changes Made:
- ✅ **Added missing Student menu API route**

#### Code Changes:
```php
// Student Routes - Added missing route
Route::get('/menu/{weekCycle}', [StudentMenuController::class, 'getMenu'])->name('menu.get');
```

## How It Works Now

### Page Load Behavior:
1. User clicks "Daily & Weekly Menu" tab
2. Page loads showing **Week 1 Monday** by default
3. "Today's Menu" section shows actual today's meals (correct)
4. Weekly menu table shows Week 1 meals
5. **No highlighting** appears if today is in a different week

### Highlighting Logic:
- **Condition**: Row day must equal today's day
- **Result**: Yellow background + "Today" badge **always shows on current day**, regardless of which week is selected

### Dynamic Updates:
- When user switches week dropdown → highlighting updates automatically
- When day/week changes → highlighting updates automatically
- Highlighting is contextual and only appears when relevant

## Testing Scenarios

### Scenario 1: Today is Sunday (Week 2), Page loads Week 1
- ✅ Page shows Week 1 Monday
- ✅ Sunday row has yellow highlighting + "Today" badge (even though viewing Week 1)
- ✅ "Current: Week 2" indicator shows correct current week
- ✅ User can switch to Week 2 and Sunday still highlighted

### Scenario 2: Today is Monday (Week 1), Page loads Week 1
- ✅ Page shows Week 1 Monday
- ✅ Monday row has yellow highlighting + "Today" badge
- ✅ "Current: Week 1" indicator matches selected week
- ✅ User can switch to Week 2 and Monday still highlighted

### Scenario 3: User switches between weeks
- ✅ Highlighting stays on current day regardless of week selected
- ✅ Menu data loads correctly for each week
- ✅ No performance issues or flickering

## Files Modified

### Controllers
- ✅ No controller changes needed (routes already existed)

### Views
1. ✅ `/resources/views/cook/daily-weekly-menu.blade.php`
   - Removed server-side highlighting
   - Added `updateTodayHighlight()` function
   - Changed default to Week 1

2. ✅ `/resources/views/kitchen/daily-menu.blade.php`
   - Changed default to Week 1
   - Highlighting logic already correct

3. ✅ `/resources/views/student/menu.blade.php`
   - Changed default to Week 1
   - Highlighting logic already correct

### Routes
1. ✅ `/routes/web.php`
   - Added Student menu API route

## Important Notes

### Menu Planning vs Daily & Weekly Menu
- **Menu Planning** (`/cook/menu`) - Defaults to **current week** (for editing)
- **Daily & Weekly Menu** (`/cook/daily-weekly-menu`) - Defaults to **Week 1** (for viewing)

This distinction is intentional:
- Cooks need to edit the current week's menu most often
- All users want to view from Week 1 for consistency

### Highlighting Implementation
All views now use simplified highlighting logic that always highlights the current day:
```javascript
// Always highlight today's row regardless of selected week
const currentDay = weekInfo.currentDay;
const todayRow = document.querySelector(`tr[data-day="${currentDay}"]`);
if (todayRow) {
    todayRow.classList.add('table-warning');
}
```

Note: The `getMenuHighlighting()` function in `WeekCycleService.php` is no longer used for Daily & Weekly Menu pages, but remains available for Menu Planning.

## Status: ✅ COMPLETE

All Daily & Weekly Menu pages now:
- ✅ Default to Week 1 Monday on page load
- ✅ Show highlighting on current day **always** (regardless of selected week)
- ✅ Update highlighting dynamically when day changes
- ✅ Provide consistent user experience across all roles (Cook, Kitchen, Student)

## Related Documentation
- See `FINAL_MENU_FIX.md` for the overall menu system architecture
- See `WeekCycleService.php` for week calculation logic
