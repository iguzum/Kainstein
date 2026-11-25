<?php
$selectedCategory = $_GET['category'] ?? 'all';
// :: START MENU SIDEBAR ACCOUNT ROLE
$accountRole = $_SESSION['account']['role'] ?? '';
// :: END MENU SIDEBAR ACCOUNT ROLE

$categories = [
    'all' => 'All Menu',
    'main_dishes' => 'Main Dishes',
    'desserts' => 'Desserts',
    'starter' => 'Starter',
    'sides' => 'Sides',
    'drinks' => 'Drinks'
];

// :: START CATEGORY NAV ICONS
$categoryIcons = [
    'all' => 'bx-food-menu',
    'main_dishes' => 'bx-bowl-hot',
    'desserts' => 'bx-cake',
    'starter' => 'bx-leaf',
    'sides' => 'bx-bowl-rice',
    'drinks' => 'bx-drink',
];
// :: END CATEGORY NAV ICONS
?>

<!-- :: START dynamic-sidebar -->
<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-70 -translate-x-full flex-col border-r border-border bg-inner-card px-6 py-7 transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">

    <a href="menu.php" class="mb-12 flex items-center gap-3">
        <span class="grid size-11 place-items-center rounded-2xl bg-primary text-xl text-light">
            <i class="bx bx-bowl-hot"></i>
        </span>
        <span>
            <span class="block font-playfair text-2xl font-bold tracking-wide">KAINSTEIN</span>
        </span>
    </a>

    <p class="mb-4 px-3 text-[10px] font-bold uppercase tracking-[.22em] text-tertiary">
        Explore menu
    </p>

    <nav class="space-y-1" aria-label="Menu categories">

        <?php foreach ($categories as $slug => $name): ?>
            <a href="?category=<?= $slug ?>"
                class="<?= $selectedCategory === $slug ? 'active' : '' ?> flex w-full items-center gap-4 rounded-xl px-4 py-3.5 text-sm font-semibold transition">

                <!-- :: START CATEGORY NAV ICON -->
                <i
                    class="bx <?= htmlspecialchars($categoryIcons[$slug] ?? 'bx-food-menu', ENT_QUOTES, 'UTF-8') ?> text-xl"></i>
                <!-- :: END CATEGORY NAV ICON -->

                <span><?= htmlspecialchars($name) ?></span>
                <?php if ($selectedCategory === $slug): ?>
                    <i class="bx bx-chevron-right ml-auto text-lg"></i>
                <?php endif; ?>

            </a>
        <?php endforeach; ?>

    </nav>
    <!-- :: START ROLE-BASED MENU SIDEBAR ACTIONS -->
    <div class="mt-auto space-y-2">
        <?php if ($accountRole === 'staff'): ?>
            <a href="staff/index.php" title="Back to staff dashboard"
                class="flex items-center gap-3 rounded-xl border border-border bg-background px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark">
                <i class="bx bx-grid-alt text-lg" aria-hidden="true"></i>
                <span>Back to dashboard</span>
            </a>
            <a href="logout.php" title="Log out"
                class="flex items-center gap-3 rounded-xl border border-border bg-background px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark">
                <i class="bx bx-log-out text-lg" aria-hidden="true"></i>
                <span>Log out</span>
            </a>
        <?php elseif ($accountRole === 'admin'): ?>
            <a href="admin/index.php" title="Back to admin dashboard"
                class="flex items-center gap-3 rounded-xl border border-border bg-background px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark">
                <i class="bx bx-grid-alt text-lg" aria-hidden="true"></i>
                <span>Back to dashboard</span>
            </a>
            <a href="logout.php" title="Log out"
                class="flex items-center gap-3 rounded-xl border border-border bg-background px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark">
                <i class="bx bx-log-out text-lg" aria-hidden="true"></i>
                <span>Log out</span>
            </a>
        <?php else: ?>
            <a href="index.php" title="Change order type"
                class="flex items-center gap-3 rounded-xl border border-border bg-background px-4 py-3 text-sm font-semibold text-tertiary transition hover:bg-background-hover hover:text-dark">
                <i class="bx bx-arrow-back text-lg" aria-hidden="true"></i>
                <span>Change order type</span>
            </a>
        <?php endif; ?>
    </div>
    <!-- :: END ROLE-BASED MENU SIDEBAR ACTIONS -->

    <p class="mt-3 text-center text-[10px] tracking-wide text-tertiary">
        ©
        <?= date('Y') ?> KAINSTEIN
    </p>

</aside>
<!-- :: END dynamic-sidebar -->
