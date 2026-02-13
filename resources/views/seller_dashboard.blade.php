<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard – Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background:#f3f4f6; margin:0; padding:20px; }
        h1 { margin-top:0; }
        .layout { display:flex; gap:20px; align-items:flex-start; }
        .card { background:#fff; border-radius:8px; padding:16px 18px; box-shadow:0 8px 20px rgba(15,23,42,0.07); }
        .card h2 { margin:0 0 10px; font-size:18px; }
        .field { margin-bottom:10px; }
        label { display:block; margin-bottom:4px; font-size:13px; font-weight:500; }
        input, textarea { width:100%; padding:7px 9px; border:1px solid #d1d5db; border-radius:4px; font-size:13px; box-sizing:border-box; }
        textarea { resize:vertical; }
        input:focus, textarea:focus { border-color:#2563eb; outline:none; box-shadow:0 0 0 1px #2563eb33; }
        button { padding:7px 12px; border:none; border-radius:4px; background:#2563eb; color:#fff; font-size:13px; font-weight:600; cursor:pointer; }
        button:disabled { opacity:.6; cursor:default; }
        .error { margin-top:4px; color:#b91c1c; font-size:12px; }
        .success { margin-top:4px; color:#15803d; font-size:12px; }
        table { width:100%; border-collapse:collapse; margin-top:10px; font-size:13px; }
        th, td { border:1px solid #e5e7eb; padding:6px 8px; text-align:left; }
        th { background:#f9fafb; font-weight:600; }
        .pagination { margin-top:8px; display:flex; gap:8px; align-items:center; font-size:13px; }
        .brand-block { border:1px dashed #d1d5db; border-radius:4px; padding:8px; margin-bottom:8px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
    </style>
</head>
<body>

<div class="topbar">
    <h1>Seller – Manage Products</h1>
    <button id="logoutBtn">Logout</button>
</div>

<div class="layout">

    <!-- Add Product form -->
    <div class="card" style="flex:0 0 360px;">
        <h2>Add Product</h2>
        <form id="productForm" enctype="multipart/form-data">
            <div class="field">
                <label for="pname">Product Name</label>
                <input id="pname" required>
            </div>
            <div class="field">
                <label for="pdesc">Product Description</label>
                <textarea id="pdesc" rows="3" required></textarea>
            </div>

            <h3 style="font-size:14px; margin:10px 0 6px;">Brands</h3>
            <div id="brandsContainer"></div>
            <button type="button" id="addBrandBtn" style="margin-bottom:8px;">+ Add Brand</button>

            <button type="submit" id="saveProductBtn">Save Product</button>

            <div id="formError" class="error" style="display:none;"></div>
            <div id="formSuccess" class="success" style="display:none;"></div>
        </form>
    </div>

    <!-- Products list -->
    <div class="card" style="flex:1;">
        <h2>Your Products</h2>
        <table id="productsTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Description</th>
                <th>Brands</th>
                <th>Total Price</th>
                <th>Actions</th>
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
    const API_BASE = '/api';

    // Auth check
    const token = localStorage.getItem('seller_token');
    if (!token) {
        window.location.href = '/';
    }

    document.getElementById('logoutBtn').addEventListener('click', () => {
        localStorage.removeItem('seller_token');
        window.location.href = '/';
    });

    // ========== Add product form with dynamic brands ==========
    const brandsContainer = document.getElementById('brandsContainer');
    const addBrandBtn = document.getElementById('addBrandBtn');
    const productForm = document.getElementById('productForm');
    const saveProductBtn = document.getElementById('saveProductBtn');
    const formError = document.getElementById('formError');
    const formSuccess = document.getElementById('formSuccess');

    function addBrandBlock(initial = {}) {
        const div = document.createElement('div');
        div.className = 'brand-block';
        div.innerHTML = `
            <div class="field">
                <label>Brand Name</label>
                <input class="brand-name" value="${initial.name || ''}" required>
            </div>
            <div class="field">
                <label>Detail</label>
                <input class="brand-detail" value="${initial.detail || ''}">
            </div>
            <div class="field">
                <label>Image (file)</label>
                <input type="file" class="brand-image" accept="image/*">
            </div>
            <div class="field">
                <label>Price</label>
                <input type="number" class="brand-price" value="${initial.price || ''}" required>
            </div>
            <button type="button" class="removeBrandBtn">Remove</button>
        `;
        brandsContainer.appendChild(div);

        div.querySelector('.removeBrandBtn').addEventListener('click', () => {
            brandsContainer.removeChild(div);
        });
    }

    // Start with one brand block
    addBrandBlock();

    addBrandBtn.addEventListener('click', () => {
        addBrandBlock();
    });

    productForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        formError.style.display = 'none';
        formSuccess.style.display = 'none';

        // collect brands
        const brandBlocks = brandsContainer.querySelectorAll('.brand-block');
        if (brandBlocks.length === 0) {
            formError.textContent = 'At least one brand is required.';
            formError.style.display = 'block';
            return;
        }

        const formData = new FormData();
        formData.append('name', document.getElementById('pname').value.trim());
        formData.append('description', document.getElementById('pdesc').value.trim());

        let index = 0;
        for (const block of brandBlocks) {
            const name = block.querySelector('.brand-name').value.trim();
            const detail = block.querySelector('.brand-detail').value.trim();
            const imageInput = block.querySelector('.brand-image');
            const price = block.querySelector('.brand-price').value;

            if (!name || !price) {
                formError.textContent = 'Brand name and price are required.';
                formError.style.display = 'block';
                return;
            }

            formData.append(`brands[${index}][name]`, name);
            formData.append(`brands[${index}][detail]`, detail);
            formData.append(`brands[${index}][price]`, price);

            if (imageInput.files[0]) {
                formData.append(`brands[${index}][image]`, imageInput.files[0]);
            }

            index++;
        }

        saveProductBtn.disabled = true;
        saveProductBtn.textContent = 'Saving...';

        try {
            const res = await fetch(API_BASE + '/seller/products', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                    // no Content-Type here; browser sets multipart boundary
                },
                body: formData
            });

            const data = await res.json();

            if (!res.ok) {
                formError.textContent = data.message || 'Error saving product.';
                formError.style.display = 'block';
            } else {
                formSuccess.textContent = 'Product created successfully.';
                formSuccess.style.display = 'block';
                productForm.reset();
                brandsContainer.innerHTML = '';
                addBrandBlock();
                loadProducts(currentPage);
            }
        } catch (err) {
            formError.textContent = 'Network error.';
            formError.style.display = 'block';
        } finally {
            saveProductBtn.disabled = false;
            saveProductBtn.textContent = 'Save Product';
        }
    });

    // ========== Product list + pagination + actions ==========
    let currentPage = 1;
    const tableBody = document.querySelector('#productsTable tbody');
    const pageInfo = document.getElementById('pageInfo');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const listError = document.getElementById('listError');

    async function loadProducts(page = 1) {
        listError.style.display = 'none';

        try {
            const res = await fetch(API_BASE + '/seller/products?page=' + page, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                }
            });

            const data = await res.json();

            if (!res.ok) {
                listError.textContent = data.message || 'Error loading products.';
                listError.style.display = 'block';
                return;
            }

            tableBody.innerHTML = '';
            data.data.forEach(row => {
                const total = Array.isArray(row.brands)
                    ? row.brands.reduce((s, b) => s + Number(b.price || 0), 0)
                    : 0;

                const brandsText = Array.isArray(row.brands)
                    ? row.brands.map(b => `${b.name} (${b.price})`).join(', ')
                    : '';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.id}</td>
                    <td>${row.name}</td>
                    <td>${row.description}</td>
                    <td>${brandsText}</td>
                    <td>${total}</td>
                    <td>
                        <button data-id="${row.id}" class="pdfBtn">View PDF</button>
                        <button data-id="${row.id}" class="deleteBtn">Delete</button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            currentPage = data.current_page;
            pageInfo.textContent = `Page ${data.current_page} of ${data.last_page}`;
            prevBtn.disabled = data.current_page <= 1;
            nextBtn.disabled = data.current_page >= data.last_page;

            // attach events
            tableBody.querySelectorAll('.pdfBtn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    openPdf(id);
                });
            });

            tableBody.querySelectorAll('.deleteBtn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    deleteProduct(id);
                });
            });

        } catch (err) {
            listError.textContent = 'Network error.';
            listError.style.display = 'block';
        }
    }

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) loadProducts(currentPage - 1);
    });

    nextBtn.addEventListener('click', () => {
        loadProducts(currentPage + 1);
    });

    async function openPdf(id) {
        try {
            const res = await fetch(API_BASE + '/seller/products/' + id + '/pdf', {
                headers: {
                    'Accept': 'application/pdf',
                    'Authorization': 'Bearer ' + token,
                }
            });

            if (!res.ok) {
                alert('Unable to open PDF');
                return;
            }

            const blob = await res.blob();
            const url = window.URL.createObjectURL(blob);
            window.open(url, '_blank');
        } catch (e) {
            alert('Network error while loading PDF');
        }
    }

    async function deleteProduct(id) {
        if (!confirm('Delete this product?')) return;

        try {
            const res = await fetch(API_BASE + '/seller/products/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token,
                }
            });

            const data = await res.json();

            if (!res.ok) {
                alert(data.message || 'Error deleting product.');
            } else {
                loadProducts(currentPage);
            }
        } catch (err) {
            alert('Network error.');
        }
    }

    // initial load
    loadProducts();
</script>

</body>
</html>
