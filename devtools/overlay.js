(function() {
    const overlayData = {
        env: '<?= getenv("APP_ENV") ?>',
        route: '<?= $_SERVER["REQUEST_URI"] ?>',
        user: <?= isset($_SESSION['user']) ? json_encode($_SESSION['user']) : 'null' ?>,
        time: '<?= microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"] ?>',
        memory: '<?= memory_get_usage(true) ?>',
        php_version: '<?= PHP_VERSION ?>'
    };

    const toggleBtn = document.createElement('div');
    toggleBtn.id = 'dev-overlay-toggle';
    toggleBtn.innerText = 'DEV';
    document.body.appendChild(toggleBtn);

    const overlay = document.createElement('div');
    overlay.id = 'dev-overlay';
    overlay.style.display = 'none';
    overlay.innerHTML = `
        <strong>Dev Console</strong><br/>
        <pre>${JSON.stringify(overlayData, null, 2)}</pre>
    `;
    document.body.appendChild(overlay);

    toggleBtn.addEventListener('click', () => {
        overlay.style.display = overlay.style.display === 'none' ? 'block' : 'none';
    });
})();
