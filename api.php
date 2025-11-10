<?php

// api.php
//
// This file acts as a RESTful API endpoint for the pharmacy inventory system.
// It handles all data manipulation requests (add, update, delete) and returns
// a JSON response, allowing the front-end to perform actions without a full
// page refresh.
// Updated to work with the 'medicines' table

// Start a session to handle status messages
session_start();

// Set the response header to JSON
header('Content-Type: application/json');

// Database credentials
$host = "localhost";
$user = "root";
$pass = "";
$db = "pharmaceutical_inventory";

// Establish database connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    // Return a JSON error response on connection failure
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Function to handle errors and return a JSON response
function json_error($message) {
    global $conn;
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $message]);
    $conn->close();
    exit;
}

// Function to get POST data and sanitize
function get_post_data($conn) {
    $data = [
        'name'          => isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '',
        'category'      => isset($_POST['category']) ? $conn->real_escape_string($_POST['category']) : '',
        'form'          => isset($_POST['form']) ? $conn->real_escape_string($_POST['form']) : '',
        'batch'         => isset($_POST['batch']) ? $conn->real_escape_string($_POST['batch']) : '',
        'supplier'      => isset($_POST['supplier']) ? $conn->real_escape_string($_POST['supplier']) : '',
        'cost_price'    => isset($_POST['cost_price']) ? floatval($_POST['cost_price']) : 0,
        'selling_price' => isset($_POST['selling_price']) ? floatval($_POST['selling_price']) : 0,
        'quantity'      => isset($_POST['quantity']) ? intval($_POST['quantity']) : 0,
        'expiry'        => !empty($_POST['expiry']) ? $conn->real_escape_string($_POST['expiry']) : NULL,
    ];
    return $data;
}

// Determine the requested action from the URL or POST data
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'add_medicine':
        $data = get_post_data($conn);
        $sql = "INSERT INTO medicines (ItemName, Category, TypeForm, BatchNumber, SupplierName, CostPrice, SellingPrice, Quantity, ExpiryDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) { json_error('Prepare failed: ' . $conn->error); }
        
        // Type definition string: sssssddis
        // 5 strings, 2 doubles, 1 integer, 1 string
        $stmt->bind_param("sssssddis", $data['name'], $data['category'], $data['form'], $data['batch'], $data['supplier'], $data['cost_price'], $data['selling_price'], $data['quantity'], $data['expiry']);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Medicine added successfully!', 'id' => $conn->insert_id]);
        } else {
            json_error('Error adding medicine: ' . $stmt->error);
        }
        $stmt->close();
        break;

    case 'update_medicine':
        $data = get_post_data($conn);
        $id = intval($_POST['update_id']);
        $sql = "UPDATE medicines SET ItemName=?, Category=?, TypeForm=?, BatchNumber=?, SupplierName=?, CostPrice=?, SellingPrice=?, Quantity=?, ExpiryDate=? WHERE ItemID=?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) { json_error('Prepare failed: ' . $conn->error); }
        
        // Type definition string: sssssddisi
        // 5 strings, 2 doubles, 1 integer, 1 string, 1 integer
        $stmt->bind_param("sssssddisi", $data['name'], $data['category'], $data['form'], $data['batch'], $data['supplier'], $data['cost_price'], $data['selling_price'], $data['quantity'], $data['expiry'], $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Medicine updated successfully!']);
        } else {
            json_error('Error updating medicine: ' . $stmt->error);
        }
        $stmt->close();
        break;
    
    case 'delete_medicine':
        $id = intval($_GET['id']);
        $sql = "DELETE FROM medicines WHERE ItemID=?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) { json_error('Prepare failed: ' . $conn->error); }
        
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Medicine deleted successfully!']);
        } else {
            json_error('Error deleting medicine: ' . $stmt->error);
        }
        $stmt->close();
        break;

    case 'get_inventory':
        // This case is for the front-end to fetch the latest data
        $filter_name = isset($_GET['filter_name']) ? $_GET['filter_name'] : '';
        $filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : '';
        $filter_supplier = isset($_GET['filter_supplier']) ? $_GET['filter_supplier'] : '';
        $sort_expiry = isset($_GET['sort_expiry']) ? $_GET['sort_expiry'] : '';

        $sql = "SELECT ItemID, ItemName, Category, TypeForm, BatchNumber, SupplierName, CostPrice, SellingPrice, Quantity, ExpiryDate FROM medicines WHERE 1";
        $params = [];
        $types = '';

        if (!empty($filter_name)) {
            $sql .= " AND ItemName LIKE ?";
            $params[] = $filter_name . '%';
            $types .= 's';
        }
        if (!empty($filter_category)) {
            $sql .= " AND Category = ?";
            $params[] = $filter_category;
            $types .= 's';
        }
        if (!empty($filter_supplier)) {
            $sql .= " AND SupplierName = ?";
            $params[] = $filter_supplier;
            $types .= 's';
        }
        if (!empty($sort_expiry)) {
            $sql .= " ORDER BY ExpiryDate ASC";
        }

        $stmt = $conn->prepare($sql);
        if ($stmt === false) { json_error('Prepare failed: ' . $conn->error); }
        
        if (!empty($params)) {
            // Using call_user_func_array for compatibility with older PHP versions
            $bind_params = array_merge([$types], $params);
            
            // This is a workaround for call_user_func_array and bind_param needing references
            $refs = [];
            foreach ($bind_params as $key => $value) {
                $refs[$key] = &$bind_params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $refs);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) { json_error('Execution failed or no result set: ' . $stmt->error); }

        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        $stmt->close();
        break;

    default:
        // Invalid action requested
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
        break;
}

// Close the database connection
$conn->close();

?>