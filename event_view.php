<?php
require_once 'includes/db.php';

if(!isset($_GET['code'])) {
    die("Error: No event code provided.");
}

$code = mysqli_real_escape_string($conn, $_GET['code']);
$event_res = mysqli_query($conn, "SELECT * FROM events WHERE qr_identifier = '$code'");
$event = mysqli_fetch_assoc($event_res);

if(!$event) {
    die("Error: Event not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event['event_name']; ?> | Event Photos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="logo.png">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #3b82f6;
            --dark: #0f172a;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg); 
            color: var(--text-main); 
            margin: 0;
            overflow-x: hidden;
        }
        
        .portal-header { 
            background: white; 
            border-bottom: 1px solid var(--border); 
            padding: 80px 0; 
            position: relative;
            text-align: center;
        }

        .logo-wrap { display: flex; align-items: center; justify-content: center; margin-bottom: 30px; }
        .logo-wrap img { height: 40px; margin-right: 15px; }
        .brand-name { font-weight: 800; font-size: 1.5rem; letter-spacing: -0.05em; color: var(--dark); }

        .event-title { font-size: 4rem; font-weight: 800; letter-spacing: -0.05em; margin-bottom: 20px; color: var(--dark); }
        .event-meta { font-size: 1.1rem; color: var(--text-muted); font-weight: 500; max-width: 600px; margin: 0 auto; }

        .gallery-wrap { padding: 80px 0; }
        .asset-card { 
            background: white; 
            border-radius: 30px; 
            overflow: hidden; 
            border: 1px solid var(--border); 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .asset-card:hover { transform: translateY(-12px); border-color: var(--primary); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); }
        
        .img-box { width: 100%; aspect-ratio: 1; overflow: hidden; background: #f1f5f9; position: relative; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        .img-type { position: absolute; top: 20px; right: 20px; background: white; padding: 5px 12px; border-radius: 30px; font-size: 0.65rem; font-weight: 800; color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.1); border: 1px solid var(--border); }

        .card-details { padding: 30px; flex-grow: 1; display: flex; flex-direction: column; }
        .asset-title { font-weight: 800; font-size: 1.25rem; margin-bottom: 8px; color: var(--dark); }
        .asset-subtitle { font-size: 0.75rem; color: var(--text-muted); font-weight: 700; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 0.1em; }
        
        .btn-download { 
            background: var(--primary); 
            color: white; border-radius: 16px; padding: 15px; font-weight: 700; width: 100%; text-decoration: none; 
            display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s; 
        }
        .btn-download:hover { background: var(--dark); color: white; transform: scale(0.98); }

        .portal-footer { padding: 60px 0; border-top: 1px solid var(--border); text-align: center; background: white; }
        .footer-logo { height: 24px; opacity: 0.5; margin-bottom: 25px; }
        .footer-text { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }
        
        .empty-state { padding: 100px 0; text-align: center; }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .event-title { font-size: 2.2rem; }
            .portal-header { padding: 40px 20px; }
            .event-meta { font-size: 1rem; }
            .gallery-wrap { padding: 40px 0; }
            .asset-card { border-radius: 20px; }
            .card-details { padding: 20px; }
        }
    </style>
</head>
<body>

<header class="portal-header text-center">
    <div class="container">
        <div class="logo-wrap">
            <img src="logo.png" alt="Logo">
            <span class="brand-name">EventHub</span>
        </div>
        <h1 class="event-title"><?php echo $event['event_name']; ?></h1>
        <div class="event-meta">
            <p><i class="bi bi-calendar3 me-2"></i> <?php echo date('F d, Y', strtotime($event['event_date'])); ?></p>
            <p class="small opacity-75"><?php echo $event['event_description'] ?: 'Photos from our school event.'; ?></p>
        </div>
    </div>
</header>

<main class="gallery-wrap">
    <div class="container">
        <div class="row g-4">
            <?php
            $assets = mysqli_query($conn, "SELECT * FROM media_assets WHERE event_id = " . $event['id'] . " ORDER BY id DESC");
            if(mysqli_num_rows($assets) == 0): ?>
                <div class="col-12 empty-state">
                    <i class="bi bi-folder2-open display-1 text-light mb-3"></i>
                    <h4 class="fw-bold text-muted">No photos yet</h4>
                    <p class="text-muted">Photos will show here soon.</p>
                </div>
            <?php endif;
            while($media = mysqli_fetch_assoc($assets)) {
                $ext = strtoupper(pathinfo($media['file_path'], PATHINFO_EXTENSION));
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="asset-card">
                        <div class="img-box">
                            <span class="img-type"><?php echo $ext; ?> HD</span>
                            <img src="watermark.php?image=<?php echo urlencode($media['file_path']); ?>" alt="Photo">
                        </div>
                        <div class="card-details">
                            <div class="asset-title"><?php echo $media['title'] ?: $media['file_name']; ?></div>
                            <div class="asset-subtitle">Good Photo</div>
                            <a href="<?php echo $media['file_path']; ?>" download class="btn-download">
                                <i class="bi bi-cloud-arrow-down-fill"></i> Save Photo
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</main>

<footer class="portal-footer">
    <div class="container">
        <img src="logo.png" alt="Logo" class="footer-logo">
        <p class="footer-text">&copy; <?php echo date('Y'); ?> School Event Hub</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>