<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prominno Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f2f4f7;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            padding: 24px 28px;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(15,23,42,0.08);
            width:100%;
            max-width: 380px;
        }
        h2 {
            margin: 0 0 4px;
            font-size: 22px;
        }
        .subtitle {
            margin: 0 0 16px;
            color: #6b7280;
            font-size: 13px;
        }
        label {
            display:block;
            margin-bottom: 4px;
            font-size: 13px;
            font-weight: 500;
        }
        .field {
            margin-bottom: 14px;
        }
        input, select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 1px #2563eb33;
        }
        button {
            width: 100%;
            padding: 9px 12px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        button:disabled {
            opacity: 0.6;
            cursor: default;
        }
        .role-switch {
            margin-bottom: 12px;
            font-size: 13px;
        }
        .error {
            margin-top: 6px;
            color: #b91c1c;
            font-size: 13px;
        }
        .success {
            margin-top: 6px;
            color: #15803d;
            font-size: 13px;
            word-break: break-all;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Login</h2>
    <p class="subtitle">Choose role and sign in to Prominno.</p>

    <div class="role-switch">
        <label for="role">Login as</label>
        <select id="role">
            <option value="admin">Admin</option>
            <option value="seller">Seller</option>
        </select>
    </div>

    <form id="loginForm">
        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" required placeholder="you@example.com">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" required placeholder="••••••••">
        </div>

        <button type="submit" id="loginBtn">Login</button>

        <div id="error" class="error" style="display:none;"></div>
        <div id="success" class="success" style="display:none;"></div>
    </form>
</div>

<script>
    const form    = document.getElementById('loginForm');
    const roleSel = document.getElementById('role');
    const btn     = document.getElementById('loginBtn');
    const errorEl = document.getElementById('error');
    const successEl = document.getElementById('success');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorEl.style.display = 'none';
        successEl.style.display = 'none';

        const role = roleSel.value; // admin or seller
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        if (!email || !password) {
            errorEl.textContent = 'Email and password are required.';
            errorEl.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Logging in...';

        // Adjust base URL if needed
        const url = role === 'admin'
            ? '/api/admin/login'
            : '/api/seller/login';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (!response.ok) {
                errorEl.textContent = data.message || 'Login failed';
                errorEl.style.display = 'block';
            } else {
                // Show token and role (in real app you store in localStorage and redirect)
                successEl.textContent = 'Login success. Role: ' + data.role + ' | Token: ' + data.access_token;
                successEl.style.display = 'block';

                if (data.role === 'admin') {
                    localStorage.setItem('admin_token', data.access_token);
                    window.location.href = '/admin/dashboard';
                } else if (data.role === 'seller') {
                    localStorage.setItem('seller_token', data.access_token);
                    window.location.href = '/seller/dashboard'; // optional for later
                }

                // Example: localStorage.setItem('token', data.access_token);
                // window.location.href = role === 'admin' ? '/admin/dashboard' : '/seller/dashboard';
            }
        } catch (err) {
            errorEl.textContent = 'Network error, please try again.';
            errorEl.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Login';
        }
    });
</script>

</body>
</html>
