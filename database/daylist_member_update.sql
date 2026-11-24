-- Daylist incremental member update (2026-11-24).

-- Select your existing Daylist database in phpMyAdmin, then import this file.

-- No tables are dropped. No passwords or payment records are replaced.

-- Existing identifiers, membership status and transaction history are preserved.

-- Conflicting/changed records are skipped; check the verification results at the end.

SET NAMES utf8mb4;

START TRANSACTION;

SET @daylist_admin_id = (SELECT id FROM users WHERE is_admin = 1 ORDER BY id LIMIT 1);

SET @daylist_profiles_updated = 0;

SET @daylist_members_added = 0;

SET @daylist_transactions_added = 0;


-- Maya Reyes

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261028-01M6135FJNP5664HJ1W38BP0XB' OR email = 'maya.demo@example.test' ORDER BY (membership_number = 'MEM-20261028-01M6135FJNP5664HJ1W38BP0XB') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('maya.demo@example.test', 'maya.reyes@gmail.com') AND m.first_name IN ('Maya', 'Maya (Demo)') AND m.last_name = 'Reyes'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'maya.reyes@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'maya.reyes@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Maya Reyes', u.email = 'maya.reyes@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Maya', last_name = 'Reyes', email = 'maya.reyes@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();


-- Daniel Cruz

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261028-01M6135FKKXA43HWW8R9JZ7MG6' OR email = 'daniel.demo@example.test' ORDER BY (membership_number = 'MEM-20261028-01M6135FKKXA43HWW8R9JZ7MG6') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('daniel.demo@example.test', 'daniel.cruz@gmail.com') AND m.first_name IN ('Daniel', 'Daniel (Demo)') AND m.last_name = 'Cruz'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'daniel.cruz@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'daniel.cruz@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Daniel Cruz', u.email = 'daniel.cruz@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Daniel', last_name = 'Cruz', email = 'daniel.cruz@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();


-- Elena Santos

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261028-01M6135FKT3BSXGYNF3DK8SBK9' OR email = 'elena.demo@example.test' ORDER BY (membership_number = 'MEM-20261028-01M6135FKT3BSXGYNF3DK8SBK9') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('elena.demo@example.test', 'elena.santos@gmail.com') AND m.first_name IN ('Elena', 'Elena (Demo)') AND m.last_name = 'Santos'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'elena.santos@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'elena.santos@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Elena Santos', u.email = 'elena.santos@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Elena', last_name = 'Santos', email = 'elena.santos@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();


-- Alex Rivera

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261028-01M6135FM4SR0ECX0936SX84QB' OR email = 'alex.demo@example.test' ORDER BY (membership_number = 'MEM-20261028-01M6135FM4SR0ECX0936SX84QB') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('alex.demo@example.test', 'alex.rivera@gmail.com') AND m.first_name IN ('Alex', 'Alex (Demo)') AND m.last_name = 'Rivera'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'alex.rivera@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'alex.rivera@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Alex Rivera', u.email = 'alex.rivera@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Alex', last_name = 'Rivera', email = 'alex.rivera@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();


-- Nina Garcia

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261028-01M6135FMHK4FXK7GHFH59SG3Z' OR email = 'nina.demo@example.test' ORDER BY (membership_number = 'MEM-20261028-01M6135FMHK4FXK7GHFH59SG3Z') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('nina.demo@example.test', 'nina.garcia@gmail.com') AND m.first_name IN ('Nina', 'Nina (Demo)') AND m.last_name = 'Garcia'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'nina.garcia@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'nina.garcia@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Nina Garcia', u.email = 'nina.garcia@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Nina', last_name = 'Garcia', email = 'nina.garcia@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();


-- Ava Santos

