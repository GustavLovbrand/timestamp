<?php
function formatDuration($seconds) {
    if ($seconds <= 0) return "Utgått";

    $days = floor($seconds / 86400);
    $seconds %= 86400;

    $hours = floor($seconds / 3600);
    $seconds %= 3600;

    $minutes = floor($seconds / 60);

    $parts = [];
    if ($days > 0) $parts[] = "{$days} dagar";
    if ($hours > 0) $parts[] = "{$hours} timmar";
    if ($minutes > 0) $parts[] = "{$minutes} minuter";

    return implode(", ", $parts);
}
?>
<div class="box">
<h1>API-nycklar</h1>

<?php if ($apiKey): ?>

    <p><b>Din API-nyckel:</b></p>
    <p style="font-family: monospace; background: #eee; padding: 10px;">
        <?= $apiKey['api_key'] ?>
    </p>

    <p><b>Giltig i:</b> <?= formatDuration($remainingSeconds) ?></p>

    <p><b>Utgår:</b> <?= $expiresAt ?></p>

    <p>
        <a href="index.php?controller=apikey&action=extend">Förläng nyckeln med +7 dagar</a><br><br>
        <a href="index.php?controller=apikey&action=delete" style="color:red;">Ta bort nyckeln</a>
    </p>

<?php else: ?>

    <p>Du har ingen API-nyckel ännu.</p>
    <a href="index.php?controller=apikey&action=create">Skapa ny API-nyckel (giltig i 7 dagar)</a>

<?php endif; ?>

<p><a href="index.php?controller=time&action=dashboard">Tillbaka</a></p>
</div>