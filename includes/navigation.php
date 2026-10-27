<?php $navPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php'); ?>
<header class="site-header">
  <div class="shell nav-wrap">
    <a class="brand" href="index.php"><img src="brand/rent-a-bau-logo.png" alt="Rent A Bau"></a>
    <button class="mobile-menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu-panel">
      <span class="sr-only">Menü öffnen</span><span aria-hidden="true">☰</span>
    </button>
    <div class="nav-menu-content" id="mobile-menu-panel">
      <nav aria-label="Hauptnavigation">
        <a href="index.php"<?= $navPage === 'index.php' ? ' aria-current="page"' : '' ?>>Startseite</a>
        <a href="maschinen.php"<?= in_array($navPage, ['maschinen.php', 'kategorie.php', 'maschine-detail.php', 'bagger.php', 'fahrzeuge.php', 'kleinmaschinen.php', 'abbruch.php'], true) ? ' aria-current="page"' : '' ?>>Maschinen</a>
        <a href="so-funktionierts.php"<?= $navPage === 'so-funktionierts.php' ? ' aria-current="page"' : '' ?>>So funktioniert's</a>
        <a href="kontakt.php"<?= $navPage === 'kontakt.php' ? ' aria-current="page"' : '' ?>>Kontakt &amp; Lieferung</a>
      </nav>
    <?php $navUser = currentUser(); ?>
    <div class="nav-actions">
      <?php if ($navUser): ?>
        <a href="<?= ($navUser['role'] ?? '') === 'admin' ? 'admin.php' : 'konto.php' ?>"><?= e($navUser['name'] ?? 'Konto') ?></a>
        <a href="logout.php">Abmelden</a>
      <?php else: ?>
        <a href="konto.php?login=1">Anmelden</a>
      <?php endif; ?>
      <?php if (($navUser['role'] ?? '') === 'admin'): ?><a class="button button-small button-dark" href="admin.php">Admin</a><?php endif; ?>
    </div>
    </div>
  </div>
</header>
<script>
(() => {
  const toggle = document.querySelector('.mobile-menu-toggle');
  const panel = document.getElementById('mobile-menu-panel');
  if (!toggle || !panel) return;
  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!open));
    toggle.querySelector('.sr-only').textContent = open ? 'Menü öffnen' : 'Menü schliessen';
    panel.classList.toggle('is-open', !open);
  });
})();
</script>