INSERT INTO members (membership_number, first_name, last_name, email, phone, address, membership_category_id, membership_level_id, is_active, created_at, updated_at)
SELECT 'MEM-20261124-01M85B1AMG00GAE0MPKDP9C0FS', 'Ava', 'Santos', 'ava.santos@gmail.com', NULL, 'Fictional sample member for the classroom pagination demonstration.', c.id, l.id, 1, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM membership_categories c CROSS JOIN membership_levels l
WHERE c.name = 'Student' AND l.name = 'Basic' AND @daylist_admin_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM members existing WHERE existing.membership_number = 'MEM-20261124-01M85B1AMG00GAE0MPKDP9C0FS' OR existing.email IN ('pagination.ava.demo@example.test', 'ava.santos@gmail.com'))
AND NOT EXISTS (SELECT 1 FROM users existing_user WHERE existing_user.email = 'ava.santos@gmail.com');

SET @daylist_members_added = @daylist_members_added + ROW_COUNT();

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261124-01M85B1AMG00GAE0MPKDP9C0FS' OR email = 'pagination.ava.demo@example.test' ORDER BY (membership_number = 'MEM-20261124-01M85B1AMG00GAE0MPKDP9C0FS') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('pagination.ava.demo@example.test', 'ava.santos@gmail.com') AND m.first_name IN ('Ava', 'Ava (Demo)') AND m.last_name = 'Santos'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'ava.santos@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'ava.santos@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Ava Santos', u.email = 'ava.santos@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Ava', last_name = 'Santos', email = 'ava.santos@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();

INSERT INTO membership_transactions (reference_number, member_id, membership_level_id, type, amount, validity_months, status, created_by, created_at, updated_at)
SELECT 'TXN-20261124-01M85B1AMWX2HVQZZSPAK4WXDP', m.id, l.id, 'application', '500.00', 12, 'pending', @daylist_admin_id, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM members m CROSS JOIN membership_levels l
WHERE m.id = @daylist_member_id AND m.membership_number = 'MEM-20261124-01M85B1AMG00GAE0MPKDP9C0FS' AND @daylist_can_update = 1 AND @daylist_admin_id IS NOT NULL
AND l.name = 'Basic'
AND NOT EXISTS (SELECT 1 FROM membership_transactions existing_transaction WHERE existing_transaction.member_id = m.id OR existing_transaction.reference_number = 'TXN-20261124-01M85B1AMWX2HVQZZSPAK4WXDP');

SET @daylist_transactions_added = @daylist_transactions_added + ROW_COUNT();


-- Ben Reyes

INSERT INTO members (membership_number, first_name, last_name, email, phone, address, membership_category_id, membership_level_id, is_active, created_at, updated_at)
SELECT 'MEM-20261124-01M85B1AN1R010DPSAKRH40YAS', 'Ben', 'Reyes', 'ben.reyes@gmail.com', NULL, 'Fictional sample member for the classroom pagination demonstration.', c.id, l.id, 1, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM membership_categories c CROSS JOIN membership_levels l
WHERE c.name = 'Regular' AND l.name = 'Silver' AND @daylist_admin_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM members existing WHERE existing.membership_number = 'MEM-20261124-01M85B1AN1R010DPSAKRH40YAS' OR existing.email IN ('pagination.ben.demo@example.test', 'ben.reyes@gmail.com'))
AND NOT EXISTS (SELECT 1 FROM users existing_user WHERE existing_user.email = 'ben.reyes@gmail.com');

SET @daylist_members_added = @daylist_members_added + ROW_COUNT();

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261124-01M85B1AN1R010DPSAKRH40YAS' OR email = 'pagination.ben.demo@example.test' ORDER BY (membership_number = 'MEM-20261124-01M85B1AN1R010DPSAKRH40YAS') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('pagination.ben.demo@example.test', 'ben.reyes@gmail.com') AND m.first_name IN ('Ben', 'Ben (Demo)') AND m.last_name = 'Reyes'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'ben.reyes@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'ben.reyes@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Ben Reyes', u.email = 'ben.reyes@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Ben', last_name = 'Reyes', email = 'ben.reyes@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();

