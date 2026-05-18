<?php
// ── WorkSpace · Database Config ──────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'workspace_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Graceful fallback — show demo data if DB not connected
    define('DB_CONNECTED', false);
} else {
    define('DB_CONNECTED', true);
    $conn->set_charset('utf8mb4');
}

// ── Demo data fallback (used when DB not connected) ──────────────────
$demo_rooms = [
    ['id'=>1,'name'=>'Quiet Room','type'=>'Individual','capacity'=>10,'base_price'=>25,'description'=>'A calm, distraction-free space with high-speed Wi-Fi and ergonomic seating.','amenities'=>'Wi-Fi, Air Conditioning, Tables & Chairs','image'=>'room-quiet.jpg'],
    ['id'=>2,'name'=>'Gaming Room','type'=>'Individual','capacity'=>8,'base_price'=>35,'description'=>'Cozy setup with LED ambiance and PlayStation consoles for creative breaks.','amenities'=>'Cozy Chairs, LED Lights, PlayStation Console','image'=>'room-gaming.jpg'],
    ['id'=>3,'name'=>'Meeting Room','type'=>'Group','capacity'=>12,'base_price'=>80,'description'=>'Professional boardroom with projector and printer for your team meetings.','amenities'=>'Projector, Printer, Wi-Fi','image'=>'room-meeting.jpg'],
    ['id'=>4,'name'=>'Discussion Room','type'=>'Group','capacity'=>8,'base_price'=>60,'description'=>'Creative collaboration space with a full whiteboard wall.','amenities'=>'Whiteboard, Wi-Fi, Marker Set','image'=>'room-discussion.jpg'],
];

$demo_products = [
    ['id'=>1,'name'=>'Premium Coffee','category'=>'Beverages','price'=>45,'stock'=>50,'description'=>'Freshly brewed specialty coffee, single origin.','image'=>'prod-coffee.jpg'],
    ['id'=>2,'name'=>'Green Tea','category'=>'Beverages','price'=>30,'stock'=>40,'description'=>'Organic green tea, calming and refreshing.','image'=>'prod-tea.jpg'],
    ['id'=>3,'name'=>'Notebook A5','category'=>'Stationery','price'=>55,'stock'=>30,'description'=>'Hardcover dotted notebook, 200 pages.','image'=>'prod-notebook.jpg'],
    ['id'=>4,'name'=>'Mechanical Pencil','category'=>'Stationery','price'=>25,'stock'=>60,'description'=>'0.5mm precise mechanical pencil.','image'=>'prod-pencil.jpg'],
    ['id'=>5,'name'=>'USB-C Hub','category'=>'Tech','price'=>180,'stock'=>15,'description'=>'7-in-1 USB-C hub with HDMI and SD card.','image'=>'prod-hub.jpg'],
    ['id'=>6,'name'=>'Protein Bar','category'=>'Snacks','price'=>40,'stock'=>45,'description'=>'High-protein energy bar, chocolate fudge.','image'=>'prod-bar.jpg'],
];
?>
