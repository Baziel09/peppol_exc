<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UBL File Upload</title>
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
</head>
<body>
    <h2>Upload je UBL-facturen</h2>

    <input type="file" name="ublFiles[]" multiple />

    <br><br>
    <form method="POST" action="generate.php">
        <button name="format" value="csv">Download CSV</button>
        <button name="format" value="pdf">Download PDF</button>
    </form>

    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script>
        FilePond.create(document.querySelector('input[type="file"]'), {
            server: {
                process: 'upload.php',
                revert: null,
                restore: null,
                load: null
            }
        });
    </script>
</body>
</html>