INSERT INTO membership_transactions (reference_number, member_id, membership_level_id, type, amount, validity_months, status, created_by, created_at, updated_at)
SELECT 'TXN-20261124-01M85B1AN8PK9BVY9BKDJTMJ81', m.id, l.id, 'application', '1000.00', 12, 'pending', @daylist_admin_id, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM members m CROSS JOIN membership_levels l
WHERE m.id = @daylist_member_id AND m.membership_number = 'MEM-20261124-01M85B1AN1R010DPSAKRH40YAS' AND @daylist_can_update = 1 AND @daylist_admin_id IS NOT NULL
AND l.name = 'Silver'
AND NOT EXISTS (SELECT 1 FROM membership_transactions existing_transaction WHERE existing_transaction.member_id = m.id OR existing_transaction.reference_number = 'TXN-20261124-01M85B1AN8PK9BVY9BKDJTMJ81');

SET @daylist_transactions_added = @daylist_transactions_added + ROW_COUNT();


-- Chloe Ramos

INSERT INTO members (membership_number, first_name, last_name, email, phone, address, membership_category_id, membership_level_id, is_active, created_at, updated_at)
SELECT 'MEM-20261124-01M85B1ANCY87ZX6HQV7HDCK1V', 'Chloe', 'Ramos', 'chloe.ramos@gmail.com', NULL, 'Fictional sample member for the classroom pagination demonstration.', c.id, l.id, 1, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM membership_categories c CROSS JOIN membership_levels l
WHERE c.name = 'Student' AND l.name = 'Gold' AND @daylist_admin_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM members existing WHERE existing.membership_number = 'MEM-20261124-01M85B1ANCY87ZX6HQV7HDCK1V' OR existing.email IN ('pagination.chloe.demo@example.test', 'chloe.ramos@gmail.com'))
AND NOT EXISTS (SELECT 1 FROM users existing_user WHERE existing_user.email = 'chloe.ramos@gmail.com');

SET @daylist_members_added = @daylist_members_added + ROW_COUNT();

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261124-01M85B1ANCY87ZX6HQV7HDCK1V' OR email = 'pagination.chloe.demo@example.test' ORDER BY (membership_number = 'MEM-20261124-01M85B1ANCY87ZX6HQV7HDCK1V') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('pagination.chloe.demo@example.test', 'chloe.ramos@gmail.com') AND m.first_name IN ('Chloe', 'Chloe (Demo)') AND m.last_name = 'Ramos'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'chloe.ramos@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'chloe.ramos@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Chloe Ramos', u.email = 'chloe.ramos@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Chloe', last_name = 'Ramos', email = 'chloe.ramos@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();

INSERT INTO membership_transactions (reference_number, member_id, membership_level_id, type, amount, validity_months, status, created_by, created_at, updated_at)
SELECT 'TXN-20261124-01M85B1ANF9PB44PG60WC07D80', m.id, l.id, 'application', '2000.00', 12, 'pending', @daylist_admin_id, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM members m CROSS JOIN membership_levels l
WHERE m.id = @daylist_member_id AND m.membership_number = 'MEM-20261124-01M85B1ANCY87ZX6HQV7HDCK1V' AND @daylist_can_update = 1 AND @daylist_admin_id IS NOT NULL
AND l.name = 'Gold'
AND NOT EXISTS (SELECT 1 FROM membership_transactions existing_transaction WHERE existing_transaction.member_id = m.id OR existing_transaction.reference_number = 'TXN-20261124-01M85B1ANF9PB44PG60WC07D80');

SET @daylist_transactions_added = @daylist_transactions_added + ROW_COUNT();


-- Ethan Cruz

