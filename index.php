<?php
require 'config.php';
$props = $db->query("SELECT * FROM properties")->fetchAll(PDO::FETCH_ASSOC);
$props_json = json_encode($props); // Pass all properties to JavaScript
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropEasy - Find Your Space</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body { background: #eef2f7; font-family: Arial, sans-serif; margin: 0; }
        nav { background: #ffffff; padding: 12px 20px; box-shadow: 0 1px 4px #ccc; }
        nav a { margin: 0 15px; text-decoration: none; color: #007bff; font-weight: 500; }
        #search-box { background: #007bff; color: #fff; padding: 30px; text-align: center; }
        #search-box h1 { font-size: 28px; margin-bottom: 20px; }
        #search-form { display: flex; gap: 8px; max-width: 650px; margin: 0 auto; }
        #search-form input, #search-form select { padding: 8px; border: none; border-radius: 4px; flex: 1; }
        #search-form button { background: #dc3545; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; }
        #prop-grid { padding: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; }
        .prop-item { background: #fff; border-radius: 6px; box-shadow: 0 2px 8px #ddd; overflow: hidden; }
        .prop-item img { width: 100%; height: 180px; object-fit: cover; }
        .prop-info { padding: 12px; }
        .prop-info h3 { font-size: 18px; color: #333; }
        .prop-info p { font-size: 14px; color: #666; margin: 4px 0; }
        .prop-actions { padding: 0 12px 12px; display: flex; gap: 8px; }
        .action-btn { padding: 6px 12px; border: none; border-radius: 4px; color: #fff; cursor: pointer; flex: 1; }
        .sched-btn { background: #28a745; }
        .buy-btn { background: #007bff; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal-content { background: #fff; width: 320px; margin: 80px auto; padding: 15px; border-radius: 6px; }
        .modal-content input, .modal-content button { width: 100%; padding: 8px; margin: 8px 0; border-radius: 4px; border: 1px solid #ddd; }
        .modal-content button { background: #007bff; color: #fff; border: none; }
        #pay-msg, #sched-msg { font-size: 14px; color: #555; margin-top: 8px; }
        #card-slot { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="owner.php">Owner Area</a>
    </nav>
    <div id="search-box">
        <h1>Find Your Next Property</h1>
        <form id="search-form">
            <input type="text" id="loc-input" placeholder="City or Area">
            <select id="type-input">
                <option value="">All Types</option>
                <option value="house">House</option>
                <option value="apartment">Apartment</option>
                <option value="land">Land</option>
            </select>
            <input type="number" id="max-input" placeholder="Max Price (₹)">
            <button type="submit">Search</button>
        </form>
    </div>
    <div id="prop-grid"></div>

    <div id="sched-modal" class="modal">
        <div class="modal-content">
            <h3>Schedule a Visit</h3>
            <input type="date" id="sched-date">
            <input type="hidden" id="sched-prop-id">
            <button id="sched-submit">Confirm</button>
            <p id="sched-msg"></p>
        </div>
    </div>

    <div id="pay-modal" class="modal">
        <div class="modal-content">
            <h3>Pay for Property</h3>
            <div id="card-slot"></div>
            <button id="pay-submit">Pay Now</button>
            <a href="index.php" style="color: black;text-decoration: none;">Cancel</a>
            <p id="pay-msg"></p>
            <input type="hidden" id="pay-prop-id">
            <input type="hidden" id="pay-amount">
        </div>
    </div>

    <script>
        const stripe = Stripe('YOUR_STRIPE_PUBLISHABLE_KEY'); 
        const elements = stripe.elements();
        const card = elements.create('card', { style: { base: { fontSize: '14px' } } });
        card.mount('#card-slot');

        const allProps = <?php echo $props_json; ?>;
        const grid = document.getElementById('prop-grid');

        function showProps(props) {
            grid.innerHTML = '';
            props.forEach(p => {
                const item = document.createElement('div');
                item.className = 'prop-item';
                item.innerHTML = `
                    <img src="${p.image}" alt="${p.title}">
                    <div class="prop-info">
                        <h3>${p.title}</h3>
                        <p>Area: ${p.location}</p>
                        <p>Cost: ₹${Number(p.price).toLocaleString()}</p>
                        <p>${p.description}</p>
                    </div>
                    <div class="prop-actions">
                        <button class="action-btn sched-btn" onclick="openSched(${p.id})">Visit</button>
                        <button class="action-btn buy-btn" onclick="openPay(${p.id}, ${p.price})">Buy</button>
                    </div>
                `;
                grid.appendChild(item);
            });
        }

        document.getElementById('search-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const loc = document.getElementById('loc-input').value.toLowerCase();
            const type = document.getElementById('type-input').value;
            const max = document.getElementById('max-input').value;

            const filtered = allProps.filter(p => {
                return (
                    (!loc || p.location.toLowerCase().includes(loc)) &&
                    (!type || p.type === type) &&
                    (!max || p.price <= parseInt(max))
                );
            });
            showProps(filtered);
        });

        function openSched(propId) {
            document.getElementById('sched-modal').style.display = 'block';
            document.getElementById('sched-prop-id').value = propId;
        }

        document.getElementById('sched-submit').addEventListener('click', async () => {
            const propId = document.getElementById('sched-prop-id').value;
            const date = document.getElementById('sched-date').value;
            if (!date) {
                document.getElementById('sched-msg').textContent = 'Pick a date!';
                return;
            }
            const resp = await fetch('request.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prop_id: propId, req: 'viewing', date: date })
            });
            const result = await resp.json();
            document.getElementById('sched-msg').textContent = result.message;
            if (result.success) setTimeout(() => document.getElementById('sched-modal').style.display = 'none', 1500);
        });

        function openPay(propId, amount) {
            document.getElementById('pay-modal').style.display = 'block';
            document.getElementById('pay-prop-id').value = propId;
            document.getElementById('pay-amount').value = amount;
        }

        document.getElementById('pay-submit').addEventListener('click', async (e) => {
            e.preventDefault();
            const propId = document.getElementById('pay-prop-id').value;
            const amount = document.getElementById('pay-amount').value;

            const { paymentMethod, error } = await stripe.createPaymentMethod({ type: 'card', card });
            if (error) {
                document.getElementById('pay-msg').textContent = error.message;
            } else {
                const resp = await fetch('payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ paymentMethodId: paymentMethod.id, amount: amount * 100, propId })
                });
                const result = await resp.json();
                document.getElementById('pay-msg').textContent = result.message;
                if (result.success) setTimeout(() => {
                    document.getElementById('pay-modal').style.display = 'none';
                    card.clear();
                }, 1500);
            }
        });

        showProps(allProps); // Initial display
    </script>
</body>
</html>
