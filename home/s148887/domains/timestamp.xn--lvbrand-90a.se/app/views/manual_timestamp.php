<div class="box">
    <h1>Manuell stämpling</h1>

    <form method="POST" action="index.php?controller=time&action=manualSave">

        <label>Datum:</label>
        <input type="date" name="date" required>

        <label>Tid:</label>
        <input type="time" name="time" required>

        <label>Typ:</label>
        <select name="type" required>
            <option value="IN">IN</option>
            <option value="OUT">OUT</option>
        </select>

        <button class="btn" type="submit">Spara stämpling</button>
    </form>

    <p><a class="link" href="index.php?controller=time&action=dashboard">Tillbaka</a></p>
</div>