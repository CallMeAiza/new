# Week Mismatch Warning - Quick Fix

## Problem from Screenshots
- Menu Planning showed Week 2 meals (chicken, fried hotdog, ampalaya)
- Dashboard showed Week 1 meals (adobo, rtwod, tqq)
- Today is Week 1, but user was viewing Week 2 in planning

## Solution
Added visual warning when viewing different week than current week.

## Changes Made
1. Added warning banner in menu planning header
2. Shows when selected week ≠ current week
3. Tells user which week contains today's menu

## Result
User will now see:
⚠️ "You are viewing a different week. Today's menu is in Week 1"

This prevents confusion about which menu appears on dashboards.
