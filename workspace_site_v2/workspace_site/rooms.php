<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Rooms — WorkSpace</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .page-hero {
      background: linear-gradient(135deg, var(--accent2) 0%, #1a2530 100%);
      color: white; padding: 72px 48px;
      text-align: center;
    }
    .page-hero h1 { font-family:'Playfair Display',serif; font-size:clamp(32px,5vw,56px); margin-bottom:12px; }
    .page-hero p  { color: rgba(255,255,255,0.7); font-size:16px; }

    .filter-bar {
      display: flex; gap: 12px; justify-content: center;
      padding: 32px 48px; background: var(--white);
      border-bottom: 1px solid var(--border); flex-wrap: wrap;
    }
    .filter-btn {
      padding: 10px 24px; border-radius: 8px;
      border: 1.5px solid var(--border);
      background: transparent; cursor: pointer;
      font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600;
      color: var(--muted); transition: all 0.2s;
    }
    .filter-btn:hover, .filter-btn.active {
      background: var(--accent); border-color: var(--accent);
      color: white;
    }

    .rooms-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 28px; }

    .room-card {
      background: var(--white); border: 1px solid var(--border);
      border-radius: 16px; overflow: hidden;
      transition: transform 0.25s, box-shadow 0.25s;
    }
    .room-card:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(0,0,0,0.1); }

    .room-card-img {
      height: 220px; display: flex; align-items: center;
      justify-content: center; font-size: 64px;
      position: relative; overflow: hidden;
    }
    .room-card-type-badge {
      position: absolute; top: 16px; left: 16px;
      background: var(--accent); color: white;
      font-size: 11px; font-weight: 700; letter-spacing: 2px;
      text-transform: uppercase; padding: 4px 12px; border-radius: 20px;
    }
    .room-card-body { padding: 28px; }
    .room-card-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
    .room-card-name { font-family:'Playfair Display',serif; font-size:22px; font-weight:700; }
    .room-card-price { font-size:20px; font-weight:700; color:var(--accent); }
    .room-card-price small { font-size:12px; color:var(--muted); font-weight:400; }
    .room-card-desc { color:var(--muted); font-size:14px; line-height:1.6; margin-bottom:20px; }

    .amenities-list { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:24px; }
    .amenity-tag {
      background: var(--bg); border:1px solid var(--border);
      border-radius:20px; padding:4px 12px;
      font-size:12px; color:var(--muted); font-weight:500;
    }

    .room-card-footer {
      display:flex; justify-content:space-between; align-items:center;
      padding-top:20px; border-top:1px solid var(--border);
    }
    .capacity-info { font-size:13px; color:var(--muted); }
    .capacity-info strong { color:var(--ink); }

    @media(max-width:768px){ .rooms-grid { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="page-hero">
  <h1 class="anim-fade-up">Our Spaces</h1>
  <p class="anim-fade-up-2">Find the perfect environment for your work style</p>
</div>

<div class="filter-bar">
  <button class="filter-btn active" data-filter="all" onclick="filterRooms('all')">All Rooms</button>
  <button class="filter-btn" data-filter="Individual" onclick="filterRooms('Individual')">Individual</button>
  <button class="filter-btn" data-filter="Group" onclick="filterRooms('Group')">Group</button>
</div>

<div class="section">
  <?php
  $rooms = DB_CONNECTED
    ? $conn->query("SELECT * FROM rooms WHERE is_active=1 ORDER BY type,id")->fetch_all(MYSQLI_ASSOC)
    : $demo_rooms;

  $configs = [
    1 => ['icon'=>'🤫','bg'=>'linear-gradient(135deg,#fef3e8,#fde8cc)'],
    2 => ['icon'=>'🎮','bg'=>'linear-gradient(135deg,#e8f0fe,#d0e0ff)'],
    3 => ['icon'=>'📊','bg'=>'linear-gradient(135deg,#e8f5e8,#c8eac8)'],
    4 => ['icon'=>'💬','bg'=>'linear-gradient(135deg,#fce8f3,#f8d0e8)'],
  ];
  ?>
  <div class="rooms-grid">
  <?php foreach ($rooms as $r):
    $cfg = $configs[$r['id']] ?? ['icon'=>'🏢','bg'=>'linear-gradient(135deg,#f0ece3,#e4d5c0)'];
    $amenities = explode(',', $r['amenities'] ?? '');
  ?>
    <div class="room-card reveal" data-type="<?= htmlspecialchars($r['type']) ?>">
      <div class="room-card-img" style="background:<?= $cfg['bg'] ?>">
        <div class="room-card-type-badge"><?= htmlspecialchars($r['type']) ?></div>
        <?= $cfg['icon'] ?>
      </div>
      <div class="room-card-body">
        <div class="room-card-header">
          <div class="room-card-name"><?= htmlspecialchars($r['name']) ?></div>
          <div class="room-card-price">EGP <?= number_format($r['base_price'],0) ?><small>/hr</small></div>
        </div>
        <p class="room-card-desc"><?= htmlspecialchars($r['description']) ?></p>
        <div class="amenities-list">
          <?php foreach ($amenities as $a): ?>
            <span class="amenity-tag">✓ <?= htmlspecialchars(trim($a)) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="room-card-footer">
          <div class="capacity-info">👥 Capacity: <strong><?= $r['capacity'] ?> seats</strong></div>
          <a href="booking.php?room_id=<?= $r['id'] ?>" class="btn btn-primary" style="padding:10px 24px;font-size:13px;">Book This Room</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</div>

<?php include 'footer.php'; ?>
<script src="js/main.js"></script>
<script>
  // Pre-filter if URL has type param
  const params = new URLSearchParams(window.location.search);
  const t = params.get('type');
  if (t) filterRooms(t);
</script>
</body>
</html>