INSERT INTO members (membership_number, first_name, last_name, email, phone, address, membership_category_id, membership_level_id, is_active, created_at, updated_at)
SELECT 'MEM-20261124-01M85B1ANGB2XBHHF09M3D018D', 'Ethan', 'Cruz', 'ethan.cruz@gmail.com', NULL, 'Fictional sample member for the classroom pagination demonstration.', c.id, l.id, 1, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM membership_categories c CROSS JOIN membership_levels l
WHERE c.name = 'Corporate' AND l.name = 'Platinum' AND @daylist_admin_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM members existing WHERE existing.membership_number = 'MEM-20261124-01M85B1ANGB2XBHHF09M3D018D' OR existing.email IN ('pagination.ethan.demo@example.test', 'ethan.cruz@gmail.com'))
AND NOT EXISTS (SELECT 1 FROM users existing_user WHERE existing_user.email = 'ethan.cruz@gmail.com');

SET @daylist_members_added = @daylist_members_added + ROW_COUNT();

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261124-01M85B1ANGB2XBHHF09M3D018D' OR email = 'pagination.ethan.demo@example.test' ORDER BY (membership_number = 'MEM-20261124-01M85B1ANGB2XBHHF09M3D018D') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('pagination.ethan.demo@example.test', 'ethan.cruz@gmail.com') AND m.first_name IN ('Ethan', 'Ethan (Demo)') AND m.last_name = 'Cruz'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'ethan.cruz@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'ethan.cruz@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Ethan Cruz', u.email = 'ethan.cruz@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Ethan', last_name = 'Cruz', email = 'ethan.cruz@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();

INSERT INTO membership_transactions (reference_number, member_id, membership_level_id, type, amount, validity_months, status, created_by, created_at, updated_at)
SELECT 'TXN-20261124-01M85B1ANMSS7R67FT05BXSGRZ', m.id, l.id, 'application', '3500.00', 12, 'pending', @daylist_admin_id, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM members m CROSS JOIN membership_levels l
WHERE m.id = @daylist_member_id AND m.membership_number = 'MEM-20261124-01M85B1ANGB2XBHHF09M3D018D' AND @daylist_can_update = 1 AND @daylist_admin_id IS NOT NULL
AND l.name = 'Platinum'
AND NOT EXISTS (SELECT 1 FROM membership_transactions existing_transaction WHERE existing_transaction.member_id = m.id OR existing_transaction.reference_number = 'TXN-20261124-01M85B1ANMSS7R67FT05BXSGRZ');

SET @daylist_transactions_added = @daylist_transactions_added + ROW_COUNT();


-- Isla Garcia

INSERT INTO members (membership_number, first_name, last_name, email, phone, address, membership_category_id, membership_level_id, is_active, created_at, updated_at)
SELECT 'MEM-20261124-01M85B1ANN2SQBPY4K5C4P4NV8', 'Isla', 'Garcia', 'isla.garcia@gmail.com', NULL, 'Fictional sample member for the classroom pagination demonstration.', c.id, l.id, 1, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM membership_categories c CROSS JOIN membership_levels l
WHERE c.name = 'Senior Citizen' AND l.name = 'Silver' AND @daylist_admin_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM members existing WHERE existing.membership_number = 'MEM-20261124-01M85B1ANN2SQBPY4K5C4P4NV8' OR existing.email IN ('pagination.isla.demo@example.test', 'isla.garcia@gmail.com'))
AND NOT EXISTS (SELECT 1 FROM users existing_user WHERE existing_user.email = 'isla.garcia@gmail.com');

SET @daylist_members_added = @daylist_members_added + ROW_COUNT();

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261124-01M85B1ANN2SQBPY4K5C4P4NV8' OR email = 'pagination.isla.demo@example.test' ORDER BY (membership_number = 'MEM-20261124-01M85B1ANN2SQBPY4K5C4P4NV8') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('pagination.isla.demo@example.test', 'isla.garcia@gmail.com') AND m.first_name IN ('Isla', 'Isla (Demo)') AND m.last_name = 'Garcia'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'isla.garcia@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'isla.garcia@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Isla Garcia', u.email = 'isla.garcia@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Isla', last_name = 'Garcia', email = 'isla.garcia@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();

