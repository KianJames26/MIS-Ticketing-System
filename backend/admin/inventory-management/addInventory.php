<?php
include('../../dbconn.php');

$itemType = $_POST['itemType'];
$itemName = $_POST['itemName'];
$itemBrand = $_POST['itemBrand'];
$itemModel = $_POST['itemModel'];
$user = $_POST['user'];
$separtment = $_POST['department'];
$dateAcquired = $_POST['dateAcquired'];
$supplier = $_POST['supplierName'];
$serialNumber = $_POST['serialNumber'];
$itemPrice = $_POST['price'];
$status = $_POST['status'];
$remarks = $_POST['remarks'];
$newFaNumber = "";

if ($itemPrice > 9999.4) {
    $currentYear = date('y', strtotime($dateAcquired));
    $latestFaNumberSql = "SELECT fa_number FROM inventory_records_tbl
                            WHERE fa_number LIKE 'TMRMIS$currentYear-%'
                            ORDER BY fa_number DESC LIMIT 1";
    $stmt = $conn->prepare($latestFaNumberSql);
    $stmt->execute();
    $latesFaNumberResult = $stmt->get_result();
    $stmt->close();

    if ($latesFaNumberResult->num_rows > 0) {
        $latestFaNumberRow = $latesFaNumberResult->fetch_assoc();

        $latesFaNumberRow = $latestFaNumberRow['fa_number'];

        $parts = explode('-', $latesFaNumberRow);

        $latestNumber = (int)$parts[1];

        $newNumber = $latestNumber + 1;
    } else {
        $newNumber = 1;
    }

    $newFaNumber = sprintf("TMRMIS%s-%04d", $currentYear, $newNumber);

    $addItemSql = "INSERT INTO inventory_records_tbl(item_type, item_name, brand, model, date_acquired, supplier, serial_number, remarks, user, department, status, price, fa_number)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($addItemSql);
    $stmt->bind_param("sssssssssssds", $itemType, $itemName, $itemBrand, $itemModel, $dateAcquired, $supplier, $serialNumber, $remarks, $user, $separtment, $status, $itemPrice, $newFaNumber);

    if ($stmt == false) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "internal-error",
            "message" => "Internal Error. Please Contact MIS",
            "data" => $conn->error
        ]);
    } else {
        if (!$stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "internal-error",
                "message" => "Internal Error. Please Contact MIS",
                "data" => $stmt->error
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "success",
                "message" => "Fixed Asset $newFaNumber Added Successfully!",
            ]);
        }
    }
} else {
    $addItemSql = "INSERT INTO inventory_records_tbl(item_type, item_name, brand, model, date_acquired, supplier, serial_number, remarks, user, department, status, price)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($addItemSql);
    $stmt->bind_param("ssssssssssds", $itemType, $itemName, $itemBrand, $itemModel, $dateAcquired, $supplier, $serialNumber, $remarks, $user, $separtment, $status, $itemPrice);

    if ($stmt == false) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "internal-error",
            "message" => "Internal Error. Please Contact MIS",
            "data" => $conn->error
        ]);
    } else {
        if (!$stmt->execute()) {
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "internal-error",
                "message" => "Internal Error. Please Contact MIS",
                "data" => $stmt->error
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                "status" => "success",
                "message" => "New $itemType Added Successfully!"
            ]);
        }
    }
}