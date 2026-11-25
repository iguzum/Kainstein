<?php
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
use App\Controllers\Authentication_Controller;
use App\Controllers\Menu_Controller;
// :: START STAFF SEARCH IMPORT
use App\Structures\Linear_Search;
// :: END STAFF SEARCH IMPORT

// :: START ADMIN ACCESS AND ACTIONS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$account = $_SESSION['account'] ?? null;
// :: START ADMIN ROLE CHECK
if (!$account || ($account['role'] ?? '') !== 'admin') {
    header('Location: ../index.php');
    exit;
}
// :: END ADMIN ROLE CHECK
if (empty($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32));
}

$auth = new Authentication_Controller();
$menuController = new Menu_Controller();
$categories = ['main_dishes', 'desserts', 'starter', 'sides', 'drinks'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // :: START ADMIN FORM TOKEN CHECK
        if (!hash_equals($_SESSION['admin_token'], (string) ($_POST['token'] ?? ''))) {
            throw new RuntimeException('Your session expired. Refresh and try again.');
        }
        // :: END ADMIN FORM TOKEN CHECK
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'create_staff') {
            if (!$auth->createStaff((string) ($_POST['account_name'] ?? ''), (string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
                throw new InvalidArgumentException('Enter a name, a valid email, and a password with at least 8 characters.');
            }
            $message = 'Staff account created.';
        } elseif ($action === 'update_staff') {
            if (!$auth->updateStaff((int) ($_POST['account_id'] ?? 0), (string) ($_POST['account_name'] ?? ''), (string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
                throw new InvalidArgumentException('Enter a name, a valid email, and leave the password blank or use at least 8 characters.');
            }
            $message = 'Staff account updated.';
        } elseif ($action === 'delete_staff') {
            $auth->deleteStaff((int) ($_POST['account_id'] ?? 0));
            $message = 'Staff account deleted.';
        } elseif ($action === 'create_menu') {
            $menuController->addFood(
                (string) ($_POST['item_name'] ?? ''),
                (string) ($_POST['category'] ?? ''),
                (string) ($_POST['description'] ?? ''),
                (float) ($_POST['price'] ?? 0),
                (string) ($_POST['image_url'] ?? ''),
                (string) ($_POST['is_available'] ?? 'Y')
            );
            $message = 'Menu item added.';
        } elseif ($action === 'update_menu') {
            $ok = $menuController->updateFood(
                (int) ($_POST['menu_id'] ?? 0),
                (string) ($_POST['item_name'] ?? ''),
                (string) ($_POST['category'] ?? ''),
                (string) ($_POST['description'] ?? ''),
                (float) ($_POST['price'] ?? 0),
                (string) ($_POST['image_url'] ?? ''),
                (string) ($_POST['is_available'] ?? 'Y')
            );
            if (!$ok) {
                throw new InvalidArgumentException('Check the menu details and try again.');
            }
            $message = 'Menu item updated.';
        } elseif ($action === 'delete_menu') {
            $menuController->deleteFood((int) ($_POST['menu_id'] ?? 0));
            $message = 'Menu item deleted.';
        }
    } catch (Throwable $exception) {
        $error = $exception instanceof PDOException && (string) $exception->getCode() === '23000'
            ? 'That email is already in use, or this item is already part of an order.'
            : ($exception instanceof InvalidArgumentException || $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'Unable to save changes. Please check the details and try again.');
    }
}

try {
    // :: START FILTER STAFF WITH LINEAR SEARCH
    $allStaff = $auth->getAllStaff();
    $staffSearch = isset($_GET['staff_search']) && is_string($_GET['staff_search'])
        ? trim($_GET['staff_search'])
        : '';
    $staff = (new Linear_Search())->searchStaffByName($allStaff, $staffSearch);
    // :: END FILTER STAFF WITH LINEAR SEARCH
    // :: START FILTER MENU WITH LINEAR SEARCH
    $allMenuItems = $menuController->getAllMenu();
    $menuSearch = isset($_GET['menu_search']) && is_string($_GET['menu_search'])
        ? trim($_GET['menu_search'])
        : '';
    $menuItems = (new Linear_Search())->searchFoodsByName($allMenuItems, $menuSearch);
    // :: END FILTER MENU WITH LINEAR SEARCH
} catch (Throwable $exception) {
    $allStaff = [];
    $staff = [];
    $staffSearch = '';
    $allMenuItems = [];
    $menuItems = [];
    $menuSearch = '';
    $error = $error ?: 'Unable to load management data. Check the database connection.';
}
// :: START FIND STAFF BEING EDITED
$editStaff = null;
foreach ($allStaff as $row) {
    if ((int) $row['account_id'] === (int) ($_GET['edit_staff'] ?? 0))
        $editStaff = $row;
}
// :: END FIND STAFF BEING EDITED
$editMenu = null;
// :: START FIND MENU ITEM BEING EDITED
foreach ($allMenuItems as $row) {
    if ((int) $row['menu_id'] === (int) ($_GET['edit_menu'] ?? 0))
        $editMenu = $row;
}
// :: END FIND MENU ITEM BEING EDITED
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
// :: END ADMIN ACCESS AND ACTIONS
?>
<!DOCTYPE html>
<!-- :: START ADMIN DASHBOARD -->
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#faf7f2">
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <title>KAINSTEIN | Admin Dashboard</title>
</head>

<body class="min-h-screen bg-background font-dm text-dark antialiased">
    <div class="min-h-screen lg:flex">
        <aside
            class="flex w-full flex-col border-b border-border bg-inner-card px-6 py-6 lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r">
            <a href="../index.php" class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-2xl bg-primary text-xl text-light"><i
                        class="bx bx-bowl-hot" aria-hidden="true"></i></span>
                <span>
                    <span class="block font-playfair text-2xl font-bold tracking-wide">KAINSTEIN</span>
                    <span class="text-xs text-tertiary">Admin console</span>
                </span>
            </a>
            <p class="mb-3 mt-10 px-3 text-[10px] font-bold uppercase tracking-[.2em] text-tertiary">Management</p>
            <nav class="grid grid-cols-2 gap-2 lg:grid-cols-1" aria-label="Admin dashboard">
                <a href="#overview"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark"><i
                        class="bx bx-grid-alt text-xl"></i> Overview</a>
                <a href="#menu-management"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark"><i
                        class="bx bx-food-menu text-xl"></i> Menu</a>
                <a href="#staff-management"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark"><i
                        class="bx bx-group text-xl"></i> Staff</a>
            </nav>
            <div class="mt-8 rounded-2xl bg-background p-4">
                <p class="text-xs font-bold uppercase tracking-[.16em] text-secondary">Signed in as</p>
                <!-- :: START SIGNED-IN ACCOUNT NAME -->

                <p class="mt-4 break-all text-md font-semibold flex flex-col leading-5">
                    <span>
                        <?= $e($account['account_name'] ?? $account['email']) ?>
                    </span>

                    <span class="text-xs text-tertiary">
                        <?= $e($account['email'] ?? '') ?>
                    </span>
                </p>
                <p class="mt-2 break-all text-xs font-semibold text-tertiary">

                </p>
                <!-- :: END SIGNED-IN ACCOUNT NAME -->
            </div>
            <!-- :: START ADMIN LOGOUT LINK -->
            <a href="../logout.php"
                class="mt-4 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark lg:mt-auto">
                <i class="bx bx-log-out text-xl" aria-hidden="true"></i> Log out
            </a>
            <!-- :: END ADMIN LOGOUT LINK -->
        </aside>

        <main id="overview" class="min-w-0 flex-1 px-5 py-7 sm:px-8 lg:px-10 lg:py-10">
            <header class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.2em] text-secondary">Kainstein management</p>
                    <h1 class="mt-2 font-playfair text-3xl font-bold sm:text-4xl">Admin dashboard</h1>
                </div>
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-border bg-inner-card px-4 py-2 text-xs font-semibold text-tertiary"><span
                        class="size-2 rounded-full bg-accent"></span> Admin view</span>
            </header>

            <?php if ($message): ?>
                <p role="status"
                    class="mb-5 rounded-xl border border-primary/20 bg-primary/10 px-4 py-3 text-sm font-semibold text-primary">
                    <?= $e($message) ?>
                </p><?php endif; ?>
            <?php if ($error): ?>
                <p role="alert"
                    class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    <?= $e($error) ?>
                </p><?php endif; ?>

            <section class="mb-8 grid gap-4 sm:grid-cols-2" aria-label="Management totals">
                <article class="rounded-2xl border border-border bg-inner-card p-5 shadow-sm">
                    <p class="text-sm font-semibold text-tertiary">Staff accounts</p>
                    <p class="mt-2 font-playfair text-3xl font-bold"><?= count($allStaff) ?></p>
                </article>
                <article class="rounded-2xl border border-border bg-inner-card p-5 shadow-sm">
                    <p class="text-sm font-semibold text-tertiary">Menu items</p>
                    <p class="mt-2 font-playfair text-3xl font-bold"><?= count($menuItems) ?></p>
                </article>
            </section>

            <section id="staff-management"
                class="mb-8 rounded-3xl border border-border bg-inner-card p-5 shadow-sm sm:p-7">
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-secondary">Team access</p>
                    <h2 class="mt-2 font-playfair text-2xl font-bold">Staff accounts</h2>
                    <p class="mt-1 text-sm text-tertiary">Create and manage staff sign-ins.</p>
                </div>
                <form method="post" class="mb-7 grid gap-4 rounded-2xl bg-background p-4 md:grid-cols-2">
                    <input type="hidden" name="token" value="<?= $e($_SESSION['admin_token']) ?>">
                    <input type="hidden" name="action" value="<?= $editStaff ? 'update_staff' : 'create_staff' ?>">
                    <?php if ($editStaff): ?><input type="hidden" name="account_id"
                            value="<?= (int) $editStaff['account_id'] ?>"><?php endif; ?>
                    <!-- :: START STAFF NAME FIELD -->
                    <label class="text-sm font-semibold">Name<input required type="text" name="account_name"
                            value="<?= $e($editStaff['account_name'] ?? '') ?>" placeholder="Staff name"
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary"></label>
                    <!-- :: END STAFF NAME FIELD -->
                    <label class="text-sm font-semibold">Email<input required type="email" name="email"
                            value="<?= $e($editStaff['email'] ?? '') ?>" placeholder="staff@example.com"
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary"></label>
                    <label class="text-sm font-semibold">Password
                        <?= $editStaff ? '<span class="font-normal text-tertiary">(leave blank to keep current)</span>' : '' ?><input
                            <?= $editStaff ? '' : 'required' ?> minlength="8" type="password" name="password"
                            placeholder="At least 8 characters"
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary"></label>
                    <div class="flex items-end gap-2"><button
                            class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-bold text-light hover:bg-primary-hover"
                            type="submit"><?= $editStaff ? 'Save changes' : 'Create staff' ?></button><?php if ($editStaff): ?><a
                                href="index.php#staff-management"
                                class="rounded-xl border border-border px-4 py-3 text-sm font-semibold">Cancel</a><?php endif; ?>
                    </div>
                </form>
                <!-- :: START STAFF NAME SEARCH FORM -->
                <form method="get" action="index.php#staff-management" class="mb-5 flex flex-col gap-3 sm:flex-row">
                    <label for="staffSearch" class="sr-only">Search staff by name</label>
                    <input id="staffSearch" type="search" name="staff_search" value="<?= $e($staffSearch) ?>"
                        placeholder="Search staff by name"
                        class="min-w-0 flex-1 rounded-xl border border-border bg-background px-4 py-3 text-sm outline-none focus:border-primary focus:ring-4 focus:ring-primary/10">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-light transition hover:bg-primary-hover">
                        <i class="bx bx-search" aria-hidden="true"></i> Search
                    </button>
                    <?php if ($staffSearch !== ''): ?>
                        <a href="index.php#staff-management"
                            class="inline-flex items-center justify-center rounded-xl border border-border px-5 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark">Clear</a>
                    <?php endif; ?>
                </form>
                <p class="mb-3 text-xs text-tertiary">
                    <?= $staffSearch !== '' ? 'Search results: ' : 'Showing ' ?><?= count($staff) ?> staff
                    member<?= count($staff) === 1 ? '' : 's' ?>
                </p>
                <!-- :: END STAFF NAME SEARCH FORM -->
                <div class="overflow-x-auto">
                    <!-- :: START STAFF TABLE NAME LAYOUT -->
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="text-xs uppercase tracking-wider text-tertiary">
                            <tr>
                                <!-- :: START STAFF NAME AND EMAIL HEADINGS -->
                                <th class="pb-3">Name</th>
                                <th class="pb-3">Staff email</th>
                                <!-- :: END STAFF NAME AND EMAIL HEADINGS -->
                                <th class="pb-3">Created</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff as $member): ?>
                                <tr class="border-t border-border">
                                    <!-- :: START STAFF NAME AND EMAIL CELLS -->
                                    <td class="py-4 font-semibold"><?= $e($member['account_name']) ?></td>
                                    <td class="py-4"><?= $e($member['email']) ?></td>
                                    <!-- :: END STAFF NAME AND EMAIL CELLS -->
                                    <td class="py-4 text-tertiary"><?= $e($member['created_at']) ?></td>
                                    <td class="py-4">
                                        <div class="flex justify-end gap-2"><a
                                                href="?edit_staff=<?= (int) $member['account_id'] ?>&amp;staff_search=<?= urlencode($staffSearch) ?>#staff-management"
                                                class="rounded-lg border border-border px-3 py-2 text-xs font-semibold hover:border-primary">Edit</a>
                                            <form method="post">
                                                <input type="hidden" name="token"
                                                    value="<?= $e($_SESSION['admin_token']) ?>"><input type="hidden"
                                                    name="action" value="delete_staff"><input type="hidden"
                                                    name="account_id" value="<?= (int) $member['account_id'] ?>"><button
                                                    type="button" data-delete-trigger data-delete-type="staff account"
                                                    data-delete-name="<?= $e($member['account_name']) ?>"
                                                    class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr><?php endforeach; ?>
                            <?php if (!$staff): ?>
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-tertiary">
                                        <?= $staffSearch !== '' ? 'No staff match that name.' : 'No staff accounts yet.' ?>
                                    </td>
                                </tr><?php endif; ?>
                        </tbody>
                    </table>
                    <!-- :: END STAFF TABLE NAME LAYOUT -->
                </div>
            </section>

            <section id="menu-management" class="rounded-3xl border border-border bg-inner-card p-5 shadow-sm sm:p-7">
                <div class="mb-6">
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-secondary">Kitchen catalog</p>
                    <h2 class="mt-2 font-playfair text-2xl font-bold">Food menu</h2>
                    <p class="mt-1 text-sm text-tertiary">Add dishes, update details, or remove items.</p>
                </div>
                <form method="post" class="mb-7 grid gap-4 rounded-2xl bg-background p-4 sm:grid-cols-2 lg:grid-cols-3">
                    <input type="hidden" name="token" value="<?= $e($_SESSION['admin_token']) ?>"><input type="hidden"
                        name="action" value="<?= $editMenu ? 'update_menu' : 'create_menu' ?>">
                    <?php if ($editMenu): ?><input type="hidden" name="menu_id"
                            value="<?= (int) $editMenu['menu_id'] ?>"><?php endif; ?>
                    <label class="text-sm font-semibold">Food name<input required name="item_name"
                            value="<?= $e($editMenu['item_name'] ?? '') ?>"
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary"></label>
                    <label class="text-sm font-semibold">Category<select name="category" required
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary"><?php foreach ($categories as $category): ?>
                                <option value="<?= $e($category) ?>" <?= ($editMenu['category'] ?? '') === $category ? 'selected' : '' ?>><?= $e(ucwords(str_replace('_', ' ', $category))) ?></option>
                            <?php endforeach; ?>
                        </select></label>
                    <label class="text-sm font-semibold">Price (₱)<input required min="0" step="0.01" type="number"
                            name="price" value="<?= $e($editMenu['price'] ?? '') ?>"
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary"></label>
                    <label class="text-sm font-semibold sm:col-span-2 lg:col-span-3">Image URL<input required type="url"
                            name="image_url" value="<?= $e($editMenu['image_url'] ?? '') ?>" placeholder="https://..."
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary"></label>
                    <label class="text-sm font-semibold sm:col-span-2 lg:col-span-2">Description<textarea
                            name="description" rows="2"
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary"><?= $e($editMenu['description'] ?? '') ?></textarea></label>
                    <label class="text-sm font-semibold">Availability<select name="is_available"
                            class="mt-2 w-full rounded-xl border border-border bg-inner-card px-4 py-3 text-sm outline-none focus:border-primary">
                            <option value="Y" <?= ($editMenu['is_available'] ?? 'Y') === 'Y' ? 'selected' : '' ?>>Available
                            </option>
                            <option value="N" <?= ($editMenu['is_available'] ?? '') === 'N' ? 'selected' : '' ?>>
                                Unavailable</option>
                        </select></label>
                    <div class="flex gap-2 sm:col-span-2 lg:col-span-3"><button
                            class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-light hover:bg-primary-hover"
                            type="submit"><?= $editMenu ? 'Save menu item' : 'Add menu item' ?></button><?php if ($editMenu): ?><a
                                href="index.php#menu-management"
                                class="rounded-xl border border-border px-5 py-3 text-sm font-semibold">Cancel</a><?php endif; ?>
                    </div>
                </form>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="text-xs uppercase tracking-wider text-tertiary">
                            <tr>
                                <th class="pb-3">Item</th>
                                <th class="pb-3">Category</th>
                                <th class="pb-3">Price</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menuItems as $item): ?>
                                <tr class="border-t border-border">
                                    <td class="py-4 font-semibold"><?= $e($item['item_name']) ?></td>
                                    <td class="py-4 text-tertiary">
                                        <?= $e(ucwords(str_replace('_', ' ', $item['category']))) ?>
                                    </td>
                                    <td class="py-4">₱<?= number_format((float) $item['price'], 2) ?></td>
                                    <td class="py-4"><span
                                            class="rounded-full px-3 py-1 text-xs font-semibold <?= $item['is_available'] === 'Y' ? 'bg-primary/10 text-primary' : 'bg-outer-card text-tertiary' ?>"><?= $item['is_available'] === 'Y' ? 'Available' : 'Unavailable' ?></span>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex justify-end gap-2"><a
                                                href="?edit_menu=<?= (int) $item['menu_id'] ?>#menu-management"
                                                class="rounded-lg border border-border px-3 py-2 text-xs font-semibold hover:border-primary">Edit</a>
                                            <form method="post">
                                                <input type="hidden" name="token"
                                                    value="<?= $e($_SESSION['admin_token']) ?>"><input type="hidden"
                                                    name="action" value="delete_menu"><input type="hidden" name="menu_id"
                                                    value="<?= (int) $item['menu_id'] ?>"><button
                                                    type="button" data-delete-trigger data-delete-type="menu item"
                                                    data-delete-name="<?= $e($item['item_name']) ?>"
                                                    class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr><?php endforeach; ?>
                            <?php if (!$menuItems): ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-tertiary">No menu items yet.</td>
                                </tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <footer class="mt-8 text-center text-xs text-tertiary">© <?= date('Y') ?> KAINSTEIN · Admin console</footer>
        </main>
    </div>

    <!-- :: START DELETE CONFIRMATION MODAL -->
    <div id="deleteConfirmModal"
        class="fixed inset-0 z-[100] hidden items-center justify-center bg-dark/50 p-4 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="deleteConfirmTitle"
        aria-describedby="deleteConfirmMessage">
        <div class="w-full max-w-sm rounded-2xl border border-border bg-inner-card p-6 shadow-2xl">
            <span class="grid size-12 place-items-center rounded-xl bg-secondary/10 text-2xl text-secondary">
                <i class="bx bx-trash" aria-hidden="true"></i>
            </span>
            <h2 id="deleteConfirmTitle" class="mt-4 font-playfair text-2xl font-bold">Delete this item?</h2>
            <p id="deleteConfirmMessage" class="mt-2 text-sm leading-6 text-tertiary"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button id="deleteCancel" type="button"
                    class="rounded-xl border border-border px-4 py-2.5 text-sm font-semibold text-dark transition hover:bg-background-hover">Cancel</button>
                <button id="deleteConfirm" type="button"
                    class="rounded-xl bg-secondary px-4 py-2.5 text-sm font-bold text-light transition hover:bg-secondary-hover">Delete</button>
            </div>
        </div>
    </div>
    <!-- :: END DELETE CONFIRMATION MODAL -->

    <!-- :: START DELETE CONFIRMATION CONTROLS -->
    <script>
        const deleteModal = document.getElementById('deleteConfirmModal');
        const deleteMessage = document.getElementById('deleteConfirmMessage');
        const deleteCancel = document.getElementById('deleteCancel');
        const deleteConfirm = document.getElementById('deleteConfirm');
        let deleteFormToSubmit = null;
        let lastDeleteTrigger = null;

        function closeDeleteModal() {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
            deleteModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            deleteFormToSubmit = null;
            lastDeleteTrigger?.focus();
        }

        document.querySelectorAll('[data-delete-trigger]').forEach((button) => {
            button.addEventListener('click', () => {
                deleteFormToSubmit = button.closest('form');
                lastDeleteTrigger = button;
                const type = button.dataset.deleteType;
                const name = button.dataset.deleteName;
                deleteMessage.textContent = 'Delete ' + type + ' “' + name + '”? This action cannot be undone.';
                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');
                deleteModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
                deleteCancel.focus();
            });
        });

        deleteCancel.addEventListener('click', closeDeleteModal);
        deleteModal.addEventListener('click', (event) => {
            if (event.target === deleteModal) closeDeleteModal();
        });
        deleteConfirm.addEventListener('click', () => {
            if (deleteFormToSubmit) deleteFormToSubmit.requestSubmit();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
                closeDeleteModal();
            }
        });
    </script>
    <!-- :: END DELETE CONFIRMATION CONTROLS -->
</body>

</html>
<!-- :: END ADMIN DASHBOARD -->
