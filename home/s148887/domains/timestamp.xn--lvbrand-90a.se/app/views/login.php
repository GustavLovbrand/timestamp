<div class="login-box">
    <h1>Logga in</h1>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <?php if (isset($_GET['timeout'])): ?>
        <p style="color: red;">Du loggades ut p.g.a inaktivitet.</p>
    <?php endif; ?>

    <form method="POST">
        Användarnamn:<br>
        <input type="text" name="username"><br><br>

        Lösenord:<br>
        <input type="password" name="password"><br><br>

        <button type="submit">Logga in</button>
    </form>

    <p><a href="index.php?controller=user&action=register">Registrera</a></p>
</div>