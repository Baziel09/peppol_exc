<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UBL File Upload</title>
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
</head>
<body>
    <h2>Upload je UBL-facturen</h2>

    <input type="file" name="file[]" multiple />

    <div id="overzicht" style="margin-top: 20px;">
        <h2>Overzicht</h2>
        <p><strong>Totaal excl. BTW:</strong> €<span id="excl">0.00</span></p>
        <p><strong>Totaal BTW:</strong> €<span id="btw">0.00</span></p>
        <p><strong>Totaal incl. BTW:</strong> €<span id="incl">0.00</span></p>
    </div>

    <br><br>
    <form method="POST" action="generate.php">
        <button name="format" value="csv">Download CSV</button>
        <button name="format" value="pdf">Download PDF</button>
    </form>

    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script>
        const pond = FilePond.create(document.querySelector('input[type="file"]'), {
            server: {
                process: 'upload-handler.php',
                revert: null,
                restore: null,
                load: null
            }
        });

        function updateOverzicht() {
            fetch('totals.php')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('excl').textContent = data.excl.toFixed(2);
                    document.getElementById('btw').textContent = data.btw.toFixed(2);
                    document.getElementById('incl').textContent = data.incl.toFixed(2);
                });
        }

        // Trigger after every upload
        pond.on('processfile', () => {
            updateOverzicht();
        });

        // Initial fetch (on page load)
        updateOverzicht();

    </script>
</body>
</html>