INSERT INTO membership_transactions (reference_number, member_id, membership_level_id, type, amount, validity_months, status, created_by, created_at, updated_at)
SELECT 'TXN-20261124-01M85B1ANQH6KKVC2F7H5WBZT2', m.id, l.id, 'application', '1000.00', 12, 'pending', @daylist_admin_id, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM members m CROSS JOIN membership_levels l
WHERE m.id = @daylist_member_id AND m.membership_number = 'MEM-20261124-01M85B1ANN2SQBPY4K5C4P4NV8' AND @daylist_can_update = 1 AND @daylist_admin_id IS NOT NULL
AND l.name = 'Silver'
AND NOT EXISTS (SELECT 1 FROM membership_transactions existing_transaction WHERE existing_transaction.member_id = m.id OR existing_transaction.reference_number = 'TXN-20261124-01M85B1ANQH6KKVC2F7H5WBZT2');

SET @daylist_transactions_added = @daylist_transactions_added + ROW_COUNT();


-- Liam Mendoza

INSERT INTO members (membership_number, first_name, last_name, email, phone, address, membership_category_id, membership_level_id, is_active, created_at, updated_at)
SELECT 'MEM-20261124-01M85B1ANR4ZPAAP3G5K2N1JRN', 'Liam', 'Mendoza', 'liam.mendoza@gmail.com', NULL, 'Fictional sample member for the classroom pagination demonstration.', c.id, l.id, 1, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM membership_categories c CROSS JOIN membership_levels l
WHERE c.name = 'Regular' AND l.name = 'Basic' AND @daylist_admin_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM members existing WHERE existing.membership_number = 'MEM-20261124-01M85B1ANR4ZPAAP3G5K2N1JRN' OR existing.email IN ('pagination.liam.demo@example.test', 'liam.mendoza@gmail.com'))
AND NOT EXISTS (SELECT 1 FROM users existing_user WHERE existing_user.email = 'liam.mendoza@gmail.com');

SET @daylist_members_added = @daylist_members_added + ROW_COUNT();

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261124-01M85B1ANR4ZPAAP3G5K2N1JRN' OR email = 'pagination.liam.demo@example.test' ORDER BY (membership_number = 'MEM-20261124-01M85B1ANR4ZPAAP3G5K2N1JRN') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('pagination.liam.demo@example.test', 'liam.mendoza@gmail.com') AND m.first_name IN ('Liam', 'Liam (Demo)') AND m.last_name = 'Mendoza'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'liam.mendoza@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'liam.mendoza@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Liam Mendoza', u.email = 'liam.mendoza@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Liam', last_name = 'Mendoza', email = 'liam.mendoza@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();

INSERT INTO membership_transactions (reference_number, member_id, membership_level_id, type, amount, validity_months, status, created_by, created_at, updated_at)
SELECT 'TXN-20261124-01M85B1ANWND3PX3P7Y01RDV97', m.id, l.id, 'application', '500.00', 12, 'pending', @daylist_admin_id, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM members m CROSS JOIN membership_levels l
WHERE m.id = @daylist_member_id AND m.membership_number = 'MEM-20261124-01M85B1ANR4ZPAAP3G5K2N1JRN' AND @daylist_can_update = 1 AND @daylist_admin_id IS NOT NULL
AND l.name = 'Basic'
AND NOT EXISTS (SELECT 1 FROM membership_transactions existing_transaction WHERE existing_transaction.member_id = m.id OR existing_transaction.reference_number = 'TXN-20261124-01M85B1ANWND3PX3P7Y01RDV97');

SET @daylist_transactions_added = @daylist_transactions_added + ROW_COUNT();


-- Nora Flores

