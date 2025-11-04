<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="text-center mt-5">Kirim Pengaduan</h2>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error; ?></div>
                <?php endif; ?>
                <form method="post" action="<?= site_url('complaint/add_complaint'); ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="complaint_text">Deskripsi Pengaduan</label>
                        <textarea class="form-control" id="complaint_text" name="complaint_text" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="file">Lampiran (Opsional)</label>
                        <input type="file" class="form-control" id="file" name="file">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Kirim Pengaduan</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
