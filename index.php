<?php
require_once 'includes/db.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$selected_event_id = isset($_GET['manage']) ? intval($_GET['manage']) : null;

// Handle Event Creation
if(isset($_POST['create_event'])) {
    $name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $date = $_POST['event_date'];
    $desc = mysqli_real_escape_string($conn, $_POST['event_description']);
    $identifier = bin2hex(random_bytes(8)); 
    $sql = "INSERT INTO events (event_name, event_date, event_description, qr_identifier) VALUES ('$name', '$date', '$desc', '$identifier')";
    mysqli_query($conn, $sql);
    header("Location: index.php?event_created=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EventHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="logo.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #3b82f6;
            --dark: #0f172a;
            --light: #ffffff;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg); 
            color: var(--text-main); 
            overflow-x: hidden; 
            margin: 0;
        }
        
        .app-wrapper { display: flex; min-height: 100vh; }
        
        .sidebar { 
            width: 320px; 
            background: white; 
            border-right: 1px solid var(--border); 
            display: flex; 
            flex-direction: column; 
            position: fixed; 
            height: 100vh; 
            z-index: 100;
        }
        .main-content { flex: 1; margin-left: 320px; padding: 60px; }

        .sidebar-header { padding: 40px 30px; border-bottom: 1px solid var(--border); }
        .sidebar-brand { font-weight: 800; font-size: 1.5rem; color: var(--dark); text-decoration: none; display: flex; align-items: center; letter-spacing: -0.05em; }
        .sidebar-brand img { height: 32px; margin-right: 15px; }
        
        .sidebar-scroll { flex: 1; overflow-y: auto; padding: 20px; }
        .sidebar-footer { padding: 30px; background: #f8fafc; border-top: 1px solid var(--border); }

        .nav-item-card { 
            padding: 20px; border-radius: 20px; margin-bottom: 12px; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent; display: block; text-decoration: none; color: inherit; background: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .nav-item-card:hover { border-color: var(--primary); transform: translateX(5px); }
        .nav-item-card.active { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 10px 15px -3px rgba(37,99,235,0.2); }
        .nav-item-card.active .small { color: rgba(255,255,255,0.7) !important; }

        .page-title { font-weight: 800; font-size: 3rem; letter-spacing: -0.05em; margin-bottom: 10px; color: var(--dark); }
        
        .white-card { 
            background: white;
            border: 1px solid var(--border);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .asset-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
        .asset-item { position: relative; border-radius: 20px; overflow: hidden; height: 180px; transition: all 0.4s; border: 1px solid var(--border); }
        .asset-item img { width: 100%; height: 100%; object-fit: cover; }
        .asset-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); display: flex; flex-direction: column; justify-content: flex-end; padding: 15px; opacity: 0; transition: opacity 0.3s; }
        .asset-item:hover .asset-overlay { opacity: 1; }

        .btn { border-radius: 14px; font-weight: 700; padding: 14px 24px; transition: all 0.3s; }
        .btn-primary { background: var(--primary); border: none; color: white; }
        .btn-primary:hover { background: var(--dark); transform: translateY(-3px); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.1); color: white; }
        
        .ai-status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #dcfce7;
            color: #166534;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 800;
            border: 1px solid #bbf7d0;
            margin-bottom: 20px;
        }
        .ai-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; }

        .fade-in { animation: fadeIn 0.8s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-brand">
                <img src="logo.png" alt="Logo">
                EventHub
            </a>
        </div>
        
        <div class="sidebar-scroll">
            <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                <h6 class="text-uppercase small fw-bold text-muted mb-0" style="letter-spacing: 0.1em;">All Events</h6>
                <button class="btn btn-sm btn-dark rounded-circle p-1" style="width:28px; height:28px;" data-bs-toggle="modal" data-bs-target="#createModal"><i class="bi bi-plus"></i></button>
            </div>

            <?php
            $res = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date DESC");
            while($row = mysqli_fetch_assoc($res)) {
                $is_active = ($selected_event_id == $row['id']);
                $asset_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM media_assets WHERE event_id = ".$row['id']))['c'];
            ?>
                <a href="index.php?manage=<?php echo $row['id']; ?>" class="nav-item-card <?php echo $is_active ? 'active' : ''; ?>">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small fw-bold <?php echo $is_active ? 'text-white-50' : 'text-muted'; ?>"><?php echo date('M d, Y', strtotime($row['event_date'])); ?></span>
                        <span class="small fw-bold"><?php echo $asset_count; ?> photos</span>
                    </div>
                    <div class="fw-bold" style="font-size: 1.1rem;"><?php echo $row['event_name']; ?></div>
                </a>
            <?php } ?>
        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; font-weight:800; font-size:1.2rem;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div>
                    <div class="fw-bold"><?php echo $_SESSION['username']; ?></div>
                    <div class="small text-muted">Admin</div>
                </div>
            </div>
            <a href="logout.php" class="btn btn-outline-danger w-100 btn-sm">Log Out</a>
        </div>
    </aside>

    <main class="main-content">
        <?php if($selected_event_id): 
            $evt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id = $selected_event_id"));
            if($evt):
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                if ($host == 'localhost' || $host == '127.0.0.1') {
                    $host = '192.168.43.6'; // Your local IP address for mobile access
                }
                $dir = dirname($_SERVER['PHP_SELF']);
                $link = "$protocol://$host$dir/event_view.php?code=" . $evt['qr_identifier'];
                $qr_api = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($link);
        ?>
            <div class="fade-in">
                <div class="content-header">
                    <div class="ai-status mb-3"><span class="ai-dot"></span> Smart Quality Check: ON</div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h2 class="page-title"><?php echo $evt['event_name']; ?></h2>
                            <p class="text-muted mb-0 lead" style="max-width: 600px;"><?php echo $evt['event_description'] ?: 'The system is ready to manage photos for this event.'; ?></p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo $link; ?>" target="_blank" class="btn btn-outline-primary"><i class="bi bi-box-arrow-up-right me-2"></i> View Sample Page</a>
                            <button onclick="confirmDeleteEvent('<?php echo $evt['id']; ?>')" class="btn btn-outline-danger"><i class="bi bi-trash3"></i></button>
                        </div>
                    </div>
                </div>

                <div class="row g-5">
                    <div class="col-lg-8">
                        <div class="white-card p-5">
                            <h4 class="fw-bold mb-5 d-flex align-items-center">
                                <i class="bi bi-images me-3 text-primary"></i> 
                                Your Photos
                                <span class="badge bg-primary ms-3 small" style="font-size: 0.6rem; vertical-align: middle;">CHECKED</span>
                            </h4>
                            <div class="asset-grid">
                                <?php
                                $assets = mysqli_query($conn, "SELECT * FROM media_assets WHERE event_id = " . $evt['id'] . " ORDER BY id DESC");
                                while($asset = mysqli_fetch_assoc($assets)) {
                                ?>
                                    <div class="asset-item">
                                        <img src="watermark.php?image=<?php echo urlencode($asset['file_path']); ?>">
                                        <div class="asset-overlay">
                                            <div class="small fw-bold text-white text-truncate mb-2"><?php echo $asset['title'] ?: $asset['file_name']; ?></div>
                                            <button onclick="confirmDeleteMedia('<?php echo $asset['id']; ?>')" class="btn btn-sm btn-danger py-1 px-2 w-100" style="font-size: 0.7rem;"><i class="bi bi-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if(mysqli_num_rows($assets) == 0) echo '<div class="col-12 text-center py-5 text-muted">Start by adding some photos.</div>'; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="white-card p-4 mb-4" style="background: #eff6ff;">
                            <h6 class="fw-bold text-uppercase small mb-4 text-primary d-flex justify-content-between">
                                <span>Add New Photo</span>
                                <i class="bi bi-cloud-arrow-up"></i>
                            </h6>
                            <form action="upload.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="event_id" value="<?php echo $evt['id']; ?>">
                                <div class="mb-3">
                                    <input type="file" name="file" class="form-control form-control-sm border-primary border-opacity-20 py-2" required>
                                </div>
                                <div class="mb-4">
                                    <input type="text" name="title" class="form-control form-control-sm border-primary border-opacity-20 py-2" placeholder="Photo Title">
                                </div>
                                <button type="submit" name="upload" class="btn btn-primary w-100">Add Photo</button>
                            </form>
                        </div>

                        <div class="white-card p-4">
                            <h6 class="fw-bold mb-4">Event QR Code</h6>
                            <div class="text-center p-4 bg-light rounded-4 mb-4" style="border: 1px dashed #cbd5e1;">
                                <img src="<?php echo $qr_api; ?>" class="img-fluid" style="border-radius:12px;">
                            </div>
                            <div class="small text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.65rem;">Event Code</div>
                            <code class="d-block bg-light p-3 rounded-4 small text-break fw-bold text-center text-primary" style="border: 1px solid #e2e8f0;"><?php echo $evt['qr_identifier']; ?></code>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; endif; ?>
    </main>
</div>

<!-- Modal Creation -->
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content overflow-hidden border-0" style="border-radius: 30px;">
      <div class="modal-body p-5">
        <h3 class="fw-bold mb-4">Add New Event</h3>
        <form method="POST">
            <div class="mb-4">
                <label class="form-label small fw-bold">Event Name</label>
                <input type="text" name="event_name" class="form-control py-3 px-4 rounded-4" placeholder="e.g. Graduation 2024" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Event Date</label>
                <input type="date" name="event_date" class="form-control py-3 px-4 rounded-4" required>
            </div>
            <div class="mb-5">
                <label class="form-label small fw-bold">Event Description</label>
                <textarea name="event_description" class="form-control py-3 px-4 rounded-4" rows="4" placeholder="Brief details about the event..."></textarea>
            </div>
            <button type="submit" name="create_event" class="btn btn-primary w-100 py-3 rounded-4">Create Event</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function confirmDeleteEvent(eventId) {
    Swal.fire({
        title: 'Delete this event?',
        text: "Are you sure? This will delete the event and all its photos forever. This cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete event'
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = 'delete.php?delete_event=' + eventId; }
    })
}
function confirmDeleteMedia(mediaId) {
    Swal.fire({
        title: 'Delete photo?',
        text: "This photo will be deleted forever.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = 'delete.php?delete_media=' + mediaId; }
    })
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
