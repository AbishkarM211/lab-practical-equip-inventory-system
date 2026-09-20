<?php
function lazyCleanupExpiredReservations($conn) {
    $result = mysqli_query($conn, "SELECT item_id FROM borrow_records WHERE status = 'reserved' AND expiry_time < NOW()");

    while ($row = mysqli_fetch_assoc($result)) {
        $itemId = (int) $row['item_id'];
        mysqli_query($conn, "UPDATE physical_items SET status = 'available' WHERE item_id = $itemId AND status = 'reserved'");
    }

    mysqli_query($conn, "UPDATE borrow_records SET status = 'expired' WHERE status = 'reserved' AND expiry_time < NOW()");
}

function getAvailableItems($conn) {
    lazyCleanupExpiredReservations($conn);

    $result = mysqli_query($conn, "
        SELECT pi.item_id, pi.serial_number, em.name, em.description, em.category
        FROM physical_items pi
        JOIN equipment_models em ON em.model_id = pi.model_id
        WHERE pi.status = 'available'
        ORDER BY em.name, pi.serial_number
    ");

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function reserveItem($conn, $userId, $itemId) {
    lazyCleanupExpiredReservations($conn);

    $itemId = (int) $itemId;
    $userId = (int) $userId;

    $result = mysqli_query($conn, "SELECT status FROM physical_items WHERE item_id = $itemId");
    $item = mysqli_fetch_assoc($result);

    if (!$item || $item['status'] !== 'available') {
        return ['success' => false, 'message' => 'Item is not available for reservation.'];
    }

    $hours = RESERVATION_HOURS;
    mysqli_query($conn, "INSERT INTO borrow_records (user_id, item_id, status, reservation_time, expiry_time) VALUES ($userId, $itemId, 'reserved', NOW(), DATE_ADD(NOW(), INTERVAL $hours HOUR))");

    if (!mysqli_query($conn, "UPDATE physical_items SET status = 'reserved' WHERE item_id = $itemId AND status = 'available'")) {
        return ['success' => false, 'message' => 'Could not reserve the item.'];
    }

    $borrowId = mysqli_insert_id($conn);
    $result = mysqli_query($conn, "SELECT expiry_time FROM borrow_records WHERE borrow_id = $borrowId");
    $row = mysqli_fetch_assoc($result);

    return ['success' => true, 'borrow_id' => $borrowId, 'expiry_time' => $row['expiry_time']];
}

function releaseReservation($conn, $borrowId, $dueDate) {
    $borrowId = (int) $borrowId;
    $safeDueDate = mysqli_real_escape_string($conn, $dueDate);

    $result = mysqli_query($conn, "SELECT item_id, status FROM borrow_records WHERE borrow_id = $borrowId");
    $record = mysqli_fetch_assoc($result);

    if (!$record || $record['status'] !== 'reserved') {
        return ['success' => false, 'message' => 'Active reservation not found.'];
    }

    mysqli_query($conn, "UPDATE borrow_records SET status = 'borrowed', borrow_date = NOW(), due_date = '$safeDueDate' WHERE borrow_id = $borrowId");
    mysqli_query($conn, "UPDATE physical_items SET status = 'borrowed' WHERE item_id = {$record['item_id']}");

    return ['success' => true];
}

function cancelReservation($conn, $borrowId) {
    $borrowId = (int) $borrowId;
    $result = mysqli_query($conn, "SELECT item_id, status FROM borrow_records WHERE borrow_id = $borrowId");
    $record = mysqli_fetch_assoc($result);

    if (!$record || $record['status'] !== 'reserved') {
        return ['success' => false, 'message' => 'Active reservation not found.'];
    }

    mysqli_query($conn, "UPDATE borrow_records SET status = 'cancelled' WHERE borrow_id = $borrowId");
    mysqli_query($conn, "UPDATE physical_items SET status = 'available' WHERE item_id = {$record['item_id']}");

    return ['success' => true];
}

function returnItem($conn, $borrowId) {
    $borrowId = (int) $borrowId;
    $result = mysqli_query($conn, "SELECT item_id, status FROM borrow_records WHERE borrow_id = $borrowId");
    $record = mysqli_fetch_assoc($result);

    if (!$record || $record['status'] !== 'borrowed') {
        return ['success' => false, 'message' => 'Active borrowed record not found.'];
    }

    mysqli_query($conn, "UPDATE borrow_records SET status = 'returned', return_date = NOW() WHERE borrow_id = $borrowId");
    mysqli_query($conn, "UPDATE physical_items SET status = 'available' WHERE item_id = {$record['item_id']}");

    return ['success' => true];
}

function getEquipmentModels($conn) {
    $result = mysqli_query($conn, "SELECT model_id, name, description, category FROM equipment_models ORDER BY name");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getPhysicalItemsForModel($conn, $modelId) {
    $modelId = (int) $modelId;
    $result = mysqli_query($conn, "SELECT item_id, serial_number, status FROM physical_items WHERE model_id = $modelId ORDER BY serial_number");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getAllStudents($conn) {
    $result = mysqli_query($conn, "SELECT user_id, username, full_name, email FROM users WHERE role = 'student' ORDER BY user_id DESC");
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
