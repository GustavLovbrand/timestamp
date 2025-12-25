<!DOCTYPE html>
<html>
<head>
    <title>Timestamp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="content">
            <?php require __DIR__ . "/$view.php"; ?>
        </div>

        <footer class="footer">
            <div class="footer-content">
                <p>&copy; <?= date("Y") ?> Timestamp System</p>
                
                <nav class="footer-links">
                    <a href="index.php?controller=time&action=dashboard">Dashboard</a>
                    <a href="index.php?controller=apikey&action=index">API-nycklar</a>
                    <a href="index.php?controller=apiDocs&action=index">API Dokumentation</a>
                    <a href="index.php?controller=user&action=logout">Logga ut</a>
                </nav>
            </div>
        </footer>
</div>
</body>
</html>
