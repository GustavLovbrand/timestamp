<?php
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
?>
<div class="box">
<h1>API Dokumentation</h1>
<p>Denna dokumentation genereras automatiskt från PHPDoc-kommentarerna i <code>/app/api/</code>.</p>

<?php foreach ($endpoints as $ep): ?>

<div class="endpoint-box">
    
    <div class="endpoint-header">
        <?php
            // Extract METHOD (GET/POST/etc)
            $method = strtok($ep['route'], " ");
            $endpointUrl = trim(substr($ep['route'], strlen($method)));
        ?>
        <span class="http-method <?= strtolower($method) ?>">
            <?= htmlspecialchars($method) ?>
        </span>
        <code class="endpoint-url">
            <?= htmlspecialchars($baseUrl . $endpointUrl) ?>
        </code>
    </div>
    
    <h2>Exempel på anrop</h2>
    <pre class="codeblock">curl "<?= htmlspecialchars($baseUrl . $endpointUrl) ?>?api_key={DIN_API_NYCKEL}"</pre>

    <p class="desc"><?= htmlspecialchars($ep['desc']) ?></p>

    <p><b>Controller:</b> <?= $ep['controller'] ?>::<?= $ep['method'] ?>()</p>

    <?php if ($ep['auth']): ?>
        <p><b>Auth:</b> <?= htmlspecialchars($ep['auth']) ?></p>
    <?php endif; ?>

    <?php if (!empty($ep['params'])): ?>
        <h3>Parametrar</h3>
        <ul>
        <?php foreach ($ep['params'] as $p): ?>
            <li><?= htmlspecialchars($p) ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($ep['returns']): ?>
        <h3>Returnerar</h3>
        <pre class="codeblock"><?= htmlspecialchars($ep['returns']) ?></pre>
    <?php endif; ?>

    <?php if (!empty($ep['errors'])): ?>
        <h3>Fel</h3>
        <ul>
        <?php foreach ($ep['errors'] as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</div>

<?php endforeach; ?>
</div>