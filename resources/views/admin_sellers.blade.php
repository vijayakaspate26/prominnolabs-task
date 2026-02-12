<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin – Sellers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background:#f3f4f6; margin:0; padding:20px; }
        .layout { display:flex; gap:20px; align-items:flex-start; }
        .card { background:#fff; border-radius:8px; padding:16px 18px; box-shadow:0 8px 20px rgba(15,23,42,0.07); }
        .card h2 { margin:0 0 10px; font-size:18px; }
        .field { margin-bottom:10px; }
        label { display:block; margin-bottom:4px; font-size:13px; font-weight:500; }
        input, select { width:100%; padding:7px 9px; border:1px solid #d1d5db; border-radius:4px; font-size:13px; box-sizing:border-box; }
        input:focus, select:focus { border-color:#2563eb; outline:none; box-shadow:0 0 0 1px #2563eb33; }
        .skills-select { height:80px; }
        button { padding:7px 12px; border:none; border-radius:4px; background:#2563eb; color:#fff; font-size:13px; font-weight:600; cursor:pointer; }
        button:disabled { opacity:.6; cursor:default; }
        .error { margin-top:4px; color:#b91c1c; font-size:12px; }
        .success { margin-top:4px; color:#15803d; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:10px; font-size:13px; }
        th, td { border:1px solid #e5e7eb; padding:6px 8px; text-align:left; }
        th { background:#f9fafb; font-weight:600; }
        .pagination { margin-top:8px; display:flex; gap:8px; align-items:center; font-size:13px; }
    </style>
</head>
<body>

<h1>Admin – Manage Sellers</h1>

<div class="layout">

    <!-- Create Seller form -->
    <div class="card" style="flex:0 0 320px;">
        <h2>Create Seller</h2>
        <form id="createSellerForm">
            <div class="field">
                <label for="name">Name</label>
                <input id="name" required>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" required>
            </div>
            <div class="field">
                <label for="mobile">Mobile No</label>
                <input id="mobile" required>
            </div>
            <div class="field">
                <label for="country">Country</label>
                <input id="country" required>
            </div>
            <div class="field">
                <label for="state">State</label>
                <input id="state" required>
            </div>
           <div class="field">
    <label>Skills</label>
    <div>
        <label><input type="checkbox" name="skills" value="Laravel"> Laravel</label><br>
        <label><input type="checkbox" name="skills" value="PHP"> PHP</label><br>
        <label><input type="checkbox" name="skills" value="React"> React</label><br>
        <label><input type="checkbox" name="skills" value="Vue"> Vue</label><br>
        <label><input type="checkbox" name="skills" value="MySQL"> MySQL</label>
    </div>
</div>


            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" required>
            </div>
            <button type="submit" id="createBtn">Create Seller</button>

            <div id="createError" class="error" style="display:none;"></div>
            <div id="createSuccess" class="success" style="display:none;"></div>
        </form>
    </div>

    <!-- Sellers list -->
    <div class="card" style="flex:1;">
        <h2>Sellers List</h2>
        <table id="sellersTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name (User)</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Country</th>
                <th>State</th>
                <th>Skills</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="pagination">
            <button id="prevPage">Prev</button>
            <span id="pageInfo"></span>
            <button id="nextPage">Next</button>
        </div>

        <div id="listError" class="error" style="display:none;"></div>
    </div>

</div>

<script>
    const API_BASE = '/api'; // adjust if needed

    // Expect admin token stored during login
    function getToken() {
        return localStorage.getItem('admin_token');
    }

    // =======================
    // Create seller
    // =======================
    const createForm = document.getElementById('createSellerForm');
    const createBtn = document.getElementById('createBtn');
    const createError = document.getElementById('createError');
    const createSuccess = document.getElementById('createSuccess');

    createForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        createError.style.display = 'none';
        createSuccess.style.display = 'none';

        const token = getToken();
        if (!token) {
            createError.textContent = 'No admin token. Please login as admin.';
            createError.style.display = 'block';
            return;
        }

        const skillCheckboxes = document.querySelectorAll('input[name="skills"]:checked');
        const skills = Array.from(skillCheckboxes).map(cb => cb.value);


        const payload = {
            name: document.getElementById('name').value.trim(),
            email: document.getElementById('email').value.trim(),
            mobile: document.getElementById('mobile').value.trim(),
            country: document.getElementById('country').value.trim(),
            state: document.getElementById('state').value.trim(),
            skills: skills,
            password: document.getElementById('password').value,
        };

        createBtn.disabled = true;
        createBtn.textContent = 'Creating...';

        try {
            const res = await fetch(API_BASE + '/admin/sellers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok) {
                createError.textContent = data.message || 'Error creating seller';
                createError.style.display = 'block';
            } else {
                createSuccess.textContent = 'Seller created successfully.';
                createSuccess.style.display = 'block';
                createForm.reset();
                loadSellers(currentPage); // refresh list
            }
        } catch (err) {
            createError.textContent = 'Network error.';
            createError.style.display = 'block';
        } finally {
            createBtn.disabled = false;
            createBtn.textContent = 'Create Seller';
        }
    });

    // =======================
    // Sellers list + pagination
    // =======================
    let currentPage = 1;

    const tableBody = document.querySelector('#sellersTable tbody');
    const pageInfo = document.getElementById('pageInfo');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const listError = document.getElementById('listError');

    async function loadSellers(page = 1) {
        listError.style.display = 'none';
        const token = getToken();
        if (!token) {
            listError.textContent = 'No admin token. Please login as admin.';
            listError.style.display = 'block';
            return;
        }

        try {
            const res = await fetch(API_BASE + '/admin/sellers?page=' + page, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                }
            });

            const data = await res.json();

            if (!res.ok) {
                listError.textContent = data.message || 'Error loading sellers.';
                listError.style.display = 'block';
                return;
            }

            tableBody.innerHTML = '';
            data.data.forEach(row => {
                const tr = document.createElement('tr');
                const skills = Array.isArray(row.skills) ? row.skills.join(', ') : '';

                tr.innerHTML = `
                    <td>${row.id}</td>
                    <td>${row.user ? row.user.name : ''}</td>
                    <td>${row.user ? row.user.email : ''}</td>
                    <td>${row.mobile}</td>
                    <td>${row.country}</td>
                    <td>${row.state}</td>
                    <td>${skills}</td>
                `;
                tableBody.appendChild(tr);
            });

            currentPage = data.current_page;
            pageInfo.textContent = `Page ${data.current_page} of ${data.last_page}`;

            prevBtn.disabled = data.current_page <= 1;
            nextBtn.disabled = data.current_page >= data.last_page;

        } catch (err) {
            listError.textContent = 'Network error.';
            listError.style.display = 'block';
        }
    }

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) loadSellers(currentPage - 1);
    });

    nextBtn.addEventListener('click', () => {
        loadSellers(currentPage + 1);
    });

    // Initial load
    loadSellers();
</script>

</body>
</html>
