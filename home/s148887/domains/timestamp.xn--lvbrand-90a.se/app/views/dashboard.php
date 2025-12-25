<div class="box">
    <h1>Stämpelklocka</h1>

    <?php if ($isClockedIn): ?>
        <p>Du är instämplad sedan: <b><?= $last['event_time'] ?></b></p>
        <a class="btn" href="index.php?controller=time&action=clockOut">Stämpla ut</a>

    <?php else: ?>
        <p>Du är inte instämplad.</p>
        <a class="btn" href="index.php?controller=time&action=clockIn">Stämpla in</a>
    <?php endif; ?>

    <p>
        <a class="link" href="index.php?controller=time&action=manual">Skapa manuell stämpling</a>
    </p>

    <p>
        <a class="link" href="index.php?controller=time&action=history">Visa historik & statistik</a>
    </p>

    <p>
        <a class="link" href="index.php?controller=apikey&action=index">Hantera API-nycklar</a>
    </p>

    <p>
        <a class="link" href="index.php?controller=user&action=logout">Logga ut</a>
    </p>
    
    <p>
        <a class="link" href="/index.php?controller=apiDocs&action=index">API Docs</a>
    </p>
</div>