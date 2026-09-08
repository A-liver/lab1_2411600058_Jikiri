<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$dataFile = __DIR__ . '/products_data.json';

function loadProducts($dataFile) {
    if (file_exists($dataFile)) {
        $json = file_get_contents($dataFile);
        $data = json_decode($json, true);
        if (is_array($data)) return $data;
    }
    return defaultProducts();
}

function saveProducts($dataFile, $products) {
    file_put_contents($dataFile, json_encode($products, JSON_PRETTY_PRINT));
}

function defaultProducts() {
    return [
        ['id' => 1,  'sku' => 'INV-001', 'name' => 'Chicken Breast',       'category' => 'Meat & Poultry',     'quantity' => 45, 'unitPrice' => 3.20,  'reorderLevel' => 20, 'supplier' => 'FreshFarms Co.',     'lastUpdated' => date('Y-m-d')],
        ['id' => 2,  'sku' => 'INV-002', 'name' => 'Beef Tenderloin',      'category' => 'Meat & Poultry',     'quantity' => 12, 'unitPrice' => 14.50, 'reorderLevel' => 15, 'supplier' => 'Prime Meats Ltd.',   'lastUpdated' => date('Y-m-d')],
        ['id' => 3,  'sku' => 'INV-003', 'name' => 'Lamb Chops',            'category' => 'Meat & Poultry',     'quantity' => 0,  'unitPrice' => 6.80,  'reorderLevel' => 10, 'supplier' => 'FreshFarms Co.',     'lastUpdated' => date('Y-m-d')],
        ['id' => 4,  'sku' => 'INV-004', 'name' => 'Ground Beef',          'category' => 'Meat & Poultry',     'quantity' => 30, 'unitPrice' => 5.10,  'reorderLevel' => 12, 'supplier' => 'Prime Meats Ltd.',   'lastUpdated' => date('Y-m-d')],
        ['id' => 5,  'sku' => 'INV-005', 'name' => 'Salmon Fillet',        'category' => 'Seafood',            'quantity' => 18, 'unitPrice' => 11.90, 'reorderLevel' => 15, 'supplier' => 'Ocean Catch',        'lastUpdated' => date('Y-m-d')],
        ['id' => 6,  'sku' => 'INV-006', 'name' => 'Shrimp',               'category' => 'Seafood',            'quantity' => 8,  'unitPrice' => 9.40,  'reorderLevel' => 10, 'supplier' => 'Ocean Catch',        'lastUpdated' => date('Y-m-d')],
        ['id' => 7,  'sku' => 'INV-007', 'name' => 'Tuna Steak',           'category' => 'Seafood',            'quantity' => 22, 'unitPrice' => 13.20, 'reorderLevel' => 10, 'supplier' => 'Ocean Catch',        'lastUpdated' => date('Y-m-d')],
        ['id' => 8,  'sku' => 'INV-008', 'name' => 'Tomatoes',             'category' => 'Vegetables & Produce', 'quantity' => 60, 'unitPrice' => 0.90, 'reorderLevel' => 25, 'supplier' => 'GreenLeaf Produce', 'lastUpdated' => date('Y-m-d')],
        ['id' => 9,  'sku' => 'INV-009', 'name' => 'Lettuce',              'category' => 'Vegetables & Produce', 'quantity' => 15, 'unitPrice' => 0.70, 'reorderLevel' => 20, 'supplier' => 'GreenLeaf Produce', 'lastUpdated' => date('Y-m-d')],
        ['id' => 10, 'sku' => 'INV-010', 'name' => 'Onions',               'category' => 'Vegetables & Produce', 'quantity' => 50, 'unitPrice' => 0.60, 'reorderLevel' => 20, 'supplier' => 'GreenLeaf Produce', 'lastUpdated' => date('Y-m-d')],
        ['id' => 11, 'sku' => 'INV-011', 'name' => 'Bell Peppers',         'category' => 'Vegetables & Produce', 'quantity' => 0,  'unitPrice' => 1.10, 'reorderLevel' => 15, 'supplier' => 'GreenLeaf Produce', 'lastUpdated' => date('Y-m-d')],
        ['id' => 12, 'sku' => 'INV-012', 'name' => 'Milk',                 'category' => 'Dairy & Eggs',       'quantity' => 40, 'unitPrice' => 1.30,  'reorderLevel' => 15, 'supplier' => 'Dairy Best',         'lastUpdated' => date('Y-m-d')],
        ['id' => 13, 'sku' => 'INV-013', 'name' => 'Mozzarella Cheese',    'category' => 'Dairy & Eggs',       'quantity' => 10, 'unitPrice' => 4.50,  'reorderLevel' => 12, 'supplier' => 'Dairy Best',         'lastUpdated' => date('Y-m-d')],
        ['id' => 14, 'sku' => 'INV-014', 'name' => 'Eggs (dozen)',         'category' => 'Dairy & Eggs',       'quantity' => 55, 'unitPrice' => 2.20,  'reorderLevel' => 20, 'supplier' => 'Dairy Best',         'lastUpdated' => date('Y-m-d')],
        ['id' => 15, 'sku' => 'INV-015', 'name' => 'Butter',               'category' => 'Dairy & Eggs',       'quantity' => 25, 'unitPrice' => 3.00,  'reorderLevel' => 10, 'supplier' => 'Dairy Best',         'lastUpdated' => date('Y-m-d')],
        ['id' => 16, 'sku' => 'INV-016', 'name' => 'Soft Drinks (case)',   'category' => 'Beverages',          'quantity' => 35, 'unitPrice' => 8.00,  'reorderLevel' => 12, 'supplier' => 'BevCo Distributors', 'lastUpdated' => date('Y-m-d')],
        ['id' => 17, 'sku' => 'INV-017', 'name' => 'Bottled Water (case)', 'category' => 'Beverages',          'quantity' => 48, 'unitPrice' => 5.50,  'reorderLevel' => 15, 'supplier' => 'BevCo Distributors', 'lastUpdated' => date('Y-m-d')],
        ['id' => 18, 'sku' => 'INV-018', 'name' => 'Coffee Beans (kg)',    'category' => 'Beverages',          'quantity' => 6,  'unitPrice' => 12.00, 'reorderLevel' => 8,  'supplier' => 'BevCo Distributors', 'lastUpdated' => date('Y-m-d')],
        ['id' => 19, 'sku' => 'INV-019', 'name' => 'Flour',                'category' => 'Dry Goods & Spices', 'quantity' => 70, 'unitPrice' => 1.20,  'reorderLevel' => 25, 'supplier' => "Baker's Supply",     'lastUpdated' => date('Y-m-d')],
        ['id' => 20, 'sku' => 'INV-020', 'name' => 'Rice',                 'category' => 'Dry Goods & Spices', 'quantity' => 33, 'unitPrice' => 1.50,  'reorderLevel' => 20, 'supplier' => "Baker's Supply",     'lastUpdated' => date('Y-m-d')],
        ['id' => 21, 'sku' => 'INV-021', 'name' => 'Black Pepper (kg)',    'category' => 'Dry Goods & Spices', 'quantity' => 3,  'unitPrice' => 15.00, 'reorderLevel' => 5,  'supplier' => "Baker's Supply",     'lastUpdated' => date('Y-m-d')],
        ['id' => 22, 'sku' => 'INV-022', 'name' => 'Bread Rolls (dozen)',  'category' => 'Bakery & Bread',     'quantity' => 20, 'unitPrice' => 3.50,  'reorderLevel' => 15, 'supplier' => 'Sunrise Bakery',     'lastUpdated' => date('Y-m-d')],
        ['id' => 23, 'sku' => 'INV-023', 'name' => 'Baguette',             'category' => 'Bakery & Bread',     'quantity' => 9,  'unitPrice' => 2.80,  'reorderLevel' => 10, 'supplier' => 'Sunrise Bakery',     'lastUpdated' => date('Y-m-d')],
    ];
}

$method = $_SERVER['REQUEST_METHOD'];
$products = loadProducts($dataFile);

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $product = null;
        foreach ($products as $p) {
            if ($p['id'] === $id) { $product = $p; break; }
        }
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }
    } else {
        echo json_encode($products);
    }
    exit();
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing product id']);
        exit();
    }

    $found = false;
    foreach ($products as &$p) {
        if ($p['id'] === (int) $input['id']) {
            if (isset($input['quantity'])) {
                $p['quantity'] = (int) $input['quantity'];
            }
            $p['lastUpdated'] = date('Y-m-d');
            $found = true;
            break;
        }
    }
    unset($p);

    if (!$found) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit();
    }

    saveProducts($dataFile, $products);
    echo json_encode(['success' => true]);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
