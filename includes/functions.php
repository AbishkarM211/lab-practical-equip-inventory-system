<?php

function lazyCleanupExpiredReservations($conn) {
    mysqli_query($conn, "UPDATE physical_items pi JOIN borrow_records br ON br.item_id = pi.item_id SET pi.status = 'available' WHERE br.status = 'reserved' AND br.expiry_time < NOW()");
    mysqli_query($conn, "UPDATE borrow_records SET status = 'expired' WHERE status = 'reserved' AND expiry_time < NOW()");
}

function handleEquipmentImageUpload($file) {
    if (empty($file['tmp_name'])) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/equipment/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = time() . '_' . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

    return 'uploads/equipment/' . $filename;
}


function getAvailableItems($conn, $search = '') {
    lazyCleanupExpiredReservations($conn);

    $sql = "SELECT pi.item_id, pi.serial_number, em.model_id, em.name, em.description, em.category, em.image_path, em.item_value FROM physical_items pi JOIN equipment_models em ON em.model_id = pi.model_id WHERE pi.status = 'available'";

    if ($search !== '') {
        $safeSearch = mysqli_real_escape_string($conn, $search);
        $sql .= " AND (em.name LIKE '%$safeSearch%' OR em.category LIKE '%$safeSearch%' OR pi.serial_number LIKE '%$safeSearch%')";
    }

    $sql .= " ORDER BY em.name, pi.serial_number";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getItem($conn, $itemId) {
    $sql = "SELECT pi.item_id, pi.serial_number, pi.status, em.model_id, em.name, em.description, em.category, em.image_path, em.item_value
            FROM physical_items pi
            JOIN equipment_models em ON em.model_id = pi.model_id
            WHERE pi.item_id = $itemId";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result) ?: null;
}

function reserveItem($conn, $userId, $itemId) {
    lazyCleanupExpiredReservations($conn);

    // Check if item is available
    $result = mysqli_query($conn, "SELECT status FROM physical_items WHERE item_id = $itemId");
    $item = mysqli_fetch_assoc($result);

    if (!$item || $item['status'] !== 'available') {
        return ['success' => false, 'message' => 'Item is not available for reservation.'];
    }

    // Insert borrow record
    $hours = RESERVATION_HOURS;
    mysqli_query($conn, "INSERT INTO borrow_records (user_id, item_id, status, reservation_time, expiry_time) VALUES ($userId, $itemId, 'reserved', NOW(), DATE_ADD(NOW(), INTERVAL $hours HOUR))");
    $borrowId = mysqli_insert_id($conn);

    // Mark item as reserved
    mysqli_query($conn, "UPDATE physical_items SET status = 'reserved' WHERE item_id = $itemId");

    // Get expiry time
    $result = mysqli_query($conn, "SELECT expiry_time FROM borrow_records WHERE borrow_id = $borrowId");
    $row = mysqli_fetch_assoc($result);

    return ['success' => true, 'borrow_id' => $borrowId, 'expiry_time' => $row['expiry_time']];
}

function releaseReservation($conn, $borrowId, $dueDate) {
    $safeDueDate = mysqli_real_escape_string($conn, $dueDate);
    $result = mysqli_query($conn, "SELECT item_id FROM borrow_records WHERE borrow_id = $borrowId");
    $record = mysqli_fetch_assoc($result);

    if (!$record) {
        return ['success' => false, 'message' => 'Reservation not found.'];
    }

    // Update record to borrowed
    mysqli_query($conn, "UPDATE borrow_records SET status = 'borrowed', borrow_date = NOW(), due_date = '$safeDueDate' WHERE borrow_id = $borrowId");
    
    // Update physical item status
    mysqli_query($conn, "UPDATE physical_items SET status = 'borrowed' WHERE item_id = {$record['item_id']}");

    return ['success' => true];
}

function cancelReservation($conn, $borrowId) {
    $result = mysqli_query($conn, "SELECT item_id FROM borrow_records WHERE borrow_id = $borrowId");
    $record = mysqli_fetch_assoc($result);

    if (!$record) {
        return ['success' => false, 'message' => 'Reservation not found.'];
    }

    mysqli_query($conn, "UPDATE borrow_records SET status = 'cancelled' WHERE borrow_id = $borrowId");
    mysqli_query($conn, "UPDATE physical_items SET status = 'available' WHERE item_id = {$record['item_id']}");

    return ['success' => true];
}

function returnItem($conn, $borrowId, $condition, $processedByUserId) {
    $sql = "SELECT br.*, pi.item_id AS pi_item_id, pi.serial_number, em.name AS model_name, em.item_value FROM borrow_records br JOIN physical_items pi ON pi.item_id = br.item_id JOIN equipment_models em ON em.model_id = pi.model_id WHERE br.borrow_id = $borrowId";
    $result = mysqli_query($conn, $sql);
    $record = mysqli_fetch_assoc($result);

    if (!$record) {
        return ['success' => false, 'message' => 'Record not found.'];
    }

    $finesIssued = [];

    // Calculate late fees if overdue
    if ($record['due_date']) {
        $daysLate = getDaysLate($conn, $record['due_date']);
        if ($daysLate > 0) {
            $lateFee = calculateLateFee($daysLate);
            issueFine($conn, $borrowId, $lateFee, 'late');
            $finesIssued[] = ['reason' => 'late', 'amount' => $lateFee];
        }
    }

    // Handle return conditions
    if ($condition === 'good') {
        $newStatus = 'returned';
        $itemStatus = 'available';
    } else {
        $newStatus = $condition;
        $itemStatus = $condition;
        
        issueFine($conn, $borrowId, (float) $record['item_value'], $condition);
        $finesIssued[] = ['reason' => $condition, 'amount' => (float) $record['item_value']];
        
        logDamageOrLossRow(
            $conn,
            (int) $record['pi_item_id'],
            $borrowId,
            $record['serial_number'],
            $record['model_name'],
            (float) $record['item_value'],
            $condition,
            'Logged on return.',
            $processedByUserId
        );
    }

    $safeCondition = mysqli_real_escape_string($conn, $newStatus);
    $safeItemStatus = mysqli_real_escape_string($conn, $itemStatus);

    mysqli_query($conn, "UPDATE borrow_records SET status = '$safeCondition', return_date = NOW() WHERE borrow_id = $borrowId");
    mysqli_query($conn, "UPDATE physical_items SET status = '$safeItemStatus' WHERE item_id = {$record['pi_item_id']}");

    return ['success' => true, 'fines' => $finesIssued];
}



function getDaysLate($conn, $dueDate) {
    $safeDueDate = mysqli_real_escape_string($conn, $dueDate);
    $result = mysqli_query($conn, "SELECT GREATEST(CEIL(TIMESTAMPDIFF(SECOND, '$safeDueDate', NOW()) / 86400), 0) AS days_late");
    $row = mysqli_fetch_assoc($result);
    return (int) $row['days_late'];
}

function calculateLateFee($daysLate, $dailyRate = null) {
    $rate = $dailyRate ?? LATE_FEE_PER_DAY;
    return round($daysLate * $rate, 2);
}

function issueFine($conn, $borrowId, $amount, $reason) {
    $safeReason = mysqli_real_escape_string($conn, $reason);
    mysqli_query($conn, "INSERT INTO fines (borrow_id, fine_amount, fine_reason, payment_status) VALUES ($borrowId, $amount, '$safeReason', 'unpaid')");
    return mysqli_insert_id($conn);
}

function getFines($conn, $status) {
    $safeStatus = mysqli_real_escape_string($conn, $status);
    $sql = "SELECT f.fine_id, f.borrow_id, f.fine_amount, f.fine_reason, f.created_at AS issued_at, f.paid_at,
                   u.full_name, em.name AS item_name, pi.serial_number
            FROM fines f
            JOIN borrow_records br ON br.borrow_id = f.borrow_id
            JOIN users u ON u.user_id = br.user_id
            JOIN physical_items pi ON pi.item_id = br.item_id
            JOIN equipment_models em ON em.model_id = pi.model_id
            WHERE f.payment_status = '$safeStatus'
            ORDER BY f.created_at DESC";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function markFinePaid($conn, $fineId) {
    mysqli_query($conn, "UPDATE fines SET payment_status = 'paid', paid_at = NOW() WHERE fine_id = $fineId AND payment_status = 'unpaid'");
    return mysqli_affected_rows($conn) > 0;
}


function sendNotification($conn, $userId, $message) {
    try {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO notifications (user_id, message)
            VALUES (?, ?)
        ");

        mysqli_stmt_bind_param($stmt, "is", $userId, $message);

        $success = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $success;

    } catch (mysqli_sql_exception $e) {
        error_log("Notification failed: " . $e->getMessage());
        return false;
    }
}


function getNotifications($conn, $userId) {
    $stmt = mysqli_prepare($conn, "
        SELECT *
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $notifications = mysqli_fetch_all($result, MYSQLI_ASSOC);

    mysqli_stmt_close($stmt);

    return $notifications;
}

function updateNotificationStatus($conn, $notificationId, $userId, $isRead) {
    $stmt = mysqli_prepare($conn, "UPDATE notifications SET is_read = ? WHERE notification_id = ? AND user_id = ?");

    mysqli_stmt_bind_param($stmt, "iii", $isRead, $notificationId, $userId);

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}


function sendDueSoonReminders($conn, $hoursBefore = 24) {
    $hoursBefore = (int) $hoursBefore;

    if ($hoursBefore <= 0) {
        return;
    }

    try {
        $sql = "
            SELECT
                borrow.borrow_id,
                borrow.user_id,
                borrow.due_date,
                model.name
            FROM borrow_records AS borrow
            INNER JOIN physical_items AS item
                ON item.item_id = borrow.item_id
            INNER JOIN equipment_models AS model
                ON model.model_id = item.model_id
            WHERE borrow.status = 'borrowed'
            AND borrow.due_reminder_sent_at IS NULL
            AND borrow.due_date BETWEEN NOW()
                AND DATE_ADD(NOW(), INTERVAL $hoursBefore HOUR)
        ";

        $result = mysqli_query($conn, $sql);
        $records = mysqli_fetch_all($result, MYSQLI_ASSOC);

    } catch (mysqli_sql_exception $e) {
        error_log("Due reminder query failed: " . $e->getMessage());
        return;
    }

    foreach ($records as $row) {
        try {
            mysqli_begin_transaction($conn);

            // Claim this reminder to prevent duplicates
            $stmt = mysqli_prepare($conn, "
                UPDATE borrow_records
                SET due_reminder_sent_at = NOW()
                WHERE borrow_id = ?
                AND status = 'borrowed'
                AND due_reminder_sent_at IS NULL
                AND due_date BETWEEN NOW()
                    AND DATE_ADD(NOW(), INTERVAL ? HOUR)
            ");

            $borrowId = (int) $row['borrow_id'];

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $borrowId,
                $hoursBefore
            );

            mysqli_stmt_execute($stmt);

            $claimed = mysqli_stmt_affected_rows($stmt);

            mysqli_stmt_close($stmt);

            // Another request may have already claimed it
            if ($claimed !== 1) {
                mysqli_commit($conn);
                continue;
            }

            // Create the student notification
            $msg = "'{$row['name']}' is due soon on {$row['due_date']}.";

            $sent = sendNotification(
                $conn,
                (int) $row['user_id'],
                $msg
            );

            if ($sent) {
                mysqli_commit($conn);
            } else {
                // Allow the reminder to be attempted again
                mysqli_rollback($conn);
            }

        } catch (mysqli_sql_exception $e) {
            mysqli_rollback($conn);
            error_log("Due reminder processing failed: " . $e->getMessage());
        }
    }
}


function getRecentlyExpiredReservations($conn, $limit = 20) {
    $sql = "SELECT br.borrow_id, br.expiry_time, u.full_name, em.name, pi.serial_number FROM borrow_records br JOIN users u ON u.user_id = br.user_id JOIN physical_items pi ON pi.item_id = br.item_id JOIN equipment_models em ON em.model_id = pi.model_id WHERE br.status = 'expired' ORDER BY br.expiry_time DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}



function getEquipmentModelsSummary($conn) {
    $sql = "SELECT em.model_id, em.name, em.description, em.category, em.item_value, em.image_path, COUNT(pi.item_id) AS total_units, SUM(pi.status = 'available') AS available_units, SUM(pi.status = 'reserved') AS reserved_units, SUM(pi.status = 'borrowed') AS borrowed_units, SUM(pi.status = 'damaged') AS damaged_units, SUM(pi.status = 'lost') AS lost_units FROM equipment_models em LEFT JOIN physical_items pi ON pi.model_id = em.model_id GROUP BY em.model_id ORDER BY em.name";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getEquipmentModel($conn, $modelId) {
    $result = mysqli_query($conn, "SELECT * FROM equipment_models WHERE model_id = $modelId");
    return mysqli_fetch_assoc($result) ?: null;
}

function updateEquipmentModel($conn, $modelId, $name, $description, $category, $itemValue, $newImagePath = null) {
    if (empty($name)) {
        return ['success' => false, 'message' => 'Name is required.'];
    }

    $safeName = mysqli_real_escape_string($conn, $name);
    $safeDesc = mysqli_real_escape_string($conn, $description);
    $safeCat = mysqli_real_escape_string($conn, $category);

    if ($newImagePath) {
        $safePath = mysqli_real_escape_string($conn, $newImagePath);
        $sql = "UPDATE equipment_models SET name = '$safeName', description = '$safeDesc', category = '$safeCat', item_value = $itemValue, image_path = '$safePath' WHERE model_id = $modelId";
    } else {
        $sql = "UPDATE equipment_models SET name = '$safeName', description = '$safeDesc', category = '$safeCat', item_value = $itemValue WHERE model_id = $modelId";
    }

    mysqli_query($conn, $sql);
    return ['success' => true];
}

function deleteEquipmentModel($conn, $modelId) {
    mysqli_query($conn, "DELETE FROM equipment_models WHERE model_id = $modelId");
    return ['success' => true];
}

function getPhysicalItemsForModel($conn, $modelId) {
    $result = mysqli_query($conn, "SELECT * FROM physical_items WHERE model_id = $modelId ORDER BY serial_number");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getPhysicalItem($conn, $itemId) {
    $result = mysqli_query($conn, "SELECT * FROM physical_items WHERE item_id = $itemId");
    return mysqli_fetch_assoc($result) ?: null;
}

function getAvailableItemsForDamageLog($conn) {
    $sql = "SELECT pi.item_id, pi.serial_number, em.name FROM physical_items pi JOIN equipment_models em ON em.model_id = pi.model_id WHERE pi.status = 'available' ORDER BY em.name, pi.serial_number";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function updatePhysicalItem($conn, $itemId, $serialNumber, $status) {
    $safeSerial = mysqli_real_escape_string($conn, $serialNumber);
    $safeStatus = mysqli_real_escape_string($conn, $status);
    
    mysqli_query($conn, "UPDATE physical_items SET serial_number = '$safeSerial', status = '$safeStatus' WHERE item_id = $itemId");
    return ['success' => true];
}

function deletePhysicalItem($conn, $itemId) {
    mysqli_query($conn, "DELETE FROM physical_items WHERE item_id = $itemId");
    return ['success' => true];
}

function logDamageOrLossRow($conn, $itemId, $borrowId, $serialNumber, $itemName, $itemValue, $reason, $notes, $loggedBy, $imagePath = null) {
    $sql = "INSERT INTO damage_loss (item_id, borrow_id, serial_number, item_name, item_value,image_path, reason, notes, logged_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iissdsssi", $itemId, $borrowId, $serialNumber, $itemName, $itemValue, $imagePath, $reason, $notes, $loggedBy);
    mysqli_stmt_execute($stmt);
    $logId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $logId;
}

function logDamageOrLoss($conn, $itemId, $reason, $notes, $loggedBy) {
    if (!in_array($reason, ['damaged', 'lost'], true)) {
        return [
            'success' => false,
            'message' => 'Invalid damage/loss reason.'
        ];
    }
    try {
        mysqli_begin_transaction($conn);

        $sql = "SELECT pi.item_id, pi.serial_number, pi.status, em.name, em.item_value FROM physical_items pi JOIN equipment_models em ON em.model_id = pi.model_id WHERE pi.item_id = ? FOR UPDATE";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $itemId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$item) {
            mysqli_rollback($conn);

            return [
                'success' => false,
                'message' => 'Item not found.'
            ];
        }

        if ($item['status'] !== 'available') {
            mysqli_rollback($conn);

            return [
                'success' => false,
                'message' => 'Only available units can be logged through this page.'
            ];
        }

        logDamageOrLossRow($conn, $itemId, null, $item['serial_number'], $item['name'], (float) $item['item_value'], $reason, $notes, $loggedBy);

        $sql = "UPDATE physical_items SET status = ? WHERE item_id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "si", $reason, $itemId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        return ['success' => true];

    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($conn);
        error_log("Damage/loss logging failed: " . $e->getMessage());

        return [
            'success' => false,
            'message' => 'Unable to record the damage/loss entry. Please try again.'
        ];
    }
}

function getDamageLossHistoryForItem($conn, $itemId) {
    $sql = "SELECT dl.log_id, dl.borrow_id, dl.serial_number, dl.item_name, dl.item_value, dl.image_path, dl.reason, dl.notes, dl.logged_at, u.full_name AS logged_by_name FROM damage_loss dl JOIN users u ON u.user_id = dl.logged_by WHERE dl.item_id = ? ORDER BY dl.logged_at DESC, dl.log_id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $itemId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $history = mysqli_fetch_all($result, MYSQLI_ASSOC);

    mysqli_stmt_close($stmt);

    return $history;
}


function getDamageLossLog($conn, $limit = 50) {
    $limit = max(1, (int) $limit);
    $sql = "SELECT dl.*, u.full_name AS logged_by_name FROM damage_loss dl JOIN users u ON u.user_id = dl.logged_by ORDER BY dl.logged_at DESC, dl.log_id DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getActiveBorrowsForNotify($conn) {
    $sql = "SELECT br.borrow_id, br.due_date, u.user_id, u.full_name, em.name, pi.serial_number FROM borrow_records br JOIN users u ON u.user_id = br.user_id JOIN physical_items pi ON pi.item_id = br.item_id JOIN equipment_models em ON em.model_id = pi.model_id WHERE br.status = 'borrowed' ORDER BY br.due_date";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getAllStudents($conn) {
    $sql = "SELECT u.user_id, u.username, u.full_name, u.email, u.is_active, u.created_at, SUM(br.status IN ('reserved', 'borrowed')) AS active_borrows, SUM(f.payment_status = 'unpaid') AS unpaid_fines FROM users u LEFT JOIN borrow_records br ON br.user_id = u.user_id LEFT JOIN fines f ON f.borrow_id = br.borrow_id WHERE u.role = 'student' GROUP BY u.user_id ORDER BY u.full_name";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function cancelStudentReservation($conn, $userId, $borrowId) {
    $userId = (int) $userId;
    $borrowId = (int) $borrowId;

    $result = mysqli_query($conn, "SELECT item_id, status FROM borrow_records WHERE borrow_id = $borrowId AND user_id = $userId");
    $record = mysqli_fetch_assoc($result);

    if (!$record || $record['status'] !== 'reserved') {
        return ['success' => false, 'message' => 'Active reservation not found.'];
    }

    mysqli_query($conn, "UPDATE borrow_records SET status = 'cancelled' WHERE borrow_id = $borrowId AND user_id = $userId");
    mysqli_query($conn, "UPDATE physical_items SET status = 'available' WHERE item_id = {$record['item_id']} AND status = 'reserved'");

    return ['success' => true];
}

function getStudent($conn, $userId) {
    $result = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $userId AND role = 'student'");
    return mysqli_fetch_assoc($result) ?: null;
}

function updateStudentProfile($conn, $userId, $fullName, $email) {
    $safeName = mysqli_real_escape_string($conn, $fullName);
    $safeEmail = mysqli_real_escape_string($conn, $email);

    mysqli_query($conn, "UPDATE users SET full_name = '$safeName', email = '$safeEmail' WHERE user_id = $userId AND role = 'student'");
    return ['success' => true];
}

function setStudentActive($conn, $userId, $active) {
    $status = $active ? 1 : 0;
    mysqli_query($conn, "UPDATE users SET is_active = $status WHERE user_id = $userId AND role = 'student'");
}

function deleteStudent($conn, $userId) {
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $userId AND role = 'student'");
    return ['success' => true];
}



function getReportSummary($conn) {
    $unitStatusRes = mysqli_query($conn, "SELECT status, COUNT(*) AS c FROM physical_items GROUP BY status");
    $unitStatus = [];
    while ($row = mysqli_fetch_assoc($unitStatusRes)) {
        $unitStatus[$row['status']] = $row['c'];
    }

    $studentRes = mysqli_query($conn, "SELECT SUM(is_active = 1) AS active, SUM(is_active = 0) AS inactive FROM users WHERE role = 'student'");
    $studentCounts = mysqli_fetch_assoc($studentRes);

    $fineRes = mysqli_query($conn, "SELECT SUM(payment_status = 'unpaid') AS unpaid_count, SUM(CASE WHEN payment_status = 'unpaid' THEN fine_amount ELSE 0 END) AS unpaid_amount, SUM(payment_status = 'paid') AS paid_count, SUM(CASE WHEN payment_status = 'paid' THEN fine_amount ELSE 0 END) AS paid_amount FROM fines");
    $fineTotals = mysqli_fetch_assoc($fineRes);

    $overdueRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM borrow_records WHERE status = 'borrowed' AND due_date < NOW()");
    $overdueCount = mysqli_fetch_assoc($overdueRes)['total'];

    return [
        'units_by_status'     => $unitStatus,
        'total_units'         => array_sum($unitStatus),
        'students_active'     => (int) ($studentCounts['active'] ?? 0),
        'students_inactive'   => (int) ($studentCounts['inactive'] ?? 0),
        'fines_unpaid_count'  => (int) ($fineTotals['unpaid_count'] ?? 0),
        'fines_unpaid_amount' => (float) ($fineTotals['unpaid_amount'] ?? 0),
        'fines_paid_count'    => (int) ($fineTotals['paid_count'] ?? 0),
        'fines_paid_amount'   => (float) ($fineTotals['paid_amount'] ?? 0),
        'overdue_count'       => (int) $overdueCount,
    ];
}

function getOverdueBorrows($conn) {
    $sql = "SELECT br.borrow_id, br.due_date, u.full_name, em.name, pi.serial_number FROM borrow_records br JOIN users u ON u.user_id = br.user_id JOIN physical_items pi ON pi.item_id = br.item_id JOIN equipment_models em ON em.model_id = pi.model_id WHERE br.status = 'borrowed' AND br.due_date < NOW() ORDER BY br.due_date";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function searchAll($conn, $search) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $like = '%' . $safeSearch . '%';

    $eqRes = mysqli_query($conn, "SELECT model_id, name, category, description FROM equipment_models WHERE name LIKE '$like' OR description LIKE '$like' OR category LIKE '$like' LIMIT 25");
    $equipment = mysqli_fetch_all($eqRes, MYSQLI_ASSOC);

    $unitRes = mysqli_query($conn, "SELECT pi.item_id, pi.serial_number, pi.status, em.name FROM physical_items pi JOIN equipment_models em ON em.model_id = pi.model_id WHERE pi.serial_number LIKE '$like' LIMIT 25");
    $units = mysqli_fetch_all($unitRes, MYSQLI_ASSOC);

    $userRes = mysqli_query($conn, "SELECT user_id, username, full_name, email, phone, role, is_active FROM users WHERE full_name LIKE '$like' OR username LIKE '$like' OR email LIKE '$like' LIMIT 25");
    $users = mysqli_fetch_all($userRes, MYSQLI_ASSOC);

    $borrows = [];
    if (is_numeric($search)) {
        $borrowRes = mysqli_query($conn, "SELECT br.borrow_id, br.status, u.full_name, em.name, pi.serial_number FROM borrow_records br JOIN users u ON u.user_id = br.user_id JOIN physical_items pi ON pi.item_id = br.item_id JOIN equipment_models em ON em.model_id = pi.model_id WHERE br.borrow_id = $search");
        $borrows = mysqli_fetch_all($borrowRes, MYSQLI_ASSOC);
    }

    return compact('equipment', 'units', 'users', 'borrows');
}
