<?php
require_once 'config.php';

$success = $error = '';

// Load rooms
$rooms = DB_CONNECTED
  ? $conn->query("SELECT * FROM rooms WHERE is_active=1")->fetch_all(MYSQLI_ASSOC)
  : $demo_rooms;

$ind_rooms   = array_filter($rooms, fn($r) => $r['type']==='Individual');
$group_rooms = array_filter($rooms, fn($r) => $r['type']==='Group');

// ── Handle Individual Booking ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form_type']??'')==='individual') {
    $name    = trim($_POST['name']  ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $room_id = intval($_POST['room_id'] ?? 0);
    $start   = $_POST['scheduled_start'] ?? '';
    $notes   = trim($_POST['notes'] ?? '');

    // ===== التحقق من السعة (إضافة جديدة) =====
    if (DB_CONNECTED) {
        $capacity_sql = "SELECT capacity FROM rooms WHERE id = ?";
        $capacity_stmt = $conn->prepare($capacity_sql);
        $capacity_stmt->bind_param('i', $room_id);
        $capacity_stmt->execute();
        $capacity_result = $capacity_stmt->get_result();
        $room_capacity = $capacity_result->fetch_assoc()['capacity'] ?? 0;
        
        $count_sql = "SELECT COUNT(*) as booked_count 
                      FROM individual_bookings 
                      WHERE room_id = ? AND scheduled_start = ? 
                      AND status NOT IN ('Cancelled', 'Completed')";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param('is', $room_id, $start);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $current_booked = $count_result->fetch_assoc()['booked_count'] ?? 0;
        
        if ($current_booked >= $room_capacity) {
            $error = "❌ Sorry, this room is fully booked for the selected time. Please choose another time or room.";
        }
    }
    // ===== نهاية التحقق =====

    if (!$error) {  // لو مفيش خطأ، نكمل
        $room_type = 'Individual';
        
        if (!$name || !$phone || !$room_id || !$start) {
            $error = 'Please fill in all required fields.';
        } else {
            if (DB_CONNECTED) {
                $stmt = $conn->prepare("INSERT INTO individual_bookings (room_id,room_type,customer_name,customer_phone,customer_email,scheduled_start,notes) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param('issssss', $room_id, $room_type, $name, $phone, $email, $start, $notes);
                if ($stmt->execute()) {
                    $bid = $conn->insert_id;
                    $success = "✅ Individual booking confirmed! Reference: <strong>#IND-".str_pad($bid,4,'0',STR_PAD_LEFT)."</strong>";
                } else {
                    $error = 'Database error: '.$conn->error;
                }
            } else {
                $success = "✅ Booking received! (Demo mode) Reference: <strong>#IND-DEMO</strong>";
            }
        }
    }
}

// ── Handle Group Booking ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form_type']??'')==='group') {
    $name       = trim($_POST['name']  ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $room_id    = intval($_POST['room_id'] ?? 0);
    $start      = $_POST['scheduled_start'] ?? '';
    $end        = $_POST['scheduled_end']   ?? '';
    $attendees  = intval($_POST['expected_attendees'] ?? 1);
    $deposit    = floatval($_POST['deposit_amount'] ?? 0);
    $card       = trim($_POST['payment_card'] ?? '');
    $notes      = trim($_POST['notes'] ?? '');
    $room_type  = 'Group';

    // 1. التحقق من البيانات الأساسية
    if (!$name || !$phone || !$room_id || !$start || !$end) {
        $error = 'Please fill in all required fields.';
    } elseif (strtotime($end) <= strtotime($start)) {
        $error = 'End time must be after start time.';
    } else {
        if (DB_CONNECTED) {
            // 2. التحقق من عدم وجود حجز متداخل (double booking)
            $check_sql = "
                SELECT id FROM group_bookings
                WHERE room_id = ?
                  AND status NOT IN ('Cancelled', 'Completed')
                  AND (
                      (scheduled_start < ? AND scheduled_end > ?)  -- الحجز الجديد يبدأ أثناء حجز قديم
                      OR (scheduled_start < ? AND scheduled_end > ?)  -- الحجز الجديد ينتهي أثناء حجز قديم
                      OR (scheduled_start >= ? AND scheduled_end <= ?) -- الحجز الجديد داخل حجز قديم
                  )
                LIMIT 1
            ";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('issssss', $room_id, $end, $start, $end, $start, $start, $end);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error = '❌ Sorry, this room is already booked during that period. Please choose another time.';
            } else {
                // 3. إدراج الحجز الجديد
                $stmt = $conn->prepare("INSERT INTO group_bookings (room_id,room_type,customer_name,customer_phone,customer_email,scheduled_start,scheduled_end,expected_attendees,deposit_amount,payment_card,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param('issssssiids', $room_id, $room_type, $name, $phone, $email, $start, $end, $attendees, $deposit, $card, $notes);
                if ($stmt->execute()) {
                    $bid = $conn->insert_id;
                    $success = "✅ Group booking confirmed! Reference: <strong>#GRP-".str_pad($bid,4,'0',STR_PAD_LEFT)."</strong>. Deposit of EGP ".number_format($deposit,2)." will be collected on arrival.";
                } else {
                    $error = 'Database error: '.$conn->error;
                }
            }
        } else {
            $success = "✅ Group booking received! (Demo mode) Reference: <strong>#GRP-DEMO</strong>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Book Now — WorkSpace</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .page-hero{background:linear-gradient(135deg,var(--accent),#d4892b);color:#fff;padding:64px 48px;text-align:center}
    .page-hero h1{font-family:'Playfair Display',serif;font-size:clamp(28px,5vw,48px);margin-bottom:10px}
    .page-hero p{color:rgba(255,255,255,.85);font-size:15px}

    .tabs{display:flex;gap:0;max-width:900px;margin:40px auto 0;padding:0 48px}
    .tab-btn{flex:1;padding:14px;border:2px solid var(--border);background:var(--white);font-family:'DM Sans',sans-serif;font-size:14px;font-weight:600;cursor:pointer;color:var(--muted);transition:all .2s}
    .tab-btn:first-child{border-radius:10px 0 0 10px}
    .tab-btn:last-child{border-radius:0 10px 10px 0;border-left:none}
    .tab-btn.active{background:var(--accent);border-color:var(--accent);color:#fff}

    .booking-layout{display:grid;grid-template-columns:1.4fr 1fr;gap:40px;max-width:900px;margin:0 auto;padding:32px 48px 64px}

    .tab-content{display:none}
    .tab-content.active{display:block}

    .form-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:36px}
    .form-card h2{font-family:'Playfair Display',serif;font-size:22px;margin-bottom:24px;color:var(--ink)}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}

    .room-sel{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:0}
    .room-opt{padding:14px;border:2px solid var(--border);border-radius:10px;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:12px;text-align:left;background:transparent;transition:all .2s;width:100%}
    .room-opt:hover{border-color:var(--accent)}
    .room-opt.sel{border-color:var(--accent);background:#fef3e8}
    .room-opt-name{font-weight:700;color:var(--ink);margin-bottom:2px;font-size:13px}
    .room-opt-price{color:var(--accent);font-weight:600;font-size:11px}
    .room-opt-cap{color:var(--muted);font-size:10px}

    .info-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:16px}
    .info-card h3{font-family:'Playfair Display',serif;font-size:17px;margin-bottom:16px}
    .step{display:flex;gap:12px;align-items:flex-start;margin-bottom:14px}
    .step-n{width:28px;height:28px;border-radius:50%;background:var(--accent);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .step-t strong{display:block;font-size:13px;margin-bottom:2px}
    .step-t span{color:var(--muted);font-size:12px}
    .policy{font-size:12px;color:var(--muted);padding:6px 0;border-bottom:1px solid var(--border);display:flex;gap:8px}
    .policy::before{content:'•';color:var(--accent);flex-shrink:0}

    .deposit-preview{background:#fef3e8;border:1px solid #f5d9b8;border-radius:8px;padding:14px;font-size:13px;color:var(--accent);margin-top:8px;display:none;font-weight:500}

    @media(max-width:768px){
      .booking-layout{grid-template-columns:1fr;padding:24px 20px}
      .form-row,.room-sel{grid-template-columns:1fr}
      .tabs{padding:0 20px}
    }
  </style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="page-hero">
  <h1 class="anim-fade-up">Reserve Your Space</h1>
  <p class="anim-fade-up-2">Individual desk or group room — book in minutes</p>
</div>

<!-- TABS -->
<div class="tabs">
  <button class="tab-btn active" onclick="switchTab('individual',this)">👤 Individual Booking</button>
  <button class="tab-btn" onclick="switchTab('group',this)">👥 Group Booking</button>
</div>

<div class="booking-layout">

  <!-- ── INDIVIDUAL FORM ── -->
  <div class="tab-content active" id="tab-individual">
    <div class="form-card">
      <h2>👤 Individual Booking</h2>

      <?php if ($success && str_contains($success,'IND')): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>
      <?php if ($error && ($_POST['form_type']??'')==='individual'): ?>
        <div class="alert alert-error"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="form_type" value="individual">

        <div class="form-group">
          <label>Select Individual Room *</label>
          <div class="room-sel">
            <?php foreach ($ind_rooms as $r): ?>
            <button type="button" class="room-opt" data-id="<?= $r['id'] ?>" onclick="pickRoom(this,'ind_room_id')">
              <div class="room-opt-name"><?= htmlspecialchars($r['name']) ?></div>
              <div class="room-opt-price">EGP <?= number_format($r['base_price'],0) ?>/hr</div>
              <div class="room-opt-cap">👥 <?= $r['capacity'] ?> seats</div>
            </button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="room_id" id="ind_room_id" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" placeholder="Your full name" required value="<?= ($_POST['form_type']??'')==='individual' ? htmlspecialchars($_POST['name']??'') : '' ?>">
          </div>
          <div class="form-group">
            <label>Phone *</label>
            <input type="tel" name="phone" placeholder="01x xxxx xxxx" required value="<?= ($_POST['form_type']??'')==='individual' ? htmlspecialchars($_POST['phone']??'') : '' ?>">
          </div>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="you@email.com" value="<?= ($_POST['form_type']??'')==='individual' ? htmlspecialchars($_POST['email']??'') : '' ?>">
        </div>

        <div class="form-group">
          <label>Check-In Date & Time *</label>
          <input type="datetime-local" name="scheduled_start" required min="<?= date('Y-m-d\TH:i') ?>" value="<?= ($_POST['form_type']??'')==='individual' ? htmlspecialchars($_POST['scheduled_start']??'') : '' ?>">
        </div>

        <div class="form-group">
          <label>Notes</label>
          <textarea name="notes" placeholder="Any special requirements..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;font-size:15px;padding:15px">Confirm Booking →</button>
      </form>
    </div>
  </div>

  <!-- ── GROUP FORM ── -->
  <div class="tab-content" id="tab-group">
    <div class="form-card">
      <h2>👥 Group Booking</h2>

      <?php if ($success && str_contains($success,'GRP')): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>
      <?php if ($error && ($_POST['form_type']??'')==='group'): ?>
        <div class="alert alert-error"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="form_type" value="group">

        <div class="form-group">
          <label>Select Group Room *</label>
          <div class="room-sel">
            <?php foreach ($group_rooms as $r): ?>
            <button type="button" class="room-opt" data-id="<?= $r['id'] ?>" data-price="<?= $r['base_price'] ?>" onclick="pickRoom(this,'grp_room_id'); calcDeposit();">
              <div class="room-opt-name"><?= htmlspecialchars($r['name']) ?></div>
              <div class="room-opt-price">EGP <?= number_format($r['base_price'],0) ?>/hr</div>
              <div class="room-opt-cap">👥 <?= $r['capacity'] ?> seats</div>
            </button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="room_id" id="grp_room_id" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="name" placeholder="Your full name" required value="<?= ($_POST['form_type']??'')==='group' ? htmlspecialchars($_POST['name']??'') : '' ?>">
          </div>
          <div class="form-group">
            <label>Phone *</label>
            <input type="tel" name="phone" placeholder="01x xxxx xxxx" required value="<?= ($_POST['form_type']??'')==='group' ? htmlspecialchars($_POST['phone']??'') : '' ?>">
          </div>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="you@email.com">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Start Date & Time *</label>
            <input type="datetime-local" name="scheduled_start" id="grp_start" required min="<?= date('Y-m-d\TH:i') ?>" onchange="calcDeposit()">
          </div>
          <div class="form-group">
            <label>End Date & Time *</label>
            <input type="datetime-local" name="scheduled_end" id="grp_end" required onchange="calcDeposit()">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Expected Attendees *</label>
            <input type="number" name="expected_attendees" placeholder="e.g. 6" min="1" max="50" required value="<?= ($_POST['form_type']??'')==='group' ? intval($_POST['expected_attendees']??1) : '' ?>">
          </div>
          <div class="form-group">
            <label>Payment Card / Reference</label>
            <input type="text" name="payment_card" placeholder="Card last 4 digits or InstaPay ID">
          </div>
        </div>

        <div class="form-group">
          <label>Deposit Amount (EGP) *</label>
          <input type="number" name="deposit_amount" id="deposit_amt" placeholder="Auto-calculated (25%)" step="0.01" min="0" required value="<?= ($_POST['form_type']??'')==='group' ? floatval($_POST['deposit_amount']??0) : '' ?>">
          <div class="deposit-preview" id="deposit-preview"></div>
        </div>

        <div class="form-group">
          <label>Notes</label>
          <textarea name="notes" placeholder="Any special requirements or setup needed..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;font-size:15px;padding:15px">Confirm Group Booking →</button>
      </form>
    </div>
  </div>

  <!-- SIDEBAR -->
  <div>
    <div class="info-card">
      <h3>How It Works</h3>
      <div class="step"><div class="step-n">1</div><div class="step-t"><strong>Choose Room</strong><span>Pick the space that fits your needs.</span></div></div>
      <div class="step"><div class="step-n">2</div><div class="step-t"><strong>Fill Details</strong><span>Name, phone and booking time.</span></div></div>
      <div class="step"><div class="step-n">3</div><div class="step-t"><strong>Confirm</strong><span>Group rooms need 25% deposit.</span></div></div>
      <div class="step"><div class="step-n">4</div><div class="step-t"><strong>Show Up!</strong><span>Present your reference ID at reception.</span></div></div>
    </div>
    <div class="info-card">
      <h3>Booking Policies</h3>
      <div class="policy">Individual: check in within grace period after booking.</div>
      <div class="policy">Group: 25% deposit required on arrival to confirm.</div>
      <div class="policy">Cancel 24hrs+ before: full deposit refund.</div>
      <div class="policy">No-show or late cancel: deposit forfeited.</div>
      <div class="policy">Payment: Cash, Credit Card, InstaPay.</div>
    </div>
  </div>

</div>

<?php include 'footer.php'; ?>
<script src="js/main.js"></script>
<script>
function switchTab(tab, btn) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  btn.classList.add('active');
}

function pickRoom(el, inputId) {
  el.closest('.room-sel').querySelectorAll('.room-opt').forEach(r => r.classList.remove('sel'));
  el.classList.add('sel');
  document.getElementById(inputId).value = el.dataset.id;
}

function calcDeposit() {
  const start = document.getElementById('grp_start')?.value;
  const end   = document.getElementById('grp_end')?.value;
  const roomBtn = document.querySelector('#tab-group .room-opt.sel');
  const preview = document.getElementById('deposit-preview');
  const amtInput = document.getElementById('deposit_amt');

  if (!start || !end || !roomBtn) return;

  const price = parseFloat(roomBtn.dataset.price || 0);
  const hours = (new Date(end) - new Date(start)) / 3600000;
  if (hours <= 0) return;

  const total   = price * hours;
  const deposit = total * 0.25;

  amtInput.value = deposit.toFixed(2);
  preview.style.display = 'block';
  preview.textContent = `Total: EGP ${total.toFixed(2)} for ${hours}hr(s) → Deposit (25%): EGP ${deposit.toFixed(2)}`;
}

// Auto-open group tab if form was submitted as group
<?php if (($_POST['form_type']??'')==='group'): ?>
document.addEventListener('DOMContentLoaded', () => {
  switchTab('group', document.querySelectorAll('.tab-btn')[1]);
});
<?php endif; ?>
</script>
</body>
</html>