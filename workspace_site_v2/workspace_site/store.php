<?php
require_once 'config.php';

$success = $error = '';

// Handle order
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['place_order'])) {
  $cname   = trim($_POST['customer_name']    ?? '');
  $cphone  = trim($_POST['customer_phone']   ?? '');
  $caddr   = trim($_POST['customer_address'] ?? '');
  $paymet  = $_POST['payment_method'] ?? 'Cash';
  $items   = json_decode($_POST['cart_data'] ?? '[]', true);

  if (!$cname || !$cphone) {
    $error = 'Please fill in your name and phone number.';
  } elseif (empty($items)) {
    $error = 'Your cart is empty!';
  } else {
    if (DB_CONNECTED) {
      $total = 0;
      foreach ($items as $item) $total += $item['price'] * $item['qty'];

      $stmt = $conn->prepare("INSERT INTO orders (customer_name,customer_phone,customer_address,payment_method,total) VALUES (?,?,?,?,?)");
      $stmt->bind_param('ssssd', $cname, $cphone, $caddr, $paymet, $total);

      if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        $ok = true;

        foreach ($items as $item) {
          // Check stock
          $res = $conn->query("SELECT stock FROM products WHERE id=".intval($item['id']));
          $row = $res->fetch_assoc();
          if ($row && $row['stock'] >= $item['qty']) {
            $stmt2 = $conn->prepare("INSERT INTO order_items (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)");
            $stmt2->bind_param('iiid', $order_id, $item['id'], $item['qty'], $item['price']);
            $stmt2->execute();
            $conn->query("UPDATE products SET stock=stock-".intval($item['qty'])." WHERE id=".intval($item['id']));
          } else {
            $ok = false;
            $error = 'Sorry, "'.$item['name'].'" is out of stock.';
          }
        }

        if ($ok) {
          $deliveryNote = $caddr
            ? 'Your order will be delivered to <strong>'.htmlspecialchars($caddr).'</strong>.'
            : 'Ready for pickup at reception.';
          $success = "✅ Order placed! Reference: <strong>#ORD-".str_pad($order_id,4,'0',STR_PAD_LEFT)."</strong>. $deliveryNote";
        }
      } else {
        $error = 'Database error: '.$conn->error;
      }
    } else {
      $deliveryNote = $caddr
        ? 'Your order will be delivered to <strong>'.htmlspecialchars($caddr).'</strong>.'
        : 'Ready for pickup at reception.';
      $success = "✅ Order placed! (Demo mode) Reference: <strong>#ORD-DEMO</strong>. $deliveryNote";
    }
  }
}

// Load products
if (DB_CONNECTED) {
  $products = $conn->query("SELECT * FROM products WHERE is_active=1 AND stock>0 ORDER BY category,id")->fetch_all(MYSQLI_ASSOC);
} else {
  $products = $demo_products;
}

