<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>WorkSpace — Your Productive Escape</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* ── HERO ── */
    .hero {
      min-height: calc(100vh - var(--nav-h));
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, #f7f5f1 0%, #ede8df 60%, #e4d5c0 100%);
      padding: 80px 48px;
    }
    .hero-content { max-width: 600px; position: relative; z-index: 2; }
    .hero-eyebrow {
      font-size: 11px; font-weight: 600; letter-spacing: 4px;
      text-transform: uppercase; color: var(--accent); margin-bottom: 20px;
      display: flex; align-items: center; gap: 10px;
    }
    .hero-eyebrow::before {
      content: ''; display: block; width: 32px; height: 2px; background: var(--accent);
    }
    .hero-title {
      font-family: 'Playfair Display', serif;
      font-size: clamp(42px, 6vw, 72px);
      font-weight: 700; line-height: 1.1;
      color: var(--ink); margin-bottom: 24px;
    }
    .hero-title em { color: var(--accent); font-style: italic; }
    .hero-sub {
      font-size: 17px; color: var(--muted); line-height: 1.7;
      margin-bottom: 40px; max-width: 480px;
    }
    .hero-btns { display: flex; gap: 16px; flex-wrap: wrap; }

    /* Floating shapes */
    .hero-shape {
      position: absolute; border-radius: 50%;
      opacity: 0.12; animation: float 6s ease-in-out infinite;
    }
    .shape-1 { width:320px; height:320px; background:var(--accent); top:-80px; right:10%; animation-delay:0s; }
    .shape-2 { width:180px; height:180px; background:var(--accent2); bottom:10%; right:25%; animation-delay:2s; }
    .shape-3 { width:80px;  height:80px;  background:var(--accent); bottom:20%; right:8%; animation-delay:4s; }

    /* Date bar */
    .date-bar {
      background: var(--accent2);
      color: rgba(255,255,255,0.9);
      text-align: center;
      padding: 14px 48px;
      font-size: 13px;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    .date-bar span { color: var(--accent); font-weight: 700; }

    /* Stats */
    .stats-section {
      background: var(--white);
      border-bottom: 1px solid var(--border);
    }
    .stats-grid {
      max-width: 1200px; margin: 0 auto;
      display: grid; grid-template-columns: repeat(4,1fr);
      padding: 48px;
    }
    .stat-item {
      text-align: center;
      padding: 24px;
      border-right: 1px solid var(--border);
      animation: countUp 0.6s ease both;
    }
    .stat-item:last-child { border-right: none; }
    .stat-number {
      font-family: 'Playfair Display', serif;
      font-size: 48px; font-weight: 700;
      color: var(--accent); line-height: 1;
    }
    .stat-label { color: var(--muted); font-size: 13px; margin-top: 8px; font-weight: 500; }

    /* Features */
    .features-grid {
      display: grid; grid-template-columns: repeat(3,1fr); gap: 32px;
    }
    .feature-card {
      padding: 36px 32px;
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      transition: transform 0.25s, box-shadow 0.25s;
    }
    .feature-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); }
    .feature-icon {
      width: 52px; height: 52px;
      background: linear-gradient(135deg, var(--accent) 0%, #d4892b 100%);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; margin-bottom: 20px;
      animation: float 5s ease-in-out infinite;
    }
    .feature-title { font-family:'Playfair Display',serif; font-size:19px; margin-bottom:10px; }
    .feature-desc { color: var(--muted); font-size:14px; line-height:1.6; }

    /* Rooms preview */
    .rooms-preview { background: var(--white); }
    .room-prev-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 24px; }
    .room-prev-card {
      border-radius: var(--radius); overflow: hidden;
      border: 1px solid var(--border);
      display: flex; transition: transform 0.25s, box-shadow 0.25s;
    }
    .room-prev-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
    .room-prev-img {
      width: 140px; min-height: 140px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      font-size: 40px;
    }
    .room-prev-body { padding: 24px; }
    .room-prev-name { font-family:'Playfair Display',serif; font-size:18px; font-weight:600; margin-bottom:6px; }
    .room-prev-price { color:var(--accent); font-weight:700; font-size:15px; margin-bottom:8px; }
    .room-prev-desc { color:var(--muted); font-size:13px; line-height:1.5; }

    /* CTA Banner */
    .cta-banner {
      background: linear-gradient(135deg, var(--accent2) 0%, #1a2530 100%);
      color: white;
      padding: 80px 48px;
      text-align: center;
    }
    .cta-banner h2 {
      font-family:'Playfair Display',serif;
      font-size: clamp(28px,4vw,44px);
      margin-bottom: 16px;
    }
    .cta-banner p { color:rgba(255,255,255,0.7); margin-bottom:36px; font-size:16px; }

    @media(max-width:768px){
      .stats-grid { grid-template-columns: repeat(2,1fr); }
      .features-grid { grid-template-columns: 1fr; }
      .room-prev-grid { grid-template-columns: 1fr; }
      .hero { padding: 48px 20px; }
    }
  </style>
</head>
<body>
<?php include 'nav.php'; ?>

<!-- DATE BAR -->
<div class="date-bar">
  📅 Today: <span id="live-clock">Loading...</span>
</div>

<!-- HERO -->
<section class="hero" id="home">
  <div class="shape-1 hero-shape"></div>
  <div class="shape-2 hero-shape"></div>
  <div class="shape-3 hero-shape"></div>
  <div class="hero-content">
    <div class="hero-eyebrow anim-fade-up">Premium Co-Working Space</div>
    <h1 class="hero-title anim-fade-up-2">
      Where Ideas <em>Come to Life</em>
    </h1>
    <p class="hero-sub anim-fade-up-3">
      Flexible individual desks and fully-equipped group rooms designed for focus, collaboration, and creativity — book by the hour, anytime.
    </p>
    <div class="hero-btns anim-fade-in">
      <a href="booking.php" class="btn btn-primary">Reserve a Space</a>
      <a href="rooms.php"   class="btn btn-outline">Explore Rooms</a>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="stats-section">
  <div class="stats-grid">
    <div class="stat-item reveal">
      <div class="stat-number counter" data-target="4" data-suffix="">4</div>
      <div class="stat-label">Unique Room Types</div>
    </div>
    <div class="stat-item reveal">
      <div class="stat-number counter" data-target="38" data-suffix="+">38+</div>
      <div class="stat-label">Total Seats</div>
    </div>
    <div class="stat-item reveal">
      <div class="stat-number counter" data-target="24" data-suffix="/7">24/7</div>
      <div class="stat-label">Access Available</div>
    </div>
    <div class="stat-item reveal">
      <div class="stat-number counter" data-target="500" data-suffix="+">500+</div>
      <div class="stat-label">Happy Members</div>
    </div>
  </div>
</div>

<!-- FEATURES -->
<section class="section">
  <div class="section-label">Why WorkSpace</div>
  <h2 class="section-title">Everything You Need to Do Your Best Work</h2>
  <p class="section-sub">From quiet individual desks to energized meeting rooms — we have a space for every kind of work.</p>

  <div class="features-grid">
    <div class="feature-card reveal">
      <div class="feature-icon">⚡</div>
      <div class="feature-title">Book by the Hour</div>
      <p class="feature-desc">No long-term commitment. Reserve a desk or room for exactly as long as you need it — pay only for what you use.</p>
    </div>
    <div class="feature-card reveal">
      <div class="feature-icon">🌐</div>
      <div class="feature-title">High-Speed Internet</div>
      <p class="feature-desc">Ultra-fast fiber Wi-Fi throughout all spaces. Stay connected and productive without interruptions.</p>
    </div>
    <div class="feature-card reveal">
      <div class="feature-icon">☕</div>
      <div class="feature-title">In-House Store</div>
      <p class="feature-desc">Grab a coffee, snack, or stationery from our on-site store — everything you need within arm's reach.</p>
    </div>
    <div class="feature-card reveal">
      <div class="feature-icon">🔒</div>
      <div class="feature-title">Secure & Private</div>
      <p class="feature-desc">Controlled access, CCTV monitoring, and private booths ensure your work stays confidential.</p>
    </div>
    <div class="feature-card reveal">
      <div class="feature-icon">🎮</div>
      <div class="feature-title">Gaming Breaks</div>
      <p class="feature-desc">Recharge with our Gaming Room featuring PlayStation consoles and LED lighting — because rest is productive too.</p>
    </div>
    <div class="feature-card reveal">
      <div class="feature-icon">📋</div>
      <div class="feature-title">Meeting Ready</div>
      <p class="feature-desc">Fully equipped meeting rooms with projectors, printers, and whiteboards — ready for your next big presentation.</p>
    </div>
  </div>
</section>

<!-- ROOMS PREVIEW -->
<section class="rooms-preview">
  <div class="section">
    <div class="section-label">Our Spaces</div>
    <h2 class="section-title">Choose Your Ideal Environment</h2>
    <p class="section-sub">Four carefully designed spaces to match every working style.</p>

    <div class="room-prev-grid">
      <?php
      $rooms = DB_CONNECTED
        ? $conn->query("SELECT * FROM rooms WHERE is_active=1 LIMIT 4")->fetch_all(MYSQLI_ASSOC)
        : $demo_rooms;
      $icons = ['🤫','🎮','📊','💬'];
      $colors = ['#fef3e8','#e8f0fe','#e8f5e8','#fce8f3'];
      foreach ($rooms as $i => $r):
      ?>
      <div class="room-prev-card reveal">
        <div class="room-prev-img" style="background:<?= $colors[$i%4] ?>">
          <?= $icons[$i%4] ?>
        </div>
        <div class="room-prev-body">
          <div class="card-tag"><?= htmlspecialchars($r['type']) ?></div>
          <div class="room-prev-name"><?= htmlspecialchars($r['name']) ?></div>
          <div class="room-prev-price">EGP <?= number_format($r['base_price'],2) ?>/hr</div>
          <p class="room-prev-desc"><?= htmlspecialchars(substr($r['description'],0,90)) ?>...</p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center; margin-top:40px;">
      <a href="rooms.php" class="btn btn-primary">View All Rooms</a>
    </div>
  </div>
</section>

<!-- ANCHOR: about section -->
<a name="about"></a>
<section class="section reveal">
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:64px; align-items:center;">
    <div>
      <div class="section-label">Our Story</div>
      <h2 class="section-title">A Space Built for Modern Professionals</h2>
      <p style="color:var(--muted); line-height:1.8; margin-bottom:20px;">
        WorkSpace was founded with a simple belief: great work happens in great environments. We designed every corner of our space with intention — from the acoustics in our quiet rooms to the lighting temperature in our creative zones.
      </p>
      <p style="color:var(--muted); line-height:1.8; margin-bottom:32px;">
        Whether you're a freelancer, a startup team, or a corporate group on retreat — we have a space that fits your workflow.
      </p>
      <a href="contact.php" class="btn btn-dark">Get in Touch</a>
    </div>
    <div style="background:linear-gradient(135deg,#f0ece3,#e4d5c0); border-radius:20px; padding:48px; text-align:center; animation:float 7s ease-in-out infinite;">
      <div style="font-size:80px; margin-bottom:16px;">🏢</div>
      <div style="font-family:'Playfair Display',serif; font-size:22px; color:var(--ink); margin-bottom:8px;">Est. 2024</div>
      <div style="color:var(--muted); font-size:14px;">Alexandria, Egypt</div>
    </div>
  </div>
</section>

<!-- CTA BANNER -->
<div class="cta-banner">
  <h2 class="reveal">Ready to Find Your Focus?</h2>
  <p class="reveal">Reserve your space in under 2 minutes. No membership required.</p>
  <a href="booking.php" class="btn btn-primary">Book Now →</a>
</div>

<?php include 'footer.php'; ?>
<script src="js/main.js"></script>
</body>
</html>
