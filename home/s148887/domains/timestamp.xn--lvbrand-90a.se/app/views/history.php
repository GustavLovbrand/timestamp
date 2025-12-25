<div class="box">
    <h1>Historik & Statistik</h1>

    <p>
        <a class="link" href="index.php?controller=time&action=manual">Skapa manuell stämpling</a>
    </p>

    <h2>Statistik</h2>
    <ul class="stats">
        <li><b>Total tid instämplad:</b> <?= $totalHours ?> timmar</li>
        <li><b>Antal stämplingspass:</b> <?= $numSessions ?></li>
        <li><b>Snitt per pass:</b> <?= $average ?> timmar</li>
    </ul>

    <hr>

    <h2>Alla stämplingar</h2>

    <table class="styled-table">
        <tr>
            <th>Datum</th>
            <th>Tid</th>
            <th>Typ</th>
            <th>Manuell</th>
            <th>Status</th>
        </tr>

        <?php foreach ($events as $e): ?>
        <?php 
            $invalid = isset($e['invalid']) && $e['invalid']; 
            $rowClass = $invalid ? "invalid-row" : "";
        ?>
        <tr class="<?= $rowClass ?>">
            <td><?= date("Y-m-d", strtotime($e['event_time'])) ?></td>
            <td><?= date("H:i:s", strtotime($e['event_time'])) ?></td>
            <td><?= $e['event_type'] ?></td>
            <td><?= $e['is_manual'] ? "Ja" : "Nej" ?></td>
            <td>
                <?php if ($invalid): ?>
                    <span class="invalid-label">⚠ <?= htmlspecialchars($e['invalid']) ?></span>
                <?php else: ?>
                    ✔ OK
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a class="link" href="index.php?controller=time&action=dashboard">Tillbaka</a></p>
</div>