$categories = array_unique(array_column($products,'category'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Store — WorkSpace</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .page-hero{background:linear-gradient(135deg,var(--accent2),#1a2530);color:#fff;padding:64px 48px;text-align:center}
    .page-hero h1{font-family:'Playfair Display',serif;font-size:clamp(28px,5vw,48px);margin-bottom:10px}
    .page-hero p{color:rgba(255,255,255,.7)}

    .store-layout{display:grid;grid-template-columns:1fr 300px;gap:40px;max-width:1150px;margin:0 auto;padding:48px}

    .cat-filter{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px}
    .cat-btn{padding:7px 18px;border-radius:18px;border:1.5px solid var(--border);background:transparent;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600;color:var(--muted);transition:all .2s}
    .cat-btn.active,.cat-btn:hover{background:var(--accent);border-color:var(--accent);color:#fff}

    .prod-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .prod-card{background:var(--white);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:transform .25s,box-shadow .25s}
    .prod-card:hover{transform:translateY(-4px);box-shadow:var(--shadow)}
    .prod-img{height:120px;display:flex;align-items:center;justify-content:center;font-size:44px}
    .prod-body{padding:16px}
    .prod-cat{font-size:9px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--accent);margin-bottom:4px}
    .prod-name{font-family:'Playfair Display',serif;font-size:15px;margin-bottom:3px}
    .prod-desc{color:var(--muted);font-size:11px;line-height:1.5;margin-bottom:12px}
    .prod-stock{font-size:10px;color:var(--muted);margin-bottom:8px}
    .prod-footer{display:flex;justify-content:space-between;align-items:center}
    .prod-price{font-size:15px;font-weight:700}
    .add-btn{width:32px;height:32px;border-radius:8px;background:var(--accent);color:#fff;border:none;font-size:18px;cursor:pointer;transition:transform .15s;display:flex;align-items:center;justify-content:center}
    .add-btn:hover{transform:scale(1.12)}

    .prod-icon{
      width:120px;
      height:120px;
      object-fit:contain;
    }

    .cart-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:24px;position:sticky;top:calc(var(--nav-h)+16px);height:fit-content}
    .cart-card h3{font-family:'Playfair Display',serif;font-size:18px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
    #store-cart-badge{background:var(--accent);color:#fff;width:20px;height:20px;border-radius:50%;font-size:10px;font-weight:700;display:none;align-items:center;justify-content:center}
    .cart-items-list{min-height:60px;margin-bottom:16px}
    .cart-empty{text-align:center;color:var(--muted);font-size:13px;padding:16px 0}
    .cart-item{display:flex;gap:10px;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:12px}
    .cart-item-img{width:80px;height:80px;object-fit:contain;flex-shrink:0}
    .cart-item-name{font-weight:600;color:var(--ink);margin-bottom:1px}
    .cart-item-sub{color:var(--muted);font-size:10px}
    .cart-rm{background:none;border:none;color:var(--muted);cursor:pointer;font-size:15px;padding:0 4px}
    .cart-total{display:flex;justify-content:space-between;font-size:14px;font-weight:700;padding:12px 0;border-top:2px solid var(--ink);margin-bottom:16px}

    @media(max-width:900px){.store-layout{grid-template-columns:1fr;padding:24px 20px}.cart-card{position:static}.prod-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:480px){.prod-grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="page-hero">
  <h1 class="anim-fade-up">WorkSpace Store</h1>
  <p class="anim-fade-up-2">Fuel your focus — beverages, stationery, tech & snacks</p>
</div>

<div class="store-layout">
  <!-- PRODUCTS -->
  <div>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

    <div class="cat-filter">
      <button class="cat-btn active" onclick="filterCat('all',this)">All</button>
      <?php foreach ($categories as $cat): ?>
      <button class="cat-btn" onclick="filterCat('<?= htmlspecialchars($cat) ?>',this)"><?= htmlspecialchars($cat) ?></button>
      <?php endforeach; ?>
    </div>

    <?php
    // ربط الصور بـ product ID
    $productImages = [
      1 => 'Latte_and_dark_coffee.jpg',
      2 => 'grean tea.jpg',
      3 => 'note book a5.jpg',
      4 => 'mechanical pencil.jpg',
      5 => 'usb c cable.jpg',
      6 => 'protein bar.jpg'
    ];
    $catIcons  = ['Beverages'=>'beverages','Stationery'=>'stationery','Tech'=>'tech','Snacks'=>'snacks'];
    $catColors = ['Beverages'=>'#fef3e8','Stationery'=>'#e8f0fe','Tech'=>'#e8f5e8','Snacks'=>'#fce8f3'];
    ?>
    <div class="prod-grid">
      <?php foreach ($products as $p):
        $productImage = $productImages[$p['id']] ?? 'default.jpg';
        $color = $catColors[$p['category']] ?? '#f0ece3';
      ?>
      <div class="prod-card reveal" data-cat="<?= htmlspecialchars($p['category']) ?>">
        <div class="prod-img" style="background:<?= $color ?>">
          <img src="img/<?= rawurlencode($productImage) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="prod-icon">
        </div>
        <div class="prod-body">
          <div class="prod-cat"><?= htmlspecialchars($p['category']) ?></div>
          <div class="prod-name"><?= htmlspecialchars($p['name']) ?></div>
          <p class="prod-desc"><?= htmlspecialchars($p['description']) ?></p>
          <div class="prod-stock">In stock: <?= intval($p['stock']) ?></div>
          <div class="prod-footer">
            <div class="prod-price">EGP <?= number_format($p['price'],0) ?></div>
            <button class="add-btn"
              onclick="addToStoreCart(<?= $p['id'] ?>,'<?= htmlspecialchars(addslashes($p['name'])) ?>',<?= $p['price'] ?>,'<?= $productImage ?>')"
              title="Add to cart">+</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- CART -->
  <div class="cart-card">
    <h3>🛒 Cart <span id="store-cart-badge">0</span></h3>

    <div class="cart-items-list" id="cart-list">
      <div class="cart-empty">Your cart is empty</div>
    </div>

    <div class="cart-total">
      <span>Total</span>
      <span id="cart-total-val">EGP 0</span>
    </div>

    <form method="POST" id="order-form">
      <input type="hidden" name="place_order" value="1">
      <input type="hidden" name="cart_data"   id="cart_data_input">

      <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="customer_name" placeholder="Your name" required>
      </div>
      <div class="form-group">
        <label>Phone *</label>
        <input type="tel" name="customer_phone" placeholder="01x xxxx xxxx" required>
      </div>
      <div class="form-group">
        <label>Delivery Address</label>
        <input type="text" name="customer_address" placeholder="Or leave blank for pickup">
      </div>
      <div class="form-group">
        <label>Payment Method *</label>
        <select name="payment_method">
          <option>Cash</option>
          <option>Credit Card</option>
          <option>InstaPay</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%" onclick="prepareOrderData()">
        Place Order →
      </button>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
<script src="js/main.js"></script>
<script>
let storeCart = [];

function addToStoreCart(id, name, price, icon) {
  const ex = storeCart.find(i => i.id===id);
  if (ex) ex.qty++; else storeCart.push({id, name, price, icon, qty:1});
  renderStoreCart();
  showCartToast(`"${name}" added!`);
}

function removeStoreItem(idx) {
  storeCart.splice(idx, 1);
  renderStoreCart();
}

function renderStoreCart() {
  const list  = document.getElementById('cart-list');
  const total = document.getElementById('cart-total-val');
  const badge = document.getElementById('store-cart-badge');

  if (!storeCart.length) {
    list.innerHTML = '<div class="cart-empty">Your cart is empty</div>';
    total.textContent = 'EGP 0';
    badge.style.display = 'none';
    return;
  }

  let sum = 0, html = '';
  storeCart.forEach((item, i) => {
    sum += item.price * item.qty;
    html += `<div class="cart-item">
      <img src="img/${encodeURIComponent(item.icon)}" alt="${item.name}" class="cart-item-img">
      <div>
        <div class="cart-item-name">${item.name}</div>
        <div class="cart-item-sub">EGP ${item.price} × ${item.qty}</div>
      </div>
      <div style="display:flex;gap:6px;align-items:center">
        <strong style="font-size:12px">EGP ${(item.price*item.qty).toFixed(0)}</strong>
        <button class="cart-rm" onclick="removeStoreItem(${i})">×</button>
      </div>
    </div>`;
  });

  list.innerHTML = html;
  total.textContent = 'EGP ' + sum.toFixed(0);

  const qty = storeCart.reduce((s,i) => s+i.qty, 0);
  badge.textContent = qty;
  badge.style.display = qty > 0 ? 'flex' : 'none';
}

function prepareOrderData() {
  document.getElementById('cart_data_input').value = JSON.stringify(storeCart);
}

function filterCat(cat, btn) {
  document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.prod-card').forEach(c => {
    c.style.display = (cat==='all' || c.dataset.cat===cat) ? '' : 'none';
  });
}

function showCartToast(msg) {
  let t = document.getElementById('cart-toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'cart-toast';
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#2c3e50;color:#fff;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:500;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.2)';
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.style.display = 'block';
  clearTimeout(t._t);
  t._t = setTimeout(() => t.style.display='none', 2500);
}
</script>
</body>
</html>
