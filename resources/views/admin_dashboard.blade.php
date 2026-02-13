<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – Prominno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { margin:0; font-family: system-ui, sans-serif; background:#f3f4f6; }
        .topbar { background:#111827; color:#fff; padding:10px 16px; display:flex; justify-content:space-between; align-items:center; }
        .topbar h1 { font-size:18px; margin:0; }
        .topbar button { background:#ef4444; color:#fff; border:none; border-radius:4px; padding:6px 10px; font-size:13px; cursor:pointer; }
        .layout { display:flex; min-height:calc(100vh - 48px); }
        .sidebar { width:200px; background:#111827; color:#e5e7eb; padding:12px 0; }
        .sidebar a { display:block; padding:8px 16px; color:#e5e7eb; text-decoration:none; font-size:14px; }
        .sidebar a.active, .sidebar a:hover { background:#1f2937; }
        .content { flex:1; padding:16px; }
        iframe { border:none; width:100%; height:calc(100vh - 80px); background:transparent; }
    </style>
</head>
<body>

<div class="topbar">
    <h1>Prominno Admin Dashboard</h1>
    <button id="logoutBtn">Logout</button>
</div>

<div class="layout">
    <nav class="sidebar">
        <a href="#" class="active" data-target="/admin/sellers" id="sellersLink">Sellers</a>
        <!-- You can add more tabs later: Products, Reports, etc. -->
    </nav>
    <main class="content">
        <!-- We’ll load the sellers page in an iframe for simplicity -->
        <iframe id="contentFrame" src="/admin/sellers"></iframe>
    </main>
</div>

<script>
    // Basic check: if no admin token, send back to login
    const token = localStorage.getItem('admin_token');
    if (!token) {
        window.location.href = '/';
    }

    // Logout
    document.getElementById('logoutBtn').addEventListener('click', () => {
        localStorage.removeItem('admin_token');
        window.location.href = '/';
    });

    // Sidebar link click (only one tab now)
    const sellersLink = document.getElementById('sellersLink');
    const frame = document.getElementById('contentFrame');

    sellersLink.addEventListener('click', (e) => {
        e.preventDefault();
        frame.src = '/admin/sellers';
        sellersLink.classList.add('active');
    });
</script>

</body>
</html>
