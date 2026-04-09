<?php

/**
 * Helper to safely locate files whether they are in the root or an includes directory
 */
function getValidFilePath($filename) {
    $locations = [
        $filename, 
        __DIR__ . '/' . $filename, 
        __DIR__ . '/../' . $filename
    ];
    foreach ($locations as $loc) {
        if (file_exists($loc)) {
            return $loc;
        }
    }
    return $filename; 
}

/**
 * Reads customers.txt into an associative array, handling broken line wrap
 */
function readCustomers($filename) {
    $customers = [];
    $filepath = getValidFilePath($filename);
    $lines = @file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if (!$lines) return $customers;
    
    $currentRecord = "";
    foreach ($lines as $line) {
        // Customer records start with an integer ID followed by a semicolon
        if (preg_match('/^\s*\d+;/', $line)) {
            if ($currentRecord !== "") {
                $c = parseCustomerRecord($currentRecord);
                if ($c) $customers[$c['id']] = $c;
            }
            $currentRecord = trim($line);
        } else {
            // Append broken lines back to the current record
            $currentRecord .= trim($line);
        }
    }
    
    // Process the final record
    if ($currentRecord !== "") {
        $c = parseCustomerRecord($currentRecord);
        if ($c) $customers[$c['id']] = $c;
    }
    
    return $customers;
}

function parseCustomerRecord($record) {
    $fields = explode(';', $record);
    if (count($fields) >= 12) {
        return [
            'id' => trim($fields[0]),
            'firstName' => trim($fields[1]),
            'lastName' => trim($fields[2]),
            'email' => trim($fields[3]),
            'university' => trim($fields[4]),
            'address' => trim($fields[5]),
            'city' => trim($fields[6]),
            'state' => trim($fields[7]),
            'country' => trim($fields[8]),
            'zip' => trim($fields[9]),
            'phone' => trim($fields[10]),
            'sales' => trim($fields[11])
        ];
    }
    return null;
}

/**
 * Reads orders.txt into an array, handling broken titles and line wraps
 */
function readOrders($filename) {
    $orders = [];
    $filepath = getValidFilePath($filename);
    $lines = @file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    if (!$lines) return $orders;
    
    $currentRecord = "";
    foreach ($lines as $line) {
        // Order records start with an integer ID followed by a comma
        if (preg_match('/^\s*\d+,/', $line)) {
            if ($currentRecord !== "") {
                $o = parseOrderRecord($currentRecord);
                if ($o) $orders[] = $o;
            }
            $currentRecord = trim($line);
        } else {
            // Append with a space for broken titles
            $currentRecord .= " " . trim($line);
        }
    }
    
    if ($currentRecord !== "") {
        $o = parseOrderRecord($currentRecord);
        if ($o) $orders[] = $o;
    }
    
    return $orders;
}

function parseOrderRecord($record) {
    $parts = explode(',', $record);
    if (count($parts) < 5) return null;
    
    $order_id = trim($parts[0]);
    $customer_id = trim($parts[1]);
    $isbn = trim($parts[2]);
    
    // Book category is the last element
    $category = trim(array_pop($parts));
    // The title is everything remaining, joined by commas in case the title itself contained a comma
    $title = trim(implode(',', array_slice($parts, 3)));
    
    return [
        'order_id' => $order_id,
        'customer_id' => $customer_id,
        'isbn' => $isbn,
        'title' => $title,
        'category' => $category
    ];
}

?>