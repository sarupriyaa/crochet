<?php
// Start session and establish database connection
session_start();

// Database credentials - change if needed to match your main config
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "snugglestitch";

$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle Record Deletion securely from your 'contacts' table
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $mysqli->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: admin_contacts.php?msg=deleted");
        exit();
    } else {
        $error_msg = "Failed to delete the message.";
    }
    $stmt->close();
}

// Setup Pagination
$limit = 10; // Number of items per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// FIXED: Changed targeted table source from contact_messages to contacts
$total_result = $mysqli->query("SELECT COUNT(id) AS total FROM contacts");
$total_row = $total_result->fetch_assoc();
$total_records = isset($total_row['total']) ? $total_row['total'] : 0;
$total_pages = ceil($total_records / $limit);

// FIXED: Changed targeted table source from contact_messages to contacts
$query = "SELECT id, name, email, message, created_at FROM contacts ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages Department - DaisyHook</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="flex min-h-screen">

    <div class="w-64 bg-white border-r border-slate-100 flex flex-col p-6 shrink-0 justify-between">
        <div>
            <div class="flex items-center gap-3 mb-8">
                <div class="bg-indigo-600 text-white p-2 rounded-xl shadow-md shadow-indigo-100">
                    <i class="fa-solid fa-chart-pie text-xl w-6 h-6 flex items-center justify-center"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 leading-none">DaisyHook</h1>
                    <span class="text-xs font-semibold text-slate-400">Enterprise v2.4</span>
                </div>
            </div>

            <nav class="space-y-1">
                <a href="admin_dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-compass text-lg w-5 text-center"></i> Overview
                </a>
                <a href="home.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-house text-lg w-5 text-center"></i> View Live Site
                </a>
                
                <div class="border-t border-slate-100 my-4"></div>

                <a href="orders.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-box text-lg w-5 text-center"></i> Manage Orders
                </a>
                <a href="admin_payments.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-credit-card text-lg w-5 text-center"></i> Payments Logs
                </a>
                <a href="admin_users.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-users text-lg w-5 text-center"></i> Manage Users
                </a>
                <a href="admin_bouquets.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    Bouquets Dept
                </a>

                <a href="admin_fashion.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-shirt text-lg w-5 text-center"></i> Fashion Catalog
                </a>
                <a href="admin_decors.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-couch text-lg w-5 text-center"></i> Decor Products
                </a>
                <a href="admin_contacts.php" class="flex items-center gap-3 px-4 py-3 bg-indigo-50 text-indigo-600 font-semibold rounded-xl shadow-sm transition-colors">
                    <i class="fa-solid fa-envelope text-lg w-5 text-center"></i> Contact Messages
                </a>
            </nav>
        </div>

        <div class="space-y-1">
            <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-slate-500 font-medium rounded-xl hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-user text-lg w-5 text-center"></i> My Profile
            </a>
            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 font-medium rounded-xl hover:bg-red-50 transition-colors">
                <i class="fa-solid fa-door-open text-lg w-5 text-center"></i> Sign Out
            </a>
        </div>
    </div>

    <main class="flex-1 p-10 overflow-y-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">Contact Messages Department</h2>
                <p class="text-slate-400 text-sm mt-1">Review inquiries, process customer feedback, and manage live storefront submissions safely.</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-xs font-bold tracking-wider text-slate-400 uppercase">Total Submissions</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?php echo $total_records; ?> Messages</h3>
                </div>
                <div class="bg-indigo-50 text-indigo-500 p-4 rounded-xl">
                    <i class="fa-solid fa-inbox text-xl"></i>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-6 col-span-2"></div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-2 text-sm shadow-sm">
                <i class="fa-solid fa-circle-check"></i> Record removed securely from the dataset logs.
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between">
                <h4 class="font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-list-ol text-indigo-500 text-sm"></i> Live Client Submissions Ledger
                </h4>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold text-[11px] tracking-wider uppercase">
                        <th class="py-4 px-6 w-20">ID Code</th>
                        <th class="py-4 px-6 w-52">Sender Identification</th>
                        <th class="py-4 px-6">Message Context Specification</th>
                        <th class="py-4 px-6 w-44">Received Timestamp</th>
                        <th class="py-4 px-6 w-24 text-center">Operational Flags</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-semibold text-slate-400">#CON-<?php echo sprintf("%03d", $row['id']); ?></td>
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-slate-800"><?php echo htmlspecialchars($row['name']); ?></div>
                                    <div class="text-xs text-indigo-500 font-medium mt-0.5"><i class="fa-regular fa-envelope text-[10px]"></i> <?php echo htmlspecialchars($row['email']); ?></div>
                                </td>
                                <td class="py-4 px-6 pr-12">
                                    <div class="bg-slate-50/60 text-slate-600 p-3 rounded-xl border border-slate-100 text-xs leading-relaxed max-w-2xl italic">
                                        "<?php echo nl2br(htmlspecialchars($row['message'])); ?>"
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-medium text-xs text-slate-400">
                                    <i class="fa-regular fa-clock text-slate-300 mr-1"></i>
                                    <?php echo date('M d, Y - h:i A', strtotime($row['created_at'])); ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <a href="admin_contacts.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you certain you want to purge this submission track?');" 
                                       class="text-red-400 hover:text-red-600 p-2 hover:bg-red-50 rounded-lg inline-block transition-all" title="Delete Message">
                                        <i class="fa-solid fa-trash-can text-base"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 font-medium bg-slate-50/20">
                                <i class="fa-solid fa-folder-open text-3xl text-slate-200 block mb-3"></i>
                                No inbox communication queries discovered inside database structures.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if($total_pages > 1): ?>
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-500">
                    <div>
                        Showing record bounds <?php echo ($offset + 1); ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?> options.
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="admin_contacts.php?page=<?php echo max(1, $page - 1); ?>" class="p-2 border border-slate-200 rounded-lg hover:bg-white bg-slate-50 <?php if($page <= 1) echo 'pointer-events-none opacity-50'; ?>">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="admin_contacts.php?page=<?php echo $i; ?>" class="px-3 py-2 border rounded-lg transition-colors <?php echo ($page == $i) ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' : 'border-slate-200 hover:bg-white bg-slate-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <a href="admin_contacts.php?page=<?php echo min($total_pages, $page + 1); ?>" class="p-2 border border-slate-200 rounded-lg hover:bg-white bg-slate-50 <?php if($page >= $total_pages) echo 'pointer-events-none opacity-50'; ?>">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
<?php
$stmt->close();
$mysqli->close();
?>