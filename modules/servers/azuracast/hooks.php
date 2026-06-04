<?php
if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

add_hook('ClientAreaHeadOutput', 1, function (array $vars): string {
    // Only inject on the product details page
    if (($vars['templatefile'] ?? '') !== 'clientareaproductdetails') {
        return '';
    }

    // Guard: only inject for services using the azuracast module
    $serviceId = (int) ($_GET['id'] ?? 0);
    if ($serviceId <= 0) {
        return '';
    }

    try {
        $serverType = \WHMCS\Database\Capsule::table('tblhosting')
            ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
            ->where('tblhosting.id', $serviceId)
            ->value('tblproducts.servertype');
    } catch (\Throwable $e) {
        return '';
    }

    if ($serverType !== 'azuracast') {
        return '';
    }

    return <<<'JS'
<script>
(function () {
    function injectEnterPanelButton() {
        if (document.getElementById('az-enter-panel-btn')) return;

        var upgradeBtn = document.querySelector('.btn-warning[href*="upgrade.php"]');
        if (!upgradeBtn) return;

        var urlParams = new URLSearchParams(window.location.search);
        var serviceId = urlParams.get('id');
        if (!serviceId) return;

        var ssoUrl = '/clientarea.php?action=productdetails&id=' + serviceId + '&dosinglesignon=1';

        var btn = document.createElement('a');
        btn.id = 'az-enter-panel-btn';
        btn.href = ssoUrl;
        btn.target = '_blank';
        btn.rel = 'noopener noreferrer';
        btn.className = 'btn btn-primary';
        btn.style.marginLeft = '8px';
        btn.innerHTML = '<i class="fas fa-sign-in-alt fa-fw"></i> Entrar no Painel';

        upgradeBtn.parentNode.insertBefore(btn, upgradeBtn.nextSibling);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectEnterPanelButton);
    } else {
        injectEnterPanelButton();
    }
})();
</script>
JS;
});