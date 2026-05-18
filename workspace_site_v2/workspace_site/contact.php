<?php
require_once 'config.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name    = trim($_POST['name']    ?? '');
  $email   = trim($_POST['email']   ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if (!$name || !$email || !$message) {
    $error = 'Please fill in all required fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
  } else {
    if (DB_CONNECTED) {
      $stmt = $conn->prepare("INSERT INTO contacts (name,email,subject,message) VALUES (?,?,?,?)");
      $stmt->bind_param('ssss', $name, $email, $subject, $message);
      if ($stmt->execute()) {
        $success = "Thank you, <strong>$name</strong>! Your message has been received. We'll get back to you within 24 hours.";
      } else {
        $error = 'Something went wrong. Please try again.';
      }
    } else {
      $success = "Message received! (Demo mode) Thank you, <strong>$name</strong>! We'll be in touch soon.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Contact — WorkSpace</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .page-hero {
      background: linear-gradient(135deg, #f0ece3 0%, #e4d5c0 100%);
      padding: 72px 48px; text-align:center;
    }
    .page-hero h1 {
      font-family:'Playfair Display',serif;
      font-size:clamp(32px,5vw,52px); color:var(--ink); margin-bottom:12px;
    }
    .page-hero p { color:var(--muted); }

    .contact-layout {
      display:grid; grid-template-columns:1fr 1.4fr; gap:64px;
      max-width:1100px; margin:0 auto; padding:72px 48px;
    }

    /* Info side */
    .contact-info h2 {
      font-family:'Playfair Display',serif; font-size:28px; margin-bottom:16px;
    }
    .contact-info > p { color:var(--muted); font-size:15px; line-height:1.7; margin-bottom:40px; }

    .info-item {
      display:flex; gap:16px; align-items:flex-start; margin-bottom:28px;
    }
    .info-icon {
      width:48px; height:48px; border-radius:12px;
      background:linear-gradient(135deg,var(--accent),#d4892b);
      display:flex; align-items:center; justify-content:center;
      font-size:20px; flex-shrink:0;
      animation:float 5s ease-in-out infinite;
    }
    .info-label { font-size:11px; font-weight:700; letter-spacing:2px;
      text-transform:uppercase; color:var(--accent); margin-bottom:4px; }
    .info-value { font-size:15px; color:var(--ink); font-weight:500; }
    .info-sub   { font-size:13px; color:var(--muted); }

    .hours-grid {
      background:var(--white); border:1px solid var(--border);
      border-radius:14px; padding:24px; margin-top:32px;
    }
    .hours-grid h4 { font-family:'Playfair Display',serif; font-size:16px; margin-bottom:16px; }
    .hours-row {
      display:flex; justify-content:space-between;
      font-size:13px; padding:8px 0;
      border-bottom:1px solid var(--border);
    }
    .hours-row:last-child { border-bottom:none; }
    .hours-row .day { color:var(--muted); }
    .hours-row .time { font-weight:600; color:var(--ink); }

    /* Form side */
    .contact-form-card {
      background:var(--white); border:1px solid var(--border);
      border-radius:16px; padding:40px;
    }
    .contact-form-card h2 {
      font-family:'Playfair Display',serif; font-size:24px; margin-bottom:24px;
    }

    .subject-chips { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:0; }
    .subject-chip {
      padding:7px 16px; border-radius:20px;
      border:1.5px solid var(--border);
      background:transparent; cursor:pointer;
      font-size:12px; font-weight:600; color:var(--muted);
      font-family:'DM Sans',sans-serif; transition:all 0.2s;
    }
    .subject-chip.selected, .subject-chip:hover {
      background:var(--accent); border-color:var(--accent); color:white;
    }

    /* Map placeholder */
    .map-placeholder {
      background:linear-gradient(135deg,#e8e2d6,#d4c9b8);
      border-radius:16px; height:200px;
      display:flex; align-items:center; justify-content:center;
      margin-top:32px; overflow:hidden; position:relative;
    }
    .map-placeholder::before {
      content:''; position:absolute; inset:0;
      background: repeating-linear-gradient(
        0deg, transparent, transparent 20px, rgba(0,0,0,0.04) 20px, rgba(0,0,0,0.04) 21px
      ),
      repeating-linear-gradient(
        90deg, transparent, transparent 20px, rgba(0,0,0,0.04) 20px, rgba(0,0,0,0.04) 21px
      );
    }
    .map-pin { font-size:40px; animation:float 3s ease-in-out infinite; z-index:1; }
    .map-label {
      position:absolute; bottom:20px; left:50%; transform:translateX(-50%);
      background:white; padding:8px 20px; border-radius:20px;
      font-size:13px; font-weight:600; color:var(--ink);
      box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:1;
    }

    @media(max-width:768px){
      .contact-layout { grid-template-columns:1fr; padding:40px 20px; }
    }
  </style>
</head>
<body>
<?php include 'nav.php'; ?>

<!-- ANCHOR: contact -->
<a name="contact"></a>
<div class="page-hero">
  <h1 class="anim-fade-up">Get in Touch</h1>
  <p class="anim-fade-up-2">We'd love to hear from you — questions, tours, or bookings</p>
</div>

<div class="contact-layout">
  <!-- INFO -->
  <div>
    <h2 class="anim-fade-up">Visit Us or Drop a Message</h2>
    <p class="anim-fade-up-2">Our team is available 7 days a week to help you find the perfect space for your needs.</p>

    <div class="info-item reveal">
      <div class="info-icon">📍</div>
      <div>
        <div class="info-label">Location</div>
        <div class="info-value">123 El-Horreya Road</div>
        <div class="info-sub">Smouha, Alexandria, Egypt</div>
      </div>
    </div>

    <div class="info-item reveal">
      <div class="info-icon">📞</div>
      <div>
        <div class="info-label">Phone</div>
        <div class="info-value">+20 3 XXX XXXX</div>
        <div class="info-sub">Sun–Thu: 8am–10pm</div>
      </div>
    </div>

    <div class="info-item reveal">
      <div class="info-icon">✉️</div>
      <div>
        <div class="info-label">Email</div>
        <div class="info-value">hello@workspace.eg</div>
        <div class="info-sub">We reply within 24 hours</div>
      </div>
    </div>

    <div class="hours-grid reveal">
      <h4>Opening Hours</h4>
      <div class="hours-row"><span class="day">Sunday – Thursday</span><span class="time">8:00 AM – 11:00 PM</span></div>
      <div class="hours-row"><span class="day">Friday</span><span class="time">10:00 AM – 8:00 PM</span></div>
      <div class="hours-row"><span class="day">Saturday</span><span class="time">9:00 AM – 10:00 PM</span></div>
    </div>

    <div class="map-placeholder reveal">
      <div class="map-pin">📌</div>
      <div class="map-label">WorkSpace · Smouha, Alexandria</div>
    </div>
  </div>

  <!-- FORM -->
  <div class="contact-form-card anim-fade-up">
    <h2>Send Us a Message</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error">❌ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="contact-form" onsubmit="return validateForm('contact-form')">

      <div class="form-group">
        <label>What's this about?</label>
        <div class="subject-chips">
          <?php
          $subjects = ['General Inquiry','Room Tour','Group Booking','Technical Issue','Partnership'];
          foreach ($subjects as $s):
          ?>
          <button type="button" class="subject-chip"
            onclick="setSubject('<?= $s ?>', this)"><?= $s ?></button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="subject" id="subject_input">
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="name" placeholder="Your name" required value="<?= htmlspecialchars($_POST['name']??'') ?>">
        </div>
        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" placeholder="you@email.com" required value="<?= htmlspecialchars($_POST['email']??'') ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Message *</label>
        <textarea name="message" placeholder="Tell us what you need..." rows="6" required><?= htmlspecialchars($_POST['message']??'') ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; font-size:16px; padding:16px;">
        Send Message →
      </button>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
<script src="js/main.js"></script>
<script>
function setSubject(subject, btn) {
  document.querySelectorAll('.subject-chip').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('subject_input').value = subject;
}
</script>
</body>
</html>
