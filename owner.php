<?php
require 'config.php';
$tenants = $db->query("SELECT t.*, p.title FROM tenants t JOIN properties p ON t.property_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
$leases = $db->query("SELECT l.*, t.name, p.title FROM leases l JOIN tenants t ON l.tenant_id = t.id JOIN properties p ON l.property_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
$maintenance = $db->query("SELECT m.*, t.name, p.title FROM maintenance m JOIN tenants t ON m.tenant_id = t.id JOIN properties p ON m.property_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PropEasy - Owner Portal</title>
    <style>
        body { background: #f0f2f5; font-family: Arial; margin: 0; }
        nav { background: #fff; padding: 15px; box-shadow: 0 2px 5px #ddd; }
        nav a { margin: 0 20px; text-decoration: none; color: #1a73e8; }
        section { padding: 40px; }
        form { margin: 20px 0; }
        input, textarea, button { padding: 10px; margin: 5px 0; border: none; border-radius: 5px; }
        button { background: #1a73e8; color: white; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="owner.php">Owner Portal</a>
    </nav>
    <section>
        <h2>Add Tenant Application</h2>
        <form action="add_tenant.php" method="POST">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="number" name="prop_id" placeholder="Property ID" required>
            <button>Add</button>
        </form>

        <h2>Tenants</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Property</th><th>Status</th></tr>
            <?php foreach ($tenants as $t) {
                echo "<tr><td>{$t['id']}</td><td>{$t['name']}</td><td>{$t['title']}</td><td>{$t['application_status']}</td></tr>";
            } ?>
        </table>

        <h2>Create Lease</h2>
        <form action="add_lease.php" method="POST">
            <input type="number" name="tenant_id" placeholder="Tenant ID" required>
            <input type="number" name="prop_id" placeholder="Property ID" required>
            <input type="date" name="start" required>
            <input type="date" name="end" required>
            <input type="number" name="rent" placeholder="Rent Amount" required>
            <button>Create</button>
        </form>

        <h2>Leases</h2>
        <table>
            <tr><th>ID</th><th>Tenant</th><th>Property</th><th>Rent</th></tr>
            <?php foreach ($leases as $l) {
                echo "<tr><td>{$l['id']}</td><td>{$l['name']}</td><td>{$l['title']}</td><td>{$l['rent_amount']}</td></tr>";
            } ?>
        </table>

        <h2>Maintenance Requests</h2>
        <form action="add_maintenance.php" method="POST">
            <input type="number" name="tenant_id" placeholder="Tenant ID" required>
            <input type="number" name="prop_id" placeholder="Property ID" required>
            <textarea name="issue" placeholder="Issue" required></textarea>
            <button>Submit</button>
        </form>
        <table>
            <tr><th>ID</th><th>Tenant</th><th>Property</th><th>Issue</th><th>Status</th></tr>
            <?php foreach ($maintenance as $m) {
                echo "<tr><td>{$m['id']}</td><td>{$m['name']}</td><td>{$m['title']}</td><td>{$m['issue']}</td><td>{$m['status']}</td></tr>";
            } ?>
        </table>
    </section>
</body>
</html>