<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<nav>
  <a class="nav-logo" href="index.php">Work<span>Space</span></a>
  <ul class="nav-menu">
    <li><a href="index.php"    <?= $current==='index.php'    ? 'class="active"' : '' ?>>Home</a></li>
    <li>
      <a href="rooms.php"      <?= $current==='rooms.php'    ? 'class="active"' : '' ?>>Rooms ▾</a>
      <div class="dropdown">
        <a href="rooms.php?type=Individual">Individual Rooms</a>
        <a href="rooms.php?type=Group">Group Rooms</a>
        <a href="rooms.php">All Rooms</a>
      </div>
    </li>
    <li><a href="store.php"    <?= $current==='store.php'    ? 'class="active"' : '' ?>>Store</a></li>
    <li><a href="contact.php"  <?= $current==='contact.php'  ? 'class="active"' : '' ?>>Contact</a></li>
    <li>
      <a href="booking.php" class="nav-cta <?= $current==='booking.php' ? 'active' : '' ?>">Book Now</a>
    </li>
  </ul>
</nav>
