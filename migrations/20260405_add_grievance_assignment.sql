-- CampusNexus Phase 3 Migration: Add Grievance Assignment Feature
-- Adds support for assigning grievances to faculty members for resolution tracking

-- Step 1: Check if assigned_to column exists (for safety)
-- If the column doesn't exist, run the following ALTER TABLE:

ALTER TABLE grievances ADD COLUMN assigned_to INT NULL DEFAULT NULL AFTER status;
ALTER TABLE grievances ADD CONSTRAINT fk_grievances_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL;

-- After running this migration:
-- 1. Admins can now assign grievances to faculty members via admin/grievances.php
-- 2. Faculty members can view assigned grievances (with tracking of who it was assigned to)
-- 3. The assignment is tracked with the assigned_to foreign key