INSERT INTO members (membership_number, first_name, last_name, email, phone, address, membership_category_id, membership_level_id, is_active, created_at, updated_at)
SELECT 'MEM-20261124-01M85B1ANXKFTDDWB5C6QKSYW8', 'Nora', 'Flores', 'nora.flores@gmail.com', NULL, 'Fictional sample member for the classroom pagination demonstration.', c.id, l.id, 1, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM membership_categories c CROSS JOIN membership_levels l
WHERE c.name = 'Senior Citizen' AND l.name = 'Gold' AND @daylist_admin_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM members existing WHERE existing.membership_number = 'MEM-20261124-01M85B1ANXKFTDDWB5C6QKSYW8' OR existing.email IN ('pagination.nora.demo@example.test', 'nora.flores@gmail.com'))
AND NOT EXISTS (SELECT 1 FROM users existing_user WHERE existing_user.email = 'nora.flores@gmail.com');

SET @daylist_members_added = @daylist_members_added + ROW_COUNT();

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261124-01M85B1ANXKFTDDWB5C6QKSYW8' OR email = 'pagination.nora.demo@example.test' ORDER BY (membership_number = 'MEM-20261124-01M85B1ANXKFTDDWB5C6QKSYW8') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('pagination.nora.demo@example.test', 'nora.flores@gmail.com') AND m.first_name IN ('Nora', 'Nora (Demo)') AND m.last_name = 'Flores'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'nora.flores@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'nora.flores@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Nora Flores', u.email = 'nora.flores@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Nora', last_name = 'Flores', email = 'nora.flores@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();

INSERT INTO membership_transactions (reference_number, member_id, membership_level_id, type, amount, validity_months, status, created_by, created_at, updated_at)
SELECT 'TXN-20261124-01M85B1AP1WCH7NE3P5GXWXC5Y', m.id, l.id, 'application', '2000.00', 12, 'pending', @daylist_admin_id, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM members m CROSS JOIN membership_levels l
WHERE m.id = @daylist_member_id AND m.membership_number = 'MEM-20261124-01M85B1ANXKFTDDWB5C6QKSYW8' AND @daylist_can_update = 1 AND @daylist_admin_id IS NOT NULL
AND l.name = 'Gold'
AND NOT EXISTS (SELECT 1 FROM membership_transactions existing_transaction WHERE existing_transaction.member_id = m.id OR existing_transaction.reference_number = 'TXN-20261124-01M85B1AP1WCH7NE3P5GXWXC5Y');

SET @daylist_transactions_added = @daylist_transactions_added + ROW_COUNT();


-- Theo Navarro

INSERT INTO members (membership_number, first_name, last_name, email, phone, address, membership_category_id, membership_level_id, is_active, created_at, updated_at)
SELECT 'MEM-20261124-01M85B1AP2PAGNGB52NFRS0Z0T', 'Theo', 'Navarro', 'theo.navarro@gmail.com', NULL, 'Fictional sample member for the classroom pagination demonstration.', c.id, l.id, 1, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM membership_categories c CROSS JOIN membership_levels l
WHERE c.name = 'Corporate' AND l.name = 'Platinum' AND @daylist_admin_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM members existing WHERE existing.membership_number = 'MEM-20261124-01M85B1AP2PAGNGB52NFRS0Z0T' OR existing.email IN ('pagination.theo.demo@example.test', 'theo.navarro@gmail.com'))
AND NOT EXISTS (SELECT 1 FROM users existing_user WHERE existing_user.email = 'theo.navarro@gmail.com');

SET @daylist_members_added = @daylist_members_added + ROW_COUNT();

SET @daylist_member_id = (SELECT id FROM members WHERE membership_number = 'MEM-20261124-01M85B1AP2PAGNGB52NFRS0Z0T' OR email = 'pagination.theo.demo@example.test' ORDER BY (membership_number = 'MEM-20261124-01M85B1AP2PAGNGB52NFRS0Z0T') DESC LIMIT 1);

SET @daylist_can_update = (SELECT COUNT(*) FROM members m LEFT JOIN users u ON u.id = m.user_id
WHERE m.id = @daylist_member_id AND m.email IN ('pagination.theo.demo@example.test', 'theo.navarro@gmail.com') AND m.first_name IN ('Theo', 'Theo (Demo)') AND m.last_name = 'Navarro'
AND (u.id IS NULL OR u.is_admin = 0)
AND NOT EXISTS (SELECT 1 FROM members other_member WHERE other_member.email = 'theo.navarro@gmail.com' AND other_member.id <> m.id)
AND NOT EXISTS (SELECT 1 FROM users other_user WHERE other_user.email = 'theo.navarro@gmail.com' AND other_user.id <> COALESCE(m.user_id, 0)));

UPDATE users u INNER JOIN members m ON m.user_id = u.id
SET u.name = 'Theo Navarro', u.email = 'theo.navarro@gmail.com', u.updated_at = CURRENT_TIMESTAMP
WHERE m.id = @daylist_member_id AND @daylist_can_update = 1 AND u.is_admin = 0;

UPDATE members SET first_name = 'Theo', last_name = 'Navarro', email = 'theo.navarro@gmail.com', updated_at = CURRENT_TIMESTAMP
WHERE id = @daylist_member_id AND @daylist_can_update = 1;

SET @daylist_profiles_updated = @daylist_profiles_updated + ROW_COUNT();

INSERT INTO membership_transactions (reference_number, member_id, membership_level_id, type, amount, validity_months, status, created_by, created_at, updated_at)
SELECT 'TXN-20261124-01M85B1AP6J9HJYMMNAH5014AA', m.id, l.id, 'application', '3500.00', 12, 'pending', @daylist_admin_id, '2026-11-24 07:14:39', CURRENT_TIMESTAMP
FROM members m CROSS JOIN membership_levels l
WHERE m.id = @daylist_member_id AND m.membership_number = 'MEM-20261124-01M85B1AP2PAGNGB52NFRS0Z0T' AND @daylist_can_update = 1 AND @daylist_admin_id IS NOT NULL
AND l.name = 'Platinum'
AND NOT EXISTS (SELECT 1 FROM membership_transactions existing_transaction WHERE existing_transaction.member_id = m.id OR existing_transaction.reference_number = 'TXN-20261124-01M85B1AP6J9HJYMMNAH5014AA');

SET @daylist_transactions_added = @daylist_transactions_added + ROW_COUNT();

COMMIT;

SELECT @daylist_members_added AS members_added, @daylist_transactions_added AS pending_transactions_added, (SELECT COUNT(*) FROM members) AS total_members;

SELECT membership_number, first_name, last_name, email FROM members WHERE membership_number IN ('MEM-20261028-01M6135FJNP5664HJ1W38BP0XB', 'MEM-20261028-01M6135FKKXA43HWW8R9JZ7MG6', 'MEM-20261028-01M6135FKT3BSXGYNF3DK8SBK9', 'MEM-20261028-01M6135FM4SR0ECX0936SX84QB', 'MEM-20261028-01M6135FMHK4FXK7GHFH59SG3Z', 'MEM-20261124-01M85B1AMG00GAE0MPKDP9C0FS', 'MEM-20261124-01M85B1AN1R010DPSAKRH40YAS', 'MEM-20261124-01M85B1ANCY87ZX6HQV7HDCK1V', 'MEM-20261124-01M85B1ANGB2XBHHF09M3D018D', 'MEM-20261124-01M85B1ANN2SQBPY4K5C4P4NV8', 'MEM-20261124-01M85B1ANR4ZPAAP3G5K2N1JRN', 'MEM-20261124-01M85B1ANXKFTDDWB5C6QKSYW8', 'MEM-20261124-01M85B1AP2PAGNGB52NFRS0Z0T') ORDER BY first_name, last_name;